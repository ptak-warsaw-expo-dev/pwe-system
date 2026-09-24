<?php

if (!defined('ABSPATH')) {
    exit;
}

trait PWE_System_Forms_Audit_Render_Trait {

    private function render_styles() {
        echo '
        <style>
            .pwe-forms-audit .pwe-qr-section {
                margin-top: 24px;
            }
            .pwe-forms-audit .pwe-qr-table-wrap {
                overflow-x: auto;
                background: #fff;
                border: 1px solid #c3c4c7;
            }
            .pwe-forms-audit table.widefat {
                border: 0;
                min-width: 1100px;
            }
            .pwe-forms-audit .pwe-qr-feed {
                margin: 0 0 8px;
                padding: 8px 10px;
                background: #f6f7f7;
                border-left: 3px solid #8c8f94;
                line-height: 1.5;
            }
            .pwe-forms-audit .pwe-qr-feed:last-child {
                margin-bottom: 0;
            }
            .pwe-forms-audit .pwe-qr-feed.is-active {
                border-left-color: #00a32a;
            }
            .pwe-forms-audit .pwe-qr-feed.is-active.status-ok {
                border-left-color: #00a32a;
            }
            .pwe-forms-audit .pwe-qr-feed.is-active.status-bad,
            .pwe-forms-audit .pwe-qr-feed.is-active.status-error {
                border-left-color: #d63638;
            }
            .pwe-forms-audit .pwe-qr-feed.is-active.status-missing {
                border-left-color: #7c3aed;
            }
            .pwe-forms-audit .pwe-qr-feed.is-active.status-resend {
                border-left-color: #dba617;
            }
            .pwe-forms-audit .pwe-qr-feed.is-active.status-none {
                border-left-color: #8c8f94;
            }
            .pwe-forms-audit .pwe-qr-feed.is-inactive {
                opacity: .72;
            }
            .pwe-forms-audit .pwe-qr-code {
                font-family: monospace;
                overflow-wrap: anywhere;
            }
            .pwe-forms-audit .pwe-qr-url {
                display: inline-block;
                max-width: 430px;
                overflow-wrap: anywhere;
                word-break: break-word;
            }
            .pwe-forms-audit .pwe-qr-status {
                margin-top: 6px;
                display: inline-block;
                padding: 3px 8px;
                border-radius: 999px;
                font-weight: 600;
                white-space: nowrap;
            }
            .pwe-forms-audit .pwe-qr-status.ok {
                background: #edfaef;
                color: #116329;
            }
            .pwe-forms-audit .pwe-qr-status.bad {
                background: #fcf0f1;
                color: #8a2424;
            }
            .pwe-forms-audit .pwe-qr-status.resend {
                background: #fff3cd;
                color: #7a5a00;
margin-top: 6px;
            }
.pwe-forms-audit .pwe-qr-status.none {
                background: #f0f0f1;
                color: #50575e;
            }
            .pwe-forms-audit .pwe-qr-filters {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                align-items: center;
                margin: 12px 0;
            }
            .pwe-forms-audit .pwe-qr-filters select,
            .pwe-forms-audit .pwe-qr-filters input {
                min-height: 32px;
            }
            .pwe-forms-audit .pwe-qr-summary {
                display: flex;
                flex-wrap: wrap;
                gap: 8px 24px;
                align-items: center;
                margin: 12px 0;
            }
            .pwe-forms-audit .pwe-qr-summary-item {
                display: inline-flex;
                align-items: center;
                gap: 4px;
                white-space: nowrap;
            }
            .pwe-forms-audit .pwe-qr-summary-item.ok {
                color: #116329;
            }
            .pwe-forms-audit .pwe-qr-summary-item.bad {
                color: #8a2424;
            }
            .pwe-forms-audit .pwe-qr-summary-item.none {
                color: #50575e;
            }
            .pwe-forms-audit .pwe-qr-summary-item.notification-none {
                color: #646970;
            }
            .pwe-forms-audit .pwe-qr-summary-item.notification-missing {
                color: #7c3aed;
            }
            .pwe-forms-audit .pwe-qr-summary-item.notification-error {
                color: #b32d2e;
            }
            .pwe-forms-audit .pwe-qr-summary-item.resend {
                color: #9a6700;
            }
            .pwe-forms-audit .pwe-qr-status.none {
                background: #f0f0f1;
                color: #50575e;
            }
            .pwe-forms-audit .pwe-qr-status.resend {
                background: #fff3cd;
                color: #7a5b00;
            }
            .pwe-forms-audit .pwe-qr-status.notification-none {
                background: #f0f0f1;
                color: #50575e;
            }
            .pwe-forms-audit .pwe-qr-status.notification-missing {
                background: #f3e8ff;
                color: #7c3aed;
            }
            .pwe-forms-audit .pwe-qr-status.notification-error {
                background: #fce8e8;
                color: #b32d2e;
            }
            .pwe-forms-audit .pwe-qr-notification-error-column {
                color: #b32d2e;
            }
            .pwe-forms-audit .pwe-qr-notification-error-column strong {
                display: block;
            }
            .pwe-forms-audit .pwe-qr-notification-error-column small {
                display: block;
                margin-top: 4px;
                line-height: 1.35;
                word-break: break-word;
            }
            .pwe-forms-audit .pwe-qr-form-stats {
                display: inline-flex;
                align-items: center;
                gap: 4px;
                font-weight: 700;
                white-space: nowrap;
            }
            .pwe-forms-audit .pwe-qr-form-stats .ok {
                color: #116329;
            }
            .pwe-forms-audit .pwe-qr-form-stats .bad {
                color: #8a2424;
            }
            .pwe-forms-audit .pwe-qr-form-stats .none {
                color: #646970;
            }
            .pwe-forms-audit .pwe-qr-form-stats .notification-none {
                color: #646970;
            }
            .pwe-forms-audit .pwe-qr-form-stats .notification-missing {
                color: #7c3aed;
            }
            .pwe-forms-audit .pwe-qr-form-stats .notification-error {
                color: #b32d2e;
            }
            .pwe-forms-audit .pwe-qr-form-stats .resend {
                color: #9a6700;
            }
            .pwe-forms-audit .pwe-qr-form-stats .sep {
                color: #8c8f94;
                font-weight: 400;
            }
            .pwe-forms-audit .pwe-qr-bulk-language {
                margin: 16px 0;
                padding: 16px 18px;
                background: #fff;
                border: 1px solid #c3c4c7;
                border-left: 4px solid #2271b1;
            }
            .pwe-forms-audit .pwe-qr-bulk-language h3 {
                margin-top: 0;
            }
            .pwe-forms-audit .pwe-qr-resend-notifications {
                margin-top: 6px;
                color: #9a6700;
            }
            .pwe-forms-audit .pwe-qr-export {
                display: flex;
                flex-wrap: wrap;
                gap: 10px 14px;
                align-items: center;
                margin: 10px 0 4px;
            }
            .pwe-forms-audit .pwe-qr-export span {
                color: #646970;
            }
            .pwe-forms-audit .tablenav {
                height: auto;
                margin: 16px 0 4px;
                padding: 0;
            }
            .pwe-forms-audit .tablenav-pages {
                float: none;
                display: flex;
                justify-content: flex-end;
                align-items: center;
                margin: 0;
            }
            .pwe-forms-audit .tablenav-pages .pagination-links {
                display: flex;
                flex-wrap: wrap;
                gap: 5px;
                align-items: center;
            }
            .pwe-forms-audit .tablenav-pages .page-numbers {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 34px;
                height: 34px;
                box-sizing: border-box;
                padding: 0 10px;
                border: 1px solid #c3c4c7;
                border-radius: 4px;
                background: #fff;
                color: #2271b1;
                text-decoration: none;
                font-size: 14px;
                line-height: 1;
            }
            .pwe-forms-audit .tablenav-pages a.page-numbers:hover,
            .pwe-forms-audit .tablenav-pages a.page-numbers:focus {
                border-color: #2271b1;
                background: #f0f6fc;
                color: #135e96;
                box-shadow: none;
            }
            .pwe-forms-audit .tablenav-pages .page-numbers.current {
                border-color: #2271b1;
                background: #2271b1;
                color: #fff;
                font-weight: 600;
            }
            .pwe-forms-audit .tablenav-pages .page-numbers.dots {
                border-color: transparent;
                background: transparent;
                color: #646970;
            }
            .pwe-forms-audit .pwe-qr-resend-tools {
                display: flex;
                flex-wrap: wrap;
                gap: 10px 14px;
                align-items: center;
                margin: 14px 0;
                padding: 12px;
                background: #fff;
                border: 1px solid #c3c4c7;
            }
            .pwe-forms-audit .pwe-qr-resend-tools .description {
                color: #646970;
            }
            .pwe-forms-audit .pwe-qr-resend-result {
                font-weight: 600;
            }
            .pwe-forms-audit .pwe-qr-notification {
                line-height: 1.45;
            }
            .pwe-forms-audit .pwe-qr-notification small {
                display: block;
                color: #646970;
                margin-top: 2px;
            }
            .pwe-forms-audit .pwe-qr-bulk-check {
                width: 36px;
                text-align: center;
            }
            .pwe-forms-audit .pwe-forms-audit-loader {
                display: flex;
                align-items: center;
                gap: 14px;
                min-height: 110px;
                padding: 24px;
                margin-top: 20px;
                background: #fff;
                border: 1px solid #dcdcde;
                border-radius: 14px;
            }
            .pwe-forms-audit .pwe-forms-audit-loader strong {
                display: block;
                margin-bottom: 4px;
                font-size: 14px;
            }
            .pwe-forms-audit .pwe-forms-audit-loader p {
                margin: 0;
                color: #646970;
            }
            .pwe-forms-audit .pwe-forms-audit-loader__spinner {
                width: 28px;
                height: 28px;
                flex: 0 0 28px;
                border: 3px solid #dcdcde;
                border-top-color: #2271b1;
                border-radius: 50%;
                animation: pweFormsAuditSpin .8s linear infinite;
            }
            @keyframes pweFormsAuditSpin {
                to { transform: rotate(360deg); }
            }
            @media (max-width: 782px) {
                .pwe-forms-audit .tablenav-pages {
                    justify-content: flex-start;
                }
                .pwe-forms-audit .tablenav-pages .page-numbers {
                    min-width: 40px;
                    height: 40px;
                }
            }
        </style>';
    }


