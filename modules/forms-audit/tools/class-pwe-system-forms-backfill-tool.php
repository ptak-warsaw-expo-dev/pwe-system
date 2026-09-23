<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Additional Forms Audit tool for restoring missing QR metadata
 * in historical Gravity Forms entries.
 */
final class PWE_System_Forms_Backfill_Tool {
    /** @var PWE_QR_Generator */
    private $qr;

    /** @var int */
    private $batch_size = 50;

    public function __construct($qr) {
        $this->qr = $qr;

        add_action('pwe_system_forms_audit_tools', [$this, 'render_audit_section']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_ajax_pwe_system_forms_backfill_scan', [$this, 'ajax_scan']);
        add_action('wp_ajax_pwe_system_forms_backfill_generate', [$this, 'ajax_generate']);
    }

    public function enqueue_assets(): void {
        if (!is_admin() || sanitize_key((string) ($_GET['page'] ?? '')) !== 'pwe-system-forms-audit') {
            return;
        }

        wp_enqueue_script(
            'pwe-system-forms-backfill',
            PWE_SYSTEM_URL . 'assets/js/forms-backfill.js',
            ['jquery'],
            PWE_SYSTEM_VERSION,
            true
        );

        wp_localize_script('pwe-system-forms-backfill', 'PWEFormsBackfill', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('pwe_system_forms_backfill'),
        ]);
    }

    public function render_audit_section(): void {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <section class="pwe-qr-section pwe-forms-backfill" id="pwe-forms-backfill">
            <div class="pwe-forms-backfill__head">
                <div>
                    <span class="pwe-forms-backfill__eyebrow">NARZĘDZIE SERWISOWE</span>
                    <h2>Uzupełnij brakujące QR</h2>
                    <p>
                        Narzędzie sprawdza formularze posiadające aktywny feed <code>pwe_qr</code>,
                        wyszukuje aktywne wpisy bez metadanych <code>pwe_qr_code_url</code>
                        i generuje brakujące kody partiami. Nie wysyła ponownie powiadomień
                        i nie uruchamia pozostałych integracji formularza.
                    </p>
                </div>
                <span class="dashicons dashicons-update-alt pwe-forms-backfill__icon" aria-hidden="true"></span>
            </div>

            <div class="pwe-forms-backfill__actions">
                <button type="button" class="button button-secondary" id="pwe-forms-backfill-scan">
                    1. Sprawdź formularze i brakujące QR
                </button>
                <button type="button" class="button button-primary" id="pwe-forms-backfill-generate" disabled>
                    2. Wygeneruj brakujące QR
                </button>
            </div>

            <div id="pwe-forms-backfill-status" class="pwe-forms-backfill__status"></div>

            <div id="pwe-forms-backfill-progress" class="pwe-forms-backfill__progress" hidden>
                <div class="pwe-forms-backfill__progress-track">
                    <div id="pwe-forms-backfill-bar" class="pwe-forms-backfill__progress-bar"></div>
                </div>
                <p id="pwe-forms-backfill-progress-text"></p>
            </div>
        </section>
        <?php
    }

    public function ajax_scan(): void {
        $this->authorize_request();

        if (!class_exists('GFAPI')) {
            wp_send_json_error(['message' => 'Gravity Forms nie jest dostępne.'], 500);
        }

        $forms = GFAPI::get_forms(true, false, 'title', 'ASC');
        $result = [];
        $total_missing = 0;

        foreach ((array) $forms as $form) {
            $form_id = absint($form['id'] ?? 0);
            if (!$form_id) {
                continue;
            }

            $feeds = $this->get_active_feeds($form_id);
            if (!$feeds) {
                continue;
            }

            $total_entries = $this->count_entries($form_id, false);
            $missing_entries = $this->count_entries($form_id, true);

            $result[] = [
                'id'              => $form_id,
                'title'           => $form['title'] ?? ('Formularz ' . $form_id),
                'active_feeds'    => count($feeds),
                'total_entries'   => $total_entries,
                'missing_entries' => $missing_entries,
            ];

            $total_missing += $missing_entries;
        }

        wp_send_json_success([
            'forms'         => $result,
            'total_missing' => $total_missing,
        ]);
    }

    public function ajax_generate(): void {
        $this->authorize_request();

        if (!class_exists('GFAPI') || !function_exists('gform_update_meta')) {
            wp_send_json_error(['message' => 'Gravity Forms nie jest dostępne.'], 500);
        }

        $form_id = absint($_POST['form_id'] ?? 0);
        if (!$form_id) {
            wp_send_json_error(['message' => 'Brak prawidłowego ID formularza.'], 400);
        }

        $form = GFAPI::get_form($form_id);
        if (!$form || is_wp_error($form)) {
            wp_send_json_error(['message' => 'Nie znaleziono formularza.'], 404);
        }

        if (!$this->get_active_feeds($form_id)) {
            wp_send_json_error(['message' => 'Formularz nie ma aktywnego feedu pwe_qr.'], 400);
        }

        $entry_ids = $this->get_missing_entry_ids($form_id, $this->batch_size);
        $generated = 0;
        $failed = [];

        foreach ($entry_ids as $entry_id) {
            $entry = GFAPI::get_entry($entry_id);

            if (is_wp_error($entry) || empty($entry['id'])) {
                $failed[] = absint($entry_id);
                continue;
            }

            if ($this->save_qr_code_link_to_entry_meta($entry, $form)) {
                $generated++;
            } else {
                $failed[] = absint($entry_id);
            }
        }

        $remaining = $this->count_entries($form_id, true);

        wp_send_json_success([
            'processed' => count($entry_ids),
            'generated' => $generated,
            'failed'    => $failed,
            'remaining' => $remaining,
        ]);
    }

