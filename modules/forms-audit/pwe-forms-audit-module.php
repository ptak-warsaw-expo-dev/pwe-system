<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/traits/trait-pwe-forms-audit-render.php';
require_once __DIR__ . '/traits/trait-pwe-forms-audit-notifications.php';
require_once __DIR__ . '/traits/trait-pwe-forms-audit-data.php';
require_once __DIR__ . '/traits/trait-pwe-forms-audit-export.php';
require_once __DIR__ . '/tools/class-pwe-system-forms-backfill-tool.php';

/**
 * Audyt formularzy i rejestracji należący do PWE System.
 *
 * PWE QR Gravity Forms pozostaje źródłem generatora QR i danych integracji,
 * natomiast ekran audytu, jego akcje i menu są utrzymywane wyłącznie tutaj.
 */
class PWE_System_Forms_Audit_Tool {
    use PWE_System_Forms_Audit_Render_Trait;
    use PWE_System_Forms_Audit_Notifications_Trait;
    use PWE_System_Forms_Audit_Data_Trait;
    use PWE_System_Forms_Audit_Export_Trait;

        private $notification_sent_cache = [];
        private $notification_error_cache = [];
        private $notification_delivery_cache = [];

        /** @var array|null */
        private $audit_session_rows = null;

        /** @var string */
        private $audit_session_cache_key = '';

        /** @var PWE_QR_Generator */
        private $qr;

        /** @var int */
        private $per_page = 100;


    public function __construct($qr) {
        $this->qr = $qr;

        add_action('admin_menu', [$this, 'register_submenu'], 31);
        add_action('admin_post_pwe_qr_export_mismatches', [$this, 'export_mismatches_csv']);
        add_action('wp_ajax_pwe_qr_resend_notifications', [$this, 'ajax_resend_notifications']);
        add_action('wp_ajax_pwe_qr_bulk_language_preview', [$this, 'ajax_bulk_language_preview']);
        add_action('wp_ajax_pwe_system_forms_audit_load', [$this, 'ajax_load_audit']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('pwe_system_forms_audit_cache_invalidate', [$this, 'clear_audit_session_cache']);
    }


    public function register_submenu() {
        add_submenu_page(
            'pwe-system',
            'Audyt formularzy i rejestracji',
            'Audyt formularzy',
            'manage_options',
            'pwe-system-forms-audit',
            [$this, 'render_page']
        );
    }


    public function render_page() {
        if (!current_user_can('manage_options')) {
            wp_die('Brak uprawnień.');
        }

        echo '<div class="wrap pwe-system-wrap pwe-system-module-page pwe-forms-audit">';

        if (class_exists('PWE_System_Admin') && method_exists('PWE_System_Admin', 'render_module_header')) {
            PWE_System_Admin::render_module_header(
                'PWE SYSTEM / GRAVITY FORMS',
                'Audyt formularzy i rejestracji',
                'Audyt formularzy, feedów QR, rejestracji i powiadomień. Widok nie zmienia danych bez wykonania konkretnej akcji.',
                'dashicons-search'
            );
        } else {
            echo '<h1>Audyt formularzy i rejestracji</h1>';
        }

        echo '<div class="pwe-module-content pwe-forms-audit-content">';

        if (!class_exists('GFAPI')) {
            echo '<div class="pwe-system-message is-warning"><span class="dashicons dashicons-warning"></span><div><strong>Gravity Forms wymagane</strong><p>Gravity Forms nie jest dostępne. Aktywuj wtyczkę, aby uruchomić audyt.</p></div></div>';
            echo '</div></div>';
            return;
        }

        $this->render_styles();

        // Additional maintenance tools are lightweight and can be available immediately.
        do_action('pwe_system_forms_audit_tools');

        echo '<div id="pwe-forms-audit-async" class="pwe-forms-audit-async" aria-live="polite">';
        echo '<div class="pwe-forms-audit-loader">';
        echo '<span class="pwe-forms-audit-loader__spinner" aria-hidden="true"></span>';
        echo '<div><strong>Ładowanie audytu…</strong><p>Strona jest już gotowa. Dane formularzy, rejestracji i powiadomień są pobierane w tle.</p></div>';
        echo '</div>';
        echo '</div>';

        echo '</div></div>';
    }

    public function enqueue_assets(): void {
        if (!is_admin() || sanitize_key((string) ($_GET['page'] ?? '')) !== 'pwe-system-forms-audit') {
            return;
        }

        $script = PWE_SYSTEM_PATH . 'assets/js/forms-audit.js';

        wp_enqueue_script(
            'pwe-system-forms-audit',
            PWE_SYSTEM_URL . 'assets/js/forms-audit.js',
            ['jquery'],
            is_file($script) ? (string) filemtime($script) : PWE_SYSTEM_VERSION,
            true
        );

        wp_localize_script('pwe-system-forms-audit', 'PWEFormsAudit', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('pwe_system_forms_audit_load'),
        ]);
    }

    public function ajax_load_audit(): void {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Brak uprawnień.'], 403);
        }

        if (!check_ajax_referer('pwe_system_forms_audit_load', 'nonce', false)) {
            wp_send_json_error(['message' => 'Sesja wygasła. Odśwież stronę.'], 403);
        }

        if (!class_exists('GFAPI')) {
            wp_send_json_error(['message' => 'Gravity Forms nie jest dostępne.'], 500);
        }

        $allowed = [
            'audit_form_id',
            'audit_search',
            'audit_status',
            'audit_notification',
            'audit_per_page',
            'audit_paged',
        ];

        foreach ($allowed as $key) {
            if (!isset($_REQUEST[$key])) {
                continue;
            }

            $_GET[$key] = wp_unslash($_REQUEST[$key]);
        }

        $forms = GFAPI::get_forms(true, false, 'title', 'ASC');
        $active_form_ids = [];

        if (is_array($forms)) {
            foreach ($forms as $form) {
                $form_id = absint($form['id'] ?? 0);

                if ($form_id && !empty($this->get_pwe_feeds($form_id))) {
                    $active_form_ids[] = $form_id;
                }
            }
        }

        $force_refresh = !empty($_REQUEST['audit_refresh']);
        $this->prime_audit_session_cache($active_form_ids, $force_refresh);

        ob_start();
        $this->render_forms_table($forms);
        $this->render_entries_table($forms);
        $html = (string) ob_get_clean();

        wp_send_json_success(['html' => $html]);
    }

}

final class PWE_System_Forms_Audit_Module {
    /** @var PWE_System_Forms_Audit_Tool|null */
    private static $tool = null;

    /** @var PWE_System_Forms_Backfill_Tool|null */
    private static $backfill_tool = null;

    public static function boot(): void {
        if (self::$tool instanceof PWE_System_Forms_Audit_Tool) {
            return;
        }

        if (!class_exists('PWE_QR_Gravity_Forms', false)) {
            return;
        }

        $plugin = PWE_QR_Gravity_Forms::get_instance();
        if (!is_object($plugin) || !isset($plugin->qr) || !is_object($plugin->qr)) {
            return;
        }

        self::$tool = new PWE_System_Forms_Audit_Tool($plugin->qr);
        self::$backfill_tool = new PWE_System_Forms_Backfill_Tool($plugin->qr);
    }

    public static function is_ready(): bool {
        return self::$tool instanceof PWE_System_Forms_Audit_Tool;
    }
}

PWE_System_Forms_Audit_Module::boot();
add_action('plugins_loaded', ['PWE_System_Forms_Audit_Module', 'boot'], 35);