    private function render_forms_table($forms) {
        echo '<div class="pwe-qr-section">';
        echo '<h2>Aktywne formularze i feedy QR</h2>';
        echo '<p>Wyświetlane są tylko aktywne formularze, które nie znajdują się w koszu.</p>';

        echo '<div class="pwe-qr-table-wrap">';
        echo '<table class="widefat striped">';
        echo '<thead><tr>';
        echo '<th style="width:70px;">ID</th>';
        echo '<th>Formularz</th>';
        echo '<th style="width:180px;" title="Zgodne / Rozbieżne / Brak danych / Resendy">Rejestracje</th>';
        echo '<th>Feedy QR</th>';
        echo '<th style="width:190px;">QR custom_key 1</th>';
        echo '<th style="width:190px;">QR custom_key 2</th>';
        echo '</tr></thead><tbody>';

        if (empty($forms)) {
            echo '<tr><td colspan="6">Brak aktywnych formularzy.</td></tr>';
        } else {
            foreach ($forms as $form) {
                $form_id = absint($form['id'] ?? 0);
                $feeds = $this->get_pwe_feeds($form_id);

                $registration_stats = !empty($feeds)
                    ? $this->get_form_registration_stats($form_id, $feeds)
                    : [
                        'ok' => 0,
                        'bad' => 0,
                        'none' => 0,
                        'notification_none' => 0,
                        'notification_missing' => 0,
                        'notification_error' => 0,
                        'resend' => 0,
                    ];

                if (
                    (int) ($registration_stats['bad'] ?? 0) > 0 ||
                    (int) ($registration_stats['notification_error'] ?? 0) > 0
                ) {
                    $form_feed_status_class = 'status-bad';
                } elseif ((int) ($registration_stats['notification_missing'] ?? 0) > 0) {
                    $form_feed_status_class = 'status-missing';
                } elseif ((int) ($registration_stats['resend'] ?? 0) > 0) {
                    // No remaining mismatch/error/unsent items, but at least one repair
                    // was made by resend, so show the form as repaired.
                    $form_feed_status_class = 'status-resend';
                } else {
                    $form_feed_status_class = 'status-ok';
                }

                echo '<tr>';
                echo '<td>' . esc_html($form_id) . '</td>';
                echo '<td><strong>' . esc_html($form['title'] ?? ('Formularz ' . $form_id)) . '</strong></td>';

                if (!empty($feeds)) {
                    echo '<td>' . $this->render_form_registration_stats($registration_stats) . '</td>';
                } else {
                    echo '<td>—</td>';
                }

                if (empty($feeds)) {
                    echo '<td><span class="pwe-qr-status none">Brak feedu QR</span></td>';
                    echo '<td>—</td><td>—</td>';
                    echo '</tr>';
                    continue;
                }

                $feed_names = [];
                $key1_values = [];
                $key2_values = [];

                foreach ($feeds as $feed) {
                    $feed_name = $this->get_feed_name($feed);
                    $keys = $this->get_feed_custom_keys($feed);
                    $active = !empty($feed['is_active']);
                    $system = (string) ($feed['_qr_system'] ?? 'pwe_qr');
                    $feed_class = $active ? 'is-active' : 'is-inactive';

                    if ($active) {
                        $feed_class .= ' ' . $form_feed_status_class;
                    }

                    $feed_names[] =
                        '<div class="pwe-qr-feed ' . esc_attr($feed_class) . '">' .
                        '<strong>' . esc_html($feed_name ?: '(bez nazwy)') . '</strong><br>' .
                        '<code>' . esc_html($system) . '</code> · ID feedu: ' . absint($feed['id'] ?? 0) . ' · ' .
                        ($active ? 'Aktywny' : 'Nieaktywny') .
                        '</div>';

                    $key1_values[] = '<div class="pwe-qr-feed ' . esc_attr($feed_class) . '"><code>' . esc_html($keys[0] ?: '—') . '</code></div>';
                    $key2_values[] = '<div class="pwe-qr-feed ' . esc_attr($feed_class) . '"><code>' . esc_html($keys[1] ?: '—') . '</code></div>';
                }

                echo '<td>' . implode('', $feed_names) . '</td>';
                echo '<td>' . implode('', $key1_values) . '</td>';
                echo '<td>' . implode('', $key2_values) . '</td>';
                echo '</tr>';
            }
        }

        echo '</tbody></table></div>';
        echo '</div>';
    }