    private function authorize_request(): void {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Brak uprawnień.'], 403);
        }

        if (!check_ajax_referer('pwe_system_forms_backfill', 'nonce', false)) {
            wp_send_json_error(['message' => 'Sesja wygasła. Odśwież stronę.'], 403);
        }
    }

    private function get_active_feeds(int $form_id): array {
        if (!class_exists('GFAPI')) {
            return [];
        }

        $feeds = GFAPI::get_feeds(null, $form_id, 'pwe_qr');
        if (is_wp_error($feeds) || empty($feeds) || !is_array($feeds)) {
            return [];
        }

        return array_values(array_filter($feeds, static function ($feed) {
            return !empty($feed['is_active']);
        }));
    }

    private function save_qr_code_link_to_entry_meta(array $entry, array $form): bool {
        if (!is_object($this->qr) || !method_exists($this->qr, 'get_qr_data_for_feed')) {
            return false;
        }

        $form_id = absint($form['id'] ?? 0);
        $entry_id = absint($entry['id'] ?? 0);
        if (!$form_id || !$entry_id) {
            return false;
        }

        foreach ($this->get_active_feeds($form_id) as $feed) {
            $meta = $feed['meta'] ?? [];
            $feed_name = $meta['feedName'] ?? $meta['qr_name'] ?? '';

            if ($feed_name === '') {
                continue;
            }

            $data = $this->qr->get_qr_data_for_feed($feed_name, $form_id, $entry);
            if (empty($data) || empty($data['value'])) {
                continue;
            }

            $qr_url = $this->build_qr_image_url(
                (string) $data['value'],
                (string) ($data['label'] ?? ''),
                absint($data['size'] ?? 200) ?: 200,
                (string) ($data['logo_url'] ?? '')
            );

            if ($qr_url === '') {
                continue;
            }

            $qr_url = esc_url_raw($qr_url);

            gform_update_meta($entry_id, 'pwe_qr_code_url', $qr_url, $form_id);
            gform_update_meta($entry_id, 'pwe_qr_code_url_encoded', rawurlencode($qr_url), $form_id);

            return (string) gform_get_meta($entry_id, 'pwe_qr_code_url') !== '';
        }

        return false;
    }

    private function build_qr_image_url(string $value, string $label, int $size, string $logo_url = ''): string {
        $size = absint($size) ?: 200;
        $logo_url = trim($logo_url);

        $signature = hash_hmac(
            'sha256',
            $value . '|' . $label . '|' . $size . '|' . $logo_url,
            wp_salt('auth')
        );

        $args = [
            'pwe_qr_img' => '1',
            'value'      => rawurlencode($value),
            'label'      => rawurlencode($label),
            'size'       => $size,
            'sig'        => $signature,
        ];

        if ($logo_url !== '') {
            $args['logo'] = rawurlencode($logo_url);
        }

        return add_query_arg($args, home_url('/'));
    }

    private function get_table_names(): array {
        global $wpdb;

        $entry_table = method_exists('GFFormsModel', 'get_entry_table_name')
            ? GFFormsModel::get_entry_table_name()
            : $wpdb->prefix . 'gf_entry';

        $meta_table = method_exists('GFFormsModel', 'get_entry_meta_table_name')
            ? GFFormsModel::get_entry_meta_table_name()
            : $wpdb->prefix . 'gf_entry_meta';

        return [$entry_table, $meta_table];
    }

    private function count_entries(int $form_id, bool $missing_only): int {
        global $wpdb;
        [$entry_table, $meta_table] = $this->get_table_names();

        if ($missing_only) {
            $sql = $wpdb->prepare(
                "SELECT COUNT(DISTINCT e.id)
                 FROM {$entry_table} e
                 LEFT JOIN {$meta_table} m
                   ON m.entry_id = e.id
                  AND m.meta_key = %s
                 WHERE e.form_id = %d
                   AND e.status = 'active'
                   AND (m.id IS NULL OR m.meta_value IS NULL OR m.meta_value = '')",
                'pwe_qr_code_url',
                $form_id
            );
        } else {
            $sql = $wpdb->prepare(
                "SELECT COUNT(*) FROM {$entry_table}
                 WHERE form_id = %d AND status = 'active'",
                $form_id
            );
        }

        return absint($wpdb->get_var($sql));
    }

    private function get_missing_entry_ids(int $form_id, int $limit): array {
        global $wpdb;
        [$entry_table, $meta_table] = $this->get_table_names();

        $sql = $wpdb->prepare(
            "SELECT DISTINCT e.id
             FROM {$entry_table} e
             LEFT JOIN {$meta_table} m
               ON m.entry_id = e.id
              AND m.meta_key = %s
             WHERE e.form_id = %d
               AND e.status = 'active'
               AND (m.id IS NULL OR m.meta_value IS NULL OR m.meta_value = '')
             ORDER BY e.id ASC
             LIMIT %d",
            'pwe_qr_code_url',
            $form_id,
            absint($limit)
        );

        return array_map('absint', (array) $wpdb->get_col($sql));
    }
}
