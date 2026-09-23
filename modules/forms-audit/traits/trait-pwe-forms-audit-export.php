<?php

if (!defined('ABSPATH')) {
    exit;
}

trait PWE_System_Forms_Audit_Export_Trait {

    public function export_mismatches_csv() {
        if (!current_user_can('manage_options')) {
            wp_die('Brak uprawnień.');
        }

        check_admin_referer('pwe_qr_export_mismatches');

        if (!class_exists('GFAPI')) {
            wp_die('Gravity Forms nie jest dostępne.');
        }

        $selected_form_id = isset($_GET['audit_form_id']) ? absint($_GET['audit_form_id']) : 0;
        $search = isset($_GET['audit_search']) ? sanitize_text_field(wp_unslash($_GET['audit_search'])) : '';

        $forms = GFAPI::get_forms(true, false, 'title', 'ASC');
        $active_forms = [];

        foreach ($forms as $form) {
            $form_id = absint($form['id'] ?? 0);

            if (!$form_id) {
                continue;
            }

            $feeds = $this->get_pwe_feeds($form_id);
            $active_feeds = array_values(array_filter($feeds, static function($feed) {
                return !empty($feed['is_active']);
            }));

            if (empty($active_feeds)) {
                continue;
            }

            $active_forms[$form_id] = [
                'form'  => $form,
                'feeds' => $active_feeds,
            ];
        }

        if ($selected_form_id) {
            if (!isset($active_forms[$selected_form_id])) {
                wp_die('Wybrany formularz nie ma aktywnego feedu pwe_qr ani qr-code.');
            }
            $active_forms = [$selected_form_id => $active_forms[$selected_form_id]];
        }

        $domain = strtolower((string) wp_parse_url(home_url('/'), PHP_URL_HOST));
        $domain_filename = preg_replace('/[^a-z0-9]+/i', '_', $domain);
        $domain_filename = trim($domain_filename, '_');
        $filename = $domain_filename . '-pwe-qr-rozbiezne-' . wp_date('Y-m-d-H-i-s') . '.csv';

        nocache_headers();
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('X-Content-Type-Options: nosniff');

        $out = fopen('php://output', 'w');

        if (!$out) {
            wp_die('Nie udało się utworzyć pliku CSV.');
        }

        // BOM sprawia, że polski Excel poprawnie rozpoznaje UTF-8.
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, [
            'Domena',
            'ID formularza',
            'Entry ID',
            'Data rejestracji',
            'E-mail',
            'Feed (QR custom_key 1)',
            'RND',
            'QR kod otrzymany przez zarejestrowanego',
            'URL QR kodu',
            'Wartość po przekierowaniu',
        ], ';');

        foreach ($active_forms as $form_id => $form_data) {
            $form = $form_data['form'];
            $feeds = $form_data['feeds'];
            $email_field_ids = $this->get_email_field_ids($form);

            $paging = ['offset' => 0, 'page_size' => 200];

            do {
                $entries = GFAPI::get_entries(
                    $form_id,
                    ['status' => 'active'],
                    ['key' => 'id', 'direction' => 'ASC'],
                    $paging
                );

                if (is_wp_error($entries) || empty($entries)) {
                    break;
                }

                foreach ($entries as $entry) {
                    $entry_id = absint($entry['id'] ?? 0);

                    if (!$entry_id) {
                        continue;
                    }

                    $email = $this->get_entry_email($entry, $email_field_ids);

                    if ($search !== '') {
                        $matches_id = ctype_digit($search) && (int) $search === $entry_id;
                        $matches_email = stripos($email, $search) !== false;

                        if (!$matches_id && !$matches_email) {
                            continue;
                        }
                    }

                    $saved_qr = $this->get_entry_saved_qr($entry_id, $feeds);
                    $qr_url = (string) ($saved_qr['url'] ?? '');
                    $saved_value = (string) ($saved_qr['value'] ?? '');

                    if ($saved_value === '' && $qr_url !== '') {
                        $saved_value = $this->extract_qr_value($qr_url);
                    }

                    if ($saved_value === '') {
                        $saved_value = $this->get_legacy_derived_qr_value($entry_id, $feeds);
                    }

                    if ($saved_value === '') {
                        continue;
                    }

                    $redirect = $this->get_redirect_value_for_entry($form_id, $entry_id, $feeds);

                    if ($redirect['value'] === '' || hash_equals((string) $redirect['value'], (string) $saved_value)) {
                        continue;
                    }

                    $rnd = '';
                    if (preg_match('/(rnd\d{5})/i', $saved_value, $rnd_match)) {
                        $rnd = $rnd_match[1];
                    }

                    fputcsv($out, [
                        $domain,
                        $form_id,
                        $entry_id,
                        $entry['date_created'] ?? '',
                        $email,
                        $redirect['feed'],
                        $rnd,
                        $saved_value,
                        $qr_url,
                        $redirect['value'],
                    ], ';');
                }

                $paging['offset'] += $paging['page_size'];
            } while (count($entries) === $paging['page_size']);
        }

        fclose($out);
        exit;
    }
}