    private function render_form_registration_stats($stats) {
        return '<span class="pwe-qr-form-stats" title="Zgodne / Rozbieżne / Brak danych / Brak powiadomień / Nie wysłane / Błędy / Resendy">' .
            '<span class="ok">' . number_format_i18n((int) ($stats['ok'] ?? 0)) . '</span>' .
            '<span class="sep">/</span>' .
            '<span class="bad">' . number_format_i18n((int) ($stats['bad'] ?? 0)) . '</span>' .
            '<span class="sep">/</span>' .
            '<span class="none">' . number_format_i18n((int) ($stats['none'] ?? 0)) . '</span>' .
            '<span class="sep">/</span>' .
            '<span class="notification-none">' . number_format_i18n((int) ($stats['notification_none'] ?? 0)) . '</span>' .
            '<span class="sep">/</span>' .
            '<span class="notification-missing">' . number_format_i18n((int) ($stats['notification_missing'] ?? 0)) . '</span>' .
            '<span class="sep">/</span>' .
            '<span class="notification-error">' . number_format_i18n((int) ($stats['notification_error'] ?? 0)) . '</span>' .
            '<span class="sep">/</span>' .
            '<span class="resend">' . number_format_i18n((int) ($stats['resend'] ?? 0)) . '</span>' .
            '</span>';
    }


    private function render_entries_table($forms) {
        $active_forms = [];

        foreach ($forms as $form) {
            $form_id = absint($form['id'] ?? 0);

            if (!$form_id) {
                continue;
            }

            // W sekcji rejestracji pokazujemy wyłącznie wpisy z formularzy,
            // które mają co najmniej jeden feed PWE QR.
            $feeds = $this->get_pwe_feeds($form_id);

            if (empty($feeds)) {
                continue;
            }

            $active_forms[$form_id] = $form;
        }

        $selected_form_id = isset($_GET['audit_form_id']) ? absint($_GET['audit_form_id']) : 0;
        if ($selected_form_id && !isset($active_forms[$selected_form_id])) {
            $selected_form_id = 0;
        }

        $search = isset($_GET['audit_search']) ? sanitize_text_field(wp_unslash($_GET['audit_search'])) : '';
        $status_filter = isset($_GET['audit_status']) ? sanitize_key(wp_unslash($_GET['audit_status'])) : '';
        $notification_filter = isset($_GET['audit_notification']) ? sanitize_key(wp_unslash($_GET['audit_notification'])) : '';

        if (!in_array($status_filter, ['', 'ok', 'bad', 'none'], true)) {
            $status_filter = '';
        }

        if (!in_array($notification_filter, ['', 'none_configured', 'sent', 'missing', 'error', 'resend'], true)) {
            $notification_filter = '';
        }

        $per_page = isset($_GET['audit_per_page']) ? absint($_GET['audit_per_page']) : 100;

        if (!in_array($per_page, [100, 200, 300, 500], true)) {
            $per_page = 100;
        }

        $this->per_page = $per_page;

        $page = max(1, isset($_GET['audit_paged']) ? absint($_GET['audit_paged']) : 1);

        $data = $this->get_entries_page(
            array_keys($active_forms),
            $selected_form_id,
            $search,
            $status_filter,
            $notification_filter,
            $page
        );

        echo '<div class="pwe-qr-section">';
        echo '<h2>Rejestracje i zapisane kody QR</h2>';
        echo '<p>Pokazywane są aktywne wpisy tylko z formularzy, które mają feed pwe_qr lub qr-code. Tabela jest stronicowana po ' . absint($this->per_page) . ' wpisów.</p>';

        $this->render_filters($active_forms, $selected_form_id, $search, $status_filter, $notification_filter, $per_page);
        $this->render_export_button($selected_form_id, $search);

        echo '<div class="pwe-qr-summary">';
        echo '<span class="pwe-qr-summary-item">Znaleziono wpisów: ' . number_format_i18n($data['total']) . '</span>';
        echo '<span class="pwe-qr-summary-item ok">Zgodne: ' . number_format_i18n($data['counts']['ok']) . '</span>';
        echo '<span class="pwe-qr-summary-item bad">Rozbieżne: ' . number_format_i18n($data['counts']['bad']) . '</span>';
        echo '<span class="pwe-qr-summary-item none">Brak danych: ' . number_format_i18n($data['counts']['none']) . '</span>';
        echo '<span class="pwe-qr-summary-item notification-none">Brak powiadomień: ' . number_format_i18n($data['counts']['notification_none']) . '</span>';
        echo '<span class="pwe-qr-summary-item notification-missing">Nie wysłane: ' . number_format_i18n($data['counts']['notification_missing']) . '</span>';
        echo '<span class="pwe-qr-summary-item notification-error">Błędy: ' . number_format_i18n($data['counts']['notification_error']) . '</span>';
        echo '<span class="pwe-qr-summary-item resend">Resendy: ' . number_format_i18n($data['counts']['resend']) . '</span>';
        echo '</div>';

        $this->render_resend_tools(
            $selected_form_id,
            $status_filter,
            $notification_filter,
            $search
        );

        echo '<div class="pwe-qr-table-wrap">';
        echo '<table class="widefat striped">';
        echo '<thead><tr>';
        echo '<th class="pwe-qr-bulk-check"><input type="checkbox" id="pwe-qr-select-page" aria-label="Zaznacz wszystkie możliwe do ponownej wysyłki"></th>';
        echo '<th style="width:140px;">Formularz</th>';
        echo '<th style="width:85px;">Entry ID</th>';
        echo '<th style="width:150px;">Data</th>';
        echo '<th style="width:220px;">E-mail</th>';
        echo '<th style="width:330px;">Feedy / custom_key</th>';
        echo '<th style="width:260px;">QR, który dostał wpis</th>';
        echo '<th style="width:260px;">Powiadomienie</th>';
        echo '<th style="width:110px;">Porównanie</th>';
        echo '</tr></thead><tbody>';

        if (empty($data['entries'])) {
            echo '<tr><td colspan="9">Brak wpisów dla wybranych filtrów.</td></tr>';
        } else {
            $forms_cache = [];
            $feeds_cache = [];
            $email_fields_cache = [];

            foreach ($data['entries'] as $entry_row) {
                $entry_id = absint($entry_row['id'] ?? 0);
                $form_id = absint($entry_row['form_id'] ?? 0);

                if (!$entry_id || !$form_id || !isset($active_forms[$form_id])) {
                    continue;
                }

                if (!isset($forms_cache[$form_id])) {
                    $forms_cache[$form_id] = $active_forms[$form_id];
                    $feeds_cache[$form_id] = $this->get_pwe_feeds($form_id);
                    $email_fields_cache[$form_id] = $this->get_email_field_ids($forms_cache[$form_id]);
                }

                $entry = GFAPI::get_entry($entry_id);
                if (is_wp_error($entry)) {
                    continue;
                }

                $email = $this->get_entry_email($entry, $email_fields_cache[$form_id]);
                $saved_qr = $this->get_entry_saved_qr($entry_id, $feeds_cache[$form_id]);
                $qr_url = (string) ($saved_qr['url'] ?? '');
                $qr_value = (string) ($saved_qr['value'] ?? '');

                if ($qr_value === '' && $qr_url !== '') {
                    $qr_value = $this->extract_qr_value($qr_url);
                }
                $resend_qr_url = (string) gform_get_meta($entry_id, 'pwe_qr_resend_code_url');
                $resend_qr_value = $this->extract_qr_value($resend_qr_url);
                $resend_sent_at = (string) gform_get_meta($entry_id, 'pwe_qr_resend_sent_at');
                $resend_success = (string) gform_get_meta($entry_id, 'pwe_qr_resend_success');
                $resend_notification_names = gform_get_meta($entry_id, 'pwe_qr_resend_notification_names');

                if (!is_array($resend_notification_names)) {
                    $resend_notification_names = [];
                }

                $delivery_state = $this->get_notification_delivery_state($forms_cache[$form_id], $entry_id);
                $has_sent_notification = !empty($delivery_state['sent']);
                $has_resend_notification = !empty($delivery_state['resend']);
                $has_audit_resend = ($resend_success === '1' || $resend_qr_url !== '');
                $resend_notification_names = array_values(array_unique(array_filter(array_merge(
                    $resend_notification_names,
                    (array) ($delivery_state['resend_names'] ?? [])
                ), 'strlen')));
                $has_notification_error = $this->has_active_notification_error($forms_cache[$form_id], $entry_id);
                $has_active_notifications = $this->form_has_active_notifications($forms_cache[$form_id]);
                $notification_error_message = $has_notification_error
                    ? $this->get_notification_error_message($entry_id)
                    : '';
                $comparison_value = (
                    $resend_success === '1' &&
                    $resend_qr_value !== ''
                )
                    ? $resend_qr_value
                    : $qr_value;

                // The list query already calculates the comparison using the feed's saved
                // custom_key values. This is especially important for legacy `qr-code` feeds:
                // recalculating through get_qr_data_for_feed() may use only the new pwe_qr
                // provider and incorrectly mark a valid legacy QR as a mismatch.
                $comparison = isset($entry_row['comparison'])
                    ? (string) $entry_row['comparison']
                    : $this->compare_entry_qr(
                        $form_id,
                        $entry,
                        $feeds_cache[$form_id],
                        $comparison_value
                    );

                $notification_match = $this->get_notification_for_entry($forms_cache[$form_id], $entry, $email);

                $resend_notifications = $this->get_resend_notifications_for_entry(
                    $forms_cache[$form_id],
                    $entry,
                    $comparison
                );

                $can_resend_auto = !empty($resend_notifications);

                echo '<tr>';
                echo '<td class="pwe-qr-bulk-check">';

                if ($can_resend_auto) {
                    $notification_ids = array_values(array_filter(array_map(
                        static function($notification) {
                            return (string) ($notification['id'] ?? '');
                        },
                        $resend_notifications
                    )));

                    echo '<input type="checkbox" class="pwe-qr-resend-entry" data-entry-id="' . esc_attr($entry_id) . '" data-notification-ids="' . esc_attr(wp_json_encode($notification_ids)) . '" data-manual="1" aria-label="Zaznacz wpis ' . esc_attr($entry_id) . '">';
                } else {
                    echo '—';
                }

                echo '</td>';
                echo '<td><strong>' . esc_html($forms_cache[$form_id]['title'] ?? ('Formularz ' . $form_id)) . '</strong><br>ID ' . esc_html($form_id) . '</td>';
                echo '<td><a href="' . esc_url(admin_url('admin.php?page=gf_entries&view=entry&id=' . $form_id . '&lid=' . $entry_id)) . '"><strong>' . esc_html($entry_id) . '</strong></a></td>';
                echo '<td>' . esc_html($entry_row['date_created'] ?? '') . '</td>';
                echo '<td>' . ($email !== '' ? esc_html($email) : '—') . '</td>';

                $has_confirmed_resend = ($has_resend_notification || $has_audit_resend);

                if ($has_confirmed_resend) {
                    // Resend is the final state for feed coloring, regardless of the
                    // original status before the repair.
                    $feed_status_class = 'status-resend';
                } elseif (!$has_sent_notification) {
                    if ($has_notification_error) {
                        $feed_status_class = 'status-error';
                    } elseif (!$has_active_notifications) {
                        $feed_status_class = 'status-none';
                    } else {
                        $feed_status_class = 'status-missing';
                    }
                } elseif ($comparison === 'ok') {
                    $feed_status_class = 'status-ok';
                } elseif ($comparison === 'bad') {
                    $feed_status_class = 'status-bad';
                } else {
                    $feed_status_class = 'status-none';
                }

                echo '<td>' . $this->render_feeds_for_entry(
                    $feeds_cache[$form_id],
                    $feed_status_class
                ) . '</td>';
                echo '<td>' . $this->render_saved_qr_history($qr_url, $qr_value, $resend_qr_url, $resend_qr_value) . '</td>';
                echo '<td>';

                if ($has_notification_error && !$has_sent_notification && !$has_confirmed_resend) {
                    echo '<div class="pwe-qr-notification-error-column">';
                    echo '<strong>Błąd wysyłki</strong>';

                    if ($notification_error_message !== '') {
                        echo '<small>' . esc_html($notification_error_message) . '</small>';
                    }

                    echo '</div>';
                } elseif (!$has_sent_notification && !$has_active_notifications) {
                    echo '<span class="pwe-qr-status notification-none">Brak aktywnych powiadomień w formularzu</span>';
                } else {
                    echo $this->render_notification_column(
                        $notification_match,
                        $resend_notification_names,
                        $forms_cache[$form_id],
                        $entry_id
                    );
                }

                echo '</td>';
                echo '<td>';

                if (!$has_sent_notification && !$has_audit_resend) {
                    if ($has_notification_error) {
                        echo '<span class="pwe-qr-status notification-error">Błąd wysyłki</span>';
                    } elseif (!$has_active_notifications) {
                        echo '<span class="pwe-qr-status notification-none">Brak powiadomień</span>';
                    } else {
                        echo '<span class="pwe-qr-status notification-missing">Nie wysłane</span>';
                    }
                } else {
                    // Audit resend is a repair, so show the resulting QR comparison.
                    echo $this->render_comparison_status($comparison);
                }

                // Resend is always a second, independent status. This applies both to
                // the audit resend and to the standalone Resend module.
                if ($has_confirmed_resend) {
                    echo ' <span class="pwe-qr-status resend">Resend</span>';
                }

                echo '</td>';
                echo '</tr>';
            }
        }

        echo '</tbody></table></div>';

        $this->render_pagination(
            $data['total'],
            $page,
            $selected_form_id,
            $search,
            $status_filter,
            $notification_filter,
            $per_page
        );

        echo '</div>';
    }


    private function render_resend_tools(
        $selected_form_id = 0,
        $status_filter = '',
        $notification_filter = '',
        $search = ''
    ) {
        $nonce = wp_create_nonce('pwe_qr_resend_notifications');
        $bulk_nonce = wp_create_nonce('pwe_qr_bulk_language_preview');

        echo '<div class="pwe-qr-resend-tools">';
        echo '<button type="button" class="button button-primary" id="pwe-qr-resend-selected" disabled>Wyślij ponownie zaznaczone powiadomienia</button>';
        echo '<span class="description">Powiadomienia do resendu są dobierane automatycznie. Dla wpisów bez historii język wynika ze źródłowego URL.</span>';
        echo '<span class="pwe-qr-resend-result" id="pwe-qr-resend-result"></span>';
        echo '</div>';

        if ($selected_form_id) {
            echo '<div class="pwe-qr-bulk-language">';
            echo '<h3>Masowa wysyłka powiadomień</h3>';
            echo '<p>Uwzględnia aktualnie wybrany formularz i filtry. Obsługuje m.in. <strong>Błąd wysyłki</strong>, <strong>Nie wysłane</strong> oraz wpisy wymagające resendu QR. Przed wysyłką zobaczysz dokładny plan powiadomień.</p>';
            echo '<button type="button" class="button button-secondary" id="pwe-qr-bulk-language-preview" ' .
                'data-form-id="' . absint($selected_form_id) . '" ' .
                'data-status-filter="' . esc_attr($status_filter) . '" ' .
                'data-notification-filter="' . esc_attr($notification_filter) . '" ' .
                'data-search="' . esc_attr($search) . '">' .
                'Przygotuj masową wysyłkę</button>';
            echo '<div id="pwe-qr-bulk-language-result" style="margin-top:12px;"></div>';
            echo '</div>';
        }

        echo '<script>
        jQuery(function($) {
            const $selectAll = $("#pwe-qr-select-page");
            const $button = $("#pwe-qr-resend-selected");
            const $result = $("#pwe-qr-resend-result");
            const nonce = ' . wp_json_encode($nonce) . ';
            const bulkNonce = ' . wp_json_encode($bulk_nonce) . ';
            let bulkEntries = [];

            function selectedItems() {
                return $(".pwe-qr-resend-entry:checked").map(function() {
                    const $checkbox = $(this);

                    let notificationIds = [];

                    try {
                        notificationIds = JSON.parse(String($checkbox.attr("data-notification-ids") || "[]"));
                    } catch (e) {
                        notificationIds = [];
                    }

                    return {
                        entry_id: parseInt($checkbox.data("entry-id"), 10) || 0,
                        notification_ids: Array.isArray(notificationIds) ? notificationIds : [],
                        manual: String($checkbox.attr("data-manual") || "0") === "1" ? 1 : 0
                    };
                }).get().filter(function(item) {
                    return item.entry_id > 0 && item.notification_ids.length > 0;
                });
            }

            function updateButton() {
                $button.prop("disabled", selectedItems().length === 0);
            }

            function sendQueue(items, $status, doneCallback) {
                let queue = items.slice();
                let sent = 0;
                let failed = 0;
                let errors = [];

                function runNextBatch() {
                    if (!queue.length) {
                        let resultText = "Zakończono. Wysłano: " + sent + ", błędy: " + failed + ".";

                        if (errors.length) {
                            resultText += " " + errors.slice(0, 5).join(" | ");
                            if (errors.length > 5) {
                                resultText += " | +" + (errors.length - 5) + " kolejnych błędów";
                            }
                        }

                        $status.text(resultText);

                        if (typeof doneCallback === "function") {
                            doneCallback();
                        }

                        return;
                    }

                    const batch = queue.splice(0, 10);
                    $status.text("Wysyłanie… " + sent + " / " + items.length);

                    $.post(ajaxurl, {
                        action: "pwe_qr_resend_notifications",
                        nonce: nonce,
                        items: JSON.stringify(batch)
                    }).done(function(response) {
                        if (response && response.success && response.data) {
                            sent += parseInt(response.data.sent || 0, 10);
                            failed += parseInt(response.data.failed || 0, 10);

                            if (Array.isArray(response.data.errors) && response.data.errors.length) {
                                errors = errors.concat(response.data.errors);
                            }
                        } else {
                            failed += batch.length;
                        }
                    }).fail(function() {
                        failed += batch.length;
                    }).always(function() {
                        runNextBatch();
                    });
                }

                runNextBatch();
            }

            $selectAll.on("change", function() {
                $(".pwe-qr-resend-entry:not(:disabled)").prop("checked", this.checked);
                updateButton();
            });


            $(document).off("change.pweQrAuditResend", ".pwe-qr-resend-entry");
            $(document).on("change.pweQrAuditResend", ".pwe-qr-resend-entry", function() {
                const total = $(".pwe-qr-resend-entry").length;
                const checked = $(".pwe-qr-resend-entry:checked").length;
                $selectAll.prop("checked", total > 0 && checked === total);
                $selectAll.prop("indeterminate", checked > 0 && checked < total);
                updateButton();
            });

            $button.on("click", function() {
                const items = selectedItems();

                if (!items.length) {
                    return;
                }

                if (!window.confirm("Ponownie wysłać " + items.length + " powiadomień?")) {
                    return;
                }

                $button.prop("disabled", true);
                $selectAll.prop("disabled", true);
                $(".pwe-qr-resend-entry").prop("disabled", true);

                sendQueue(items, $result, function() {
                    $(".pwe-qr-resend-entry").prop("disabled", false);
                    $selectAll.prop("disabled", false);
                    updateButton();
                });
            });

            function renderBulkPlan(data) {
                const $target = $("#pwe-qr-bulk-language-result");
                bulkEntries = Array.isArray(data.entries) ? data.entries : [];
                const groups = Array.isArray(data.groups) ? data.groups : [];

                if (!bulkEntries.length) {
                    $target.html("<p><strong>Brak wpisów możliwych do ponownej wysyłki dla aktualnych filtrów.</strong></p>");
                    return;
                }

                let html = "<div class=\"pwe-qr-bulk-plan\"><p><strong>Do wysyłki: " + bulkEntries.length + " wpisów.</strong></p>";
                html += "<table class=\"widefat striped\" style=\"max-width:900px; min-width:0;\"><thead><tr><th>Wpisy</th><th>Powiadomienia, które zostaną wysłane</th></tr></thead><tbody>";

                groups.forEach(function(group) {
                    const notifications = Array.isArray(group.notifications) ? group.notifications : [];

                    html += "<tr><td><strong>" + parseInt(group.count || 0, 10) + "</strong></td><td>";
                    html += notifications.length
                        ? notifications.map(function(name) {
                            return $("<div>").text(name).html();
                        }).join("<br>")
                        : "<span style=\"color:#b32d2e;\">Brak powiadomienia</span>";
                    html += "</td></tr>";
                });

                html += "</tbody></table>";
                html += "<div id=\"pwe-qr-bulk-plan-summary\" style=\"margin-top:12px;\"></div>";
                html += "<button type=\"button\" class=\"button button-primary\" id=\"pwe-qr-bulk-language-send\">Wyślij masowo</button> ";
                html += "<span id=\"pwe-qr-bulk-language-send-status\"></span>";
                html += "</div>";

                $target.html(html);

                const lines = groups.map(function(group) {
                    const names = Array.isArray(group.notifications)
                        ? group.notifications.join(" + ")
                        : "";

                    return "<strong>" + parseInt(group.count || 0, 10) + " wpisów:</strong> " +
                        $("<div>").text(names).html();
                });

                $("#pwe-qr-bulk-plan-summary").html(
                    "<p><strong>Plan wysyłki:</strong><br>" + lines.join("<br>") + "</p>"
                );
            }

            $("#pwe-qr-bulk-language-preview").on("click", function() {
                const $previewButton = $(this);
                const formId = parseInt($previewButton.data("form-id"), 10) || 0;
                const $target = $("#pwe-qr-bulk-language-result");

                if (!formId) {
                    return;
                }

                $previewButton.prop("disabled", true);
                $target.text("Analizuję wpisy i języki…");

                $.post(ajaxurl, {
                    action: "pwe_qr_bulk_language_preview",
                    nonce: bulkNonce,
                    form_id: formId,
                    status_filter: String($previewButton.data("status-filter") || ""),
                    notification_filter: String($previewButton.data("notification-filter") || ""),
                    search: String($previewButton.data("search") || "")
                }).done(function(response) {
                    if (response && response.success && response.data) {
                        renderBulkPlan(response.data);
                    } else {
                        $target.text("Nie udało się przygotować planu wysyłki.");
                    }
                }).fail(function() {
                    $target.text("Nie udało się przygotować planu wysyłki.");
                }).always(function() {
                    $previewButton.prop("disabled", false);
                });
            });

            $(document).off("click.pweQrAuditBulk", "#pwe-qr-bulk-language-send");
            $(document).on("click.pweQrAuditBulk", "#pwe-qr-bulk-language-send", function() {
                const $sendButton = $(this);
                const $status = $("#pwe-qr-bulk-language-send-status");
                const grouped = {};

                const items = bulkEntries.map(function(entry) {
                    const ids = Array.isArray(entry.notification_ids)
                        ? entry.notification_ids.map(String).filter(Boolean)
                        : [];

                    const names = Array.isArray(entry.notifications)
                        ? entry.notifications.map(String).filter(Boolean)
                        : [];

                    const groupKey = ids.slice().sort().join("|");

                    if (!grouped[groupKey]) {
                        grouped[groupKey] = {
                            count: 0,
                            name: names.join(" + ")
                        };
                    }

                    grouped[groupKey].count++;

                    return {
                        entry_id: parseInt(entry.entry_id, 10) || 0,
                        notification_ids: ids,
                        manual: 1
                    };
                }).filter(function(item) {
                    return item.entry_id > 0 && item.notification_ids.length > 0;
                });

                if (!items.length || items.length !== bulkEntries.length) {
                    return;
                }

                const planLines = Object.keys(grouped).map(function(key) {
                    return grouped[key].count + " × " + grouped[key].name;
                });

                const confirmText = "Zostaną obsłużone " + items.length + " wpisy.\\n\\n" +
                    planLines.join("\\n") +
                    "\\n\\nKontynuować?";

                if (!window.confirm(confirmText)) {
                    return;
                }

                $sendButton.prop("disabled", true);

                sendQueue(items, $status, function() {
                    $sendButton.prop("disabled", false);
                });
            });

            updateButton();
        });
        </script>';
    }


    private function render_notification_column($match, $resend_notification_names = [], $form = [], $entry_id = 0) {
        $html = $this->render_notification_match($match);

        if (!empty($resend_notification_names) && is_array($resend_notification_names)) {
            $names = array_values(array_filter(array_map('strval', $resend_notification_names)));

            if (!empty($names)) {
                $html .= '<div class="pwe-qr-resend-notifications">' .
                    '<strong>Resend:</strong> ' .
                    esc_html(implode(' + ', $names)) .
                    '</div>';
            }
        }

        return $html;
    }


    private function render_notification_match($match) {
        if (!empty($match['ambiguous'])) {
            $names = array_values($match['candidates'] ?? []);

            $html = '<div class="pwe-qr-notification"><span class="pwe-qr-status none">Niejednoznaczne</span><small>' .
                esc_html(implode(', ', $names)) .
                '</small>';

            $html .= '</div>';

            return $html;
        }

        if (empty($match['id'])) {
            $html = '<div class="pwe-qr-notification"><span class="pwe-qr-status none">Nie ustalono</span><small>Brak historycznego zapisu i brak jednoznacznego powiadomienia do tego e-maila.</small>';

            $html .= '</div>';

            return $html;
        }

        if (
            ($match['source'] ?? '') === 'history' ||
            ($match['source'] ?? '') === 'gf_notes'
        ) {
            $source = 'zapisane przy wysyłce';
        } elseif (($match['source'] ?? '') === 'source_url') {
            $source = 'dobrane automatycznie z języka źródłowego URL: ' . esc_html($match['lang'] ?? '');
        } elseif (($match['source'] ?? '') === 'entry_lang') {
            $source = 'dobrane z pola lang wpisu: ' . esc_html($match['lang'] ?? '');
        } elseif (($match['source'] ?? '') === 'conditional_logic') {
            $source = 'dobrane z aktywnych powiadomień i logiki warunkowej dla tego wpisu';
        } else {
            $source = 'wykryte z aktualnej konfiguracji formularza';
        }

        $names = !empty($match['names']) && is_array($match['names'])
            ? $match['names']
            : [($match['name'] ?? '')];

        $names = array_values(array_filter(array_map('strval', $names)));

        return '<div class="pwe-qr-notification"><strong>' .
            esc_html(implode(' + ', $names)) .
            '</strong><small>' . esc_html($source) . '</small></div>';
    }


    private function render_filters($active_forms, $selected_form_id, $search, $status_filter, $notification_filter, $per_page) {
        echo '<form method="get" class="pwe-qr-filters">';
        echo '<input type="hidden" name="page" value="pwe-system-forms-audit">';

        echo '<select name="audit_form_id">';
        echo '<option value="0">Wszystkie formularze z feedem QR</option>';

        foreach ($active_forms as $form_id => $form) {
            echo '<option value="' . absint($form_id) . '" ' . selected($selected_form_id, $form_id, false) . '>' .
                esc_html(($form['title'] ?? ('Formularz ' . $form_id)) . ' (ID ' . $form_id . ')') .
                '</option>';
        }

        echo '</select>';

        echo '<select name="audit_status">';
        echo '<option value="" ' . selected($status_filter, '', false) . '>Wszystkie statusy QR</option>';
        echo '<option value="ok" ' . selected($status_filter, 'ok', false) . '>Zgodne</option>';
        echo '<option value="bad" ' . selected($status_filter, 'bad', false) . '>Rozbieżne</option>';
        echo '<option value="none" ' . selected($status_filter, 'none', false) . '>Brak danych</option>';
        echo '</select>';

        echo '<select name="audit_notification">';
        echo '<option value="" ' . selected($notification_filter, '', false) . '>Wszystkie powiadomienia</option>';
        echo '<option value="none_configured" ' . selected($notification_filter, 'none_configured', false) . '>Brak powiadomień</option>';
        echo '<option value="missing" ' . selected($notification_filter, 'missing', false) . '>Nie wysłane</option>';
        echo '<option value="error" ' . selected($notification_filter, 'error', false) . '>Błąd wysyłki</option>';
        echo '<option value="sent" ' . selected($notification_filter, 'sent', false) . '>Powiadomienie wysłane</option>';
        echo '<option value="resend" ' . selected($notification_filter, 'resend', false) . '>Resend wysłany</option>';
        echo '</select>';

        echo '<select name="audit_per_page" title="Liczba wpisów na stronę">';
        foreach ([100, 200, 300, 500] as $page_size) {
            echo '<option value="' . absint($page_size) . '" ' . selected($per_page, $page_size, false) . '>' .
                absint($page_size) . ' / strona</option>';
        }
        echo '</select>';

        echo '<input type="search" name="audit_search" value="' . esc_attr($search) . '" placeholder="Entry ID lub e-mail">';
        echo '<button type="submit" class="button button-secondary">Filtruj</button>';
        echo '<button type="button" class="button pwe-forms-audit-refresh" title="Wyczyść pamięć podręczną audytu i przelicz wszystkie wpisy ponownie">Odśwież dane</button>';

        if ($selected_form_id || $search !== '' || $status_filter !== '' || $notification_filter !== '') {
            echo '<a class="button" href="' . esc_url(admin_url('admin.php?page=pwe-system-forms-audit')) . '">Wyczyść</a>';
        }

        echo '</form>';
    }


    private function render_export_button($selected_form_id, $search) {
        $url = wp_nonce_url(
            add_query_arg(
                [
                    'action'        => 'pwe_qr_export_mismatches',
                    'audit_form_id' => $selected_form_id ?: 0,
                    'audit_search'  => $search !== '' ? $search : '',
                ],
                admin_url('admin-post.php')
            ),
            'pwe_qr_export_mismatches'
        );

        echo '<div class="pwe-qr-export">';
        echo '<a class="button button-primary" href="' . esc_url($url) . '">Eksportuj rozbieżne CSV</a>';
        echo '<span>Eksport uwzględnia wybrany formularz i wyszukiwanie. Status jest zawsze ograniczony do wpisów rozbieżnych.</span>';
        echo '</div>';
    }


    private function render_pagination($total, $page, $selected_form_id, $search, $status_filter, $notification_filter, $per_page) {
        $total_pages = max(1, (int) ceil($total / $this->per_page));

        if ($total_pages <= 1) {
            return;
        }

        $base_url = add_query_arg(
            [
                'page'          => 'pwe-system-forms-audit',
                'audit_form_id' => $selected_form_id ?: false,
                'audit_search'  => $search !== '' ? $search : false,
                'audit_status'       => $status_filter !== '' ? $status_filter : false,
                'audit_notification' => $notification_filter !== '' ? $notification_filter : false,
                'audit_per_page'     => $per_page,
                'audit_paged'        => '%#%',
            ],
            admin_url('admin.php')
        );

        $links = paginate_links([
            'base'      => $base_url,
            'format'    => '',
            'current'   => $page,
            'total'     => $total_pages,
            'type'      => 'array',
            'prev_text' => '‹',
            'next_text' => '›',
        ]);

        if (empty($links)) {
            return;
        }

        echo '<div class="tablenav"><div class="tablenav-pages"><span class="pagination-links">';
        echo implode('', array_map('wp_kses_post', $links));
        echo '</span></div></div>';
    }


    private function render_feeds_for_entry($feeds, $status_class = '') {
        if (empty($feeds)) {
            return '<span class="pwe-qr-status none">Brak feedu QR</span>';
        }

        $html = '';

        foreach ($feeds as $feed) {
            $name = $this->get_feed_name($feed);
            $keys = $this->get_feed_custom_keys($feed);
            $active = !empty($feed['is_active']);
            $system = (string) ($feed['_qr_system'] ?? 'pwe_qr');
            $feed_class = $active ? 'is-active' : 'is-inactive';

            if ($active && $status_class !== '') {
                $feed_class .= ' ' . sanitize_html_class($status_class);
            }

            $html .= '<div class="pwe-qr-feed ' . esc_attr($feed_class) . '">';
            $html .= '<strong>' . esc_html($name ?: '(bez nazwy)') . '</strong> · <code>' . esc_html($system) . '</code> · ' . ($active ? 'Aktywny' : 'Nieaktywny') . '<br>';
            $html .= 'key 1: <code>' . esc_html($keys[0] ?: '—') . '</code><br>';
            $html .= 'key 2: <code>' . esc_html($keys[1] ?: '—') . '</code>';
            $html .= '</div>';
        }

        return $html;
    }


    private function render_saved_qr_history($qr_url, $qr_value, $resend_qr_url, $resend_qr_value) {
        $html = '<div class="pwe-qr-history">';
        $html .= '<div>';
        $html .= $this->render_saved_qr($qr_url, $qr_value);
        $html .= '</div>';

        if ($resend_qr_url !== '' || $resend_qr_value !== '') {
            $html .= '<div style="margin-top:10px;padding-top:10px;border-top:1px solid #dcdcde;">';
            $html .= '<strong>QR z resendu</strong><br>';
            $html .= $this->render_saved_qr($resend_qr_url, $resend_qr_value);

            $resend_sent_at = '';
            if ($resend_qr_value !== '') {
                // Date is stored separately; the entry ID is not needed here because
                // this helper is deliberately presentation-only.
            }

            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }


    private function render_saved_qr($qr_url, $qr_value) {
        if ($qr_url === '' && $qr_value === '') {
            return '<span class="pwe-qr-status none">Brak zapisanego QR</span>';
        }

        $html = '';

        if ($qr_value !== '') {
            $html .= '<div class="pwe-qr-code"><strong>' . esc_html($qr_value) . '</strong></div>';
        }

        if ($qr_url !== '') {
            $html .= '<a class="pwe-qr-url" href="' . esc_url($qr_url) . '" target="_blank" rel="noopener noreferrer">Otwórz zapisany QR</a>';
        } else {
            $html .= '<small>wartość wyliczona z feedu qr-code</small>';
        }

        return $html;
    }


    private function render_comparison_status($status) {
        if ($status === 'ok') {
            return '<span class="pwe-qr-status ok">Zgodny</span>';
        }

        if ($status === 'bad') {
            return '<span class="pwe-qr-status bad">Rozbieżny</span>';
        }

        return '<span class="pwe-qr-status none">Brak danych</span>';
    }
}
