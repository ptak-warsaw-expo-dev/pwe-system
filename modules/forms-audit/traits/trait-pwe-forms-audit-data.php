<?php

if (!defined('ABSPATH')) {
    exit;
}

trait PWE_System_Forms_Audit_Data_Trait {
    private function get_form_registration_stats($form_id, $feeds) {
        global $wpdb;

        $stats = [
            'ok'                   => 0,
            'bad'                  => 0,
            'none'                 => 0,
            'notification_none'    => 0,
            'notification_missing' => 0,
            'notification_error'   => 0,
            'resend'               => 0,
        ];

        $form_id = absint($form_id);

        if (!$form_id) {
            return $stats;
        }

        $form = GFAPI::get_form($form_id);
        $has_active_notifications = (
            !is_wp_error($form) &&
            is_array($form) &&
            $this->form_has_active_notifications($form)
        );

        [$entry_table, $meta_table] = $this->get_table_names();

        $sql = $wpdb->prepare(
            "SELECT e.id,
                    qm.meta_value AS pwe_qr_code_url,
                    (
                        SELECT oqm.meta_value
                        FROM {$meta_table} oqm
                        WHERE oqm.entry_id = e.id
                          AND oqm.meta_key LIKE 'qr-code_feed_%_url'
                        ORDER BY oqm.id DESC
                        LIMIT 1
                    ) AS legacy_qr_code_url,
                    rqm.meta_value AS pwe_qr_resend_code_url
             FROM {$entry_table} e
             LEFT JOIN {$meta_table} qm
               ON qm.entry_id = e.id
              AND qm.meta_key = 'pwe_qr_code_url'
             LEFT JOIN {$meta_table} rqm
               ON rqm.entry_id = e.id
              AND rqm.meta_key = 'pwe_qr_resend_code_url'
             WHERE e.form_id = %d
               AND e.status = 'active'
             ORDER BY e.id DESC",
            $form_id
        );

        $rows = (array) $wpdb->get_results($sql, ARRAY_A);

        foreach ($rows as $row) {
            $entry_id = absint($row['id'] ?? 0);

            if (!$entry_id) {
                continue;
            }

            $saved_qr_url = (string) ($row['pwe_qr_code_url'] ?? '');

            if ($saved_qr_url === '') {
                $saved_qr_url = (string) ($row['legacy_qr_code_url'] ?? '');
            }

            $saved_value = $this->extract_qr_value($saved_qr_url);

            if ($saved_value === '') {
                $saved_value = $this->get_legacy_derived_qr_value($entry_id, $feeds);
            }

            $resend_success = (string) gform_get_meta($entry_id, 'pwe_qr_resend_success');
            $has_resend = ($resend_success === '1' || !empty($row['pwe_qr_resend_code_url']));

            $comparison_value = $saved_value;

            if (
                $resend_success === '1' &&
                !empty($row['pwe_qr_resend_code_url'])
            ) {
                $resend_value = $this->extract_qr_value(
                    (string) $row['pwe_qr_resend_code_url']
                );

                if ($resend_value !== '') {
                    $comparison_value = $resend_value;
                }
            }

            $comparison = $this->compare_entry_qr_light(
                $form_id,
                $entry_id,
                $feeds,
                $comparison_value
            );
            $has_sent_notification = $this->has_sent_notification($entry_id);
            $has_notification_error = $this->has_notification_error($entry_id);

            if (!$has_sent_notification && !$has_resend) {
                if ($has_notification_error) {
                    $stats['notification_error']++;
                } elseif (!$has_active_notifications) {
                    $stats['notification_none']++;
                } else {
                    $stats['notification_missing']++;
                }
                continue;
            }

            if (isset($stats[$comparison])) {
                $stats[$comparison]++;
            }

            if ($has_resend) {
                $stats['resend']++;
            }
        }

        return $stats;
    }


    private function get_redirect_value_for_entry($form_id, $entry_id, $feeds) {
        foreach ($feeds as $feed) {
            if (empty($feed['is_active'])) {
                continue;
            }

            [$key1, $key2] = $this->get_feed_custom_keys($feed);

            if ($key1 === '') {
                continue;
            }

            $value = $this->qr->generate_label($form_id, $entry_id, $key2, $key1);

            if ($value !== '') {
                return [
                    'feed'  => $key1,
                    'value' => $value,
                ];
            }
        }

        return ['feed' => '', 'value' => ''];
    }


    private function get_entries_page($active_form_ids, $selected_form_id, $search, $status_filter, $notification_filter, $page) {
        global $wpdb;

        if (empty($active_form_ids)) {
            return [
                'entries'   => [],
                'total'     => 0,
                'all_total' => 0,
                'counts'    => ['ok' => 0, 'bad' => 0, 'none' => 0, 'notification_none' => 0, 'notification_missing' => 0, 'notification_error' => 0, 'resend' => 0],
            ];
        }

        [$entry_table, $meta_table] = $this->get_table_names();

        $where = ["e.status = 'active'"];
        $params = [];

        if ($selected_form_id) {
            $where[] = 'e.form_id = %d';
            $params[] = $selected_form_id;
        } else {
            $ids = array_map('absint', $active_form_ids);
            $placeholders = implode(',', array_fill(0, count($ids), '%d'));
            $where[] = "e.form_id IN ({$placeholders})";
            $params = array_merge($params, $ids);
        }

        if ($search !== '') {
            if (ctype_digit($search)) {
                $where[] = '(e.id = %d OR EXISTS (
                    SELECT 1 FROM ' . $meta_table . ' sm
                    WHERE sm.entry_id = e.id
                      AND sm.meta_value LIKE %s
                ))';
                $params[] = absint($search);
                $params[] = '%' . $wpdb->esc_like($search) . '%';
            } else {
                $where[] = 'EXISTS (
                    SELECT 1 FROM ' . $meta_table . ' sm
                    WHERE sm.entry_id = e.id
                      AND sm.meta_value LIKE %s
                )';
                $params[] = '%' . $wpdb->esc_like($search) . '%';
            }
        }

        $where_sql = implode(' AND ', $where);

        $list_sql = "SELECT e.id, e.form_id, e.date_created,
                            qm.meta_value AS pwe_qr_code_url,
                            (
                                SELECT oqm.meta_value
                                FROM {$meta_table} oqm
                                WHERE oqm.entry_id = e.id
                                  AND oqm.meta_key LIKE 'qr-code_feed_%_url'
                                ORDER BY oqm.id DESC
                                LIMIT 1
                            ) AS legacy_qr_code_url,
                            rqm.meta_value AS pwe_qr_resend_code_url,
                            rsa.meta_value AS pwe_qr_resend_sent_at,
                            rsu.meta_value AS pwe_qr_resend_success,
                            nh.meta_value AS pwe_qr_notification_history
                     FROM {$entry_table} e
                     LEFT JOIN {$meta_table} qm
                       ON qm.entry_id = e.id
                      AND qm.meta_key = 'pwe_qr_code_url'
                     LEFT JOIN {$meta_table} rqm
                       ON rqm.entry_id = e.id
                      AND rqm.meta_key = 'pwe_qr_resend_code_url'
                     LEFT JOIN {$meta_table} rsa
                       ON rsa.entry_id = e.id
                      AND rsa.meta_key = 'pwe_qr_resend_sent_at'
                     LEFT JOIN {$meta_table} rsu
                       ON rsu.entry_id = e.id
                      AND rsu.meta_key = 'pwe_qr_resend_success'
                     LEFT JOIN {$meta_table} nh
                       ON nh.entry_id = e.id
                      AND nh.meta_key = 'pwe_qr_notification_history'
                     WHERE {$where_sql}
                     ORDER BY e.id DESC";

        $list_query = !empty($params) ? $wpdb->prepare($list_sql, $params) : $list_sql;
        $rows = (array) $wpdb->get_results($list_query, ARRAY_A);

        $feeds_cache = [];
        $forms_cache = [];
        $counts = ['ok' => 0, 'bad' => 0, 'none' => 0, 'notification_none' => 0, 'notification_missing' => 0, 'notification_error' => 0, 'resend' => 0];
        $filtered_rows = [];

        foreach ($rows as $row) {
            $entry_id = absint($row['id'] ?? 0);
            $form_id = absint($row['form_id'] ?? 0);

            if (!$entry_id || !$form_id) {
                continue;
            }

            if (!isset($feeds_cache[$form_id])) {
                $feeds_cache[$form_id] = $this->get_pwe_feeds($form_id);
            }

            if (!isset($forms_cache[$form_id])) {
                $form = GFAPI::get_form($form_id);
                $forms_cache[$form_id] = (!is_wp_error($form) && is_array($form))
                    ? $form
                    : [];
            }

            $saved_qr_url = (string) ($row['pwe_qr_code_url'] ?? '');

            if ($saved_qr_url === '') {
                $saved_qr_url = (string) ($row['legacy_qr_code_url'] ?? '');
            }

            $saved_value = $this->extract_qr_value($saved_qr_url);

            if ($saved_value === '') {
                $saved_value = $this->get_legacy_derived_qr_value(
                    $entry_id,
                    $feeds_cache[$form_id]
                );
            }

            $has_resend = (
                (string) ($row['pwe_qr_resend_success'] ?? '') === '1' ||
                !empty($row['pwe_qr_resend_code_url'])
            );

            $comparison_value = $saved_value;

            if (
                (string) ($row['pwe_qr_resend_success'] ?? '') === '1' &&
                !empty($row['pwe_qr_resend_code_url'])
            ) {
                $resend_value = $this->extract_qr_value(
                    (string) $row['pwe_qr_resend_code_url']
                );

                if ($resend_value !== '') {
                    $comparison_value = $resend_value;
                }
            }

            $comparison = $this->compare_entry_qr_light(
                $form_id,
                $entry_id,
                $feeds_cache[$form_id],
                $comparison_value
            );
            $has_sent_notification = $this->has_sent_notification($entry_id);
            $has_notification_error = $this->has_notification_error($entry_id);
            $has_active_notifications = $this->form_has_active_notifications($forms_cache[$form_id]);

            $row['comparison'] = $comparison;
            $row['notification_status'] = $has_resend
                ? 'resend'
                : (
                    $has_sent_notification
                        ? 'sent'
                        : ($has_notification_error ? 'error' : ($has_active_notifications ? 'missing' : 'none_configured'))
                );

            // "Nie wysłane" is intentionally separate from QR comparison.
            // If no message was sent, the client did not receive the QR and we
            // should not classify that entry as Zgodny/Rozbieżny.
            $effective_comparison = (
                !$has_sent_notification &&
                !$has_resend
            ) ? 'unsent' : $comparison;

            if ($status_filter !== '' && $effective_comparison !== $status_filter) {
                continue;
            }

            if ($notification_filter !== '' && $row['notification_status'] !== $notification_filter) {
                continue;
            }

            if ($effective_comparison === 'unsent') {
                if ($has_notification_error) {
                    $counts['notification_error']++;
                } elseif (!$has_active_notifications) {
                    $counts['notification_none']++;
                } else {
                    $counts['notification_missing']++;
                }
            } else {
                $counts[$comparison]++;

                if ($has_resend) {
                    $counts['resend']++;
                }
            }

            $filtered_rows[] = $row;
        }

        $all_total = count($rows);
        $total = count($filtered_rows);
        $offset = ($page - 1) * $this->per_page;
        $entries = array_slice($filtered_rows, $offset, $this->per_page);

        return [
            'entries'   => $entries,
            'total'     => $total,
            'all_total' => $all_total,
            'counts'    => $counts,
        ];
    }


    private function compare_entry_qr_light($form_id, $entry_id, $feeds, $saved_value) {
        if ($saved_value === '') {
            return 'none';
        }

        $active_feeds = array_values(array_filter($feeds, static function($feed) {
            return !empty($feed['is_active']);
        }));

        if (empty($active_feeds)) {
            return 'none';
        }

        foreach ($active_feeds as $feed) {
            [$key1, $key2] = $this->get_feed_custom_keys($feed);

            if ($key1 === '') {
                continue;
            }

            $expected_value = $this->qr->generate_label($form_id, $entry_id, $key2, $key1);

            if ($expected_value !== '' && hash_equals((string) $expected_value, (string) $saved_value)) {
                return 'ok';
            }
        }

        return 'bad';
    }


    private function get_pwe_feeds($form_id) {
        $all_feeds = [];

        foreach (['pwe_qr', 'qr-code'] as $addon_slug) {
            $feeds = GFAPI::get_feeds(null, $form_id, $addon_slug);

            if (is_wp_error($feeds) || empty($feeds) || !is_array($feeds)) {
                continue;
            }

            foreach ($feeds as $feed) {
                $feed['_qr_system'] = $addon_slug;
                $all_feeds[] = $feed;
            }
        }

        return $all_feeds;
    }


    private function get_feed_name($feed) {
        $meta = $feed['meta'] ?? [];

        return (string) ($meta['feedName'] ?? $meta['qr_name'] ?? '');
    }


    private function get_feed_custom_keys($feed) {
        $meta = $feed['meta'] ?? [];
        $fields = $meta['qrcodeFields'] ?? [];

        $key1 = '';
        $key2 = '';

        if (is_array($fields)) {
            if (!empty($fields[0]['custom_key']) && is_string($fields[0]['custom_key'])) {
                $key1 = trim($fields[0]['custom_key']);
            }

            if (!empty($fields[1]['custom_key']) && is_string($fields[1]['custom_key'])) {
                $key2 = trim($fields[1]['custom_key']);
            }
        }

        return [$key1, $key2];
    }


    private function get_email_field_ids($form) {
        $ids = [];

        foreach (($form['fields'] ?? []) as $field) {
            if (($field->type ?? '') === 'email') {
                $ids[] = (string) $field->id;
            }
        }

        return $ids;
    }


    private function get_entry_email($entry, $email_field_ids) {
        foreach ($email_field_ids as $field_id) {
            $value = trim((string) ($entry[$field_id] ?? ''));

            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }


    private function get_legacy_derived_qr_value($entry_id, $feeds) {
        $entry_id = absint($entry_id);

        if (!$entry_id || empty($feeds)) {
            return '';
        }

        foreach ($feeds as $feed) {
            if (($feed['_qr_system'] ?? '') !== 'qr-code' || empty($feed['is_active'])) {
                continue;
            }

            [$key1, $key2] = $this->get_feed_custom_keys($feed);

            $key1 = trim((string) $key1);
            $key2 = trim((string) $key2);

            if ($key1 === '') {
                continue;
            }

            return $key1 . $entry_id . $key2 . $entry_id;
        }

        return '';
    }


    private function get_entry_saved_qr($entry_id, $feeds) {
        $entry_id = absint($entry_id);

        if (!$entry_id) {
            return [
                'url'      => '',
                'value'    => '',
                'system'   => '',
                'feed_id'  => 0,
                'meta_key' => '',
            ];
        }

        // New PWE QR system.
        $pwe_url = (string) gform_get_meta($entry_id, 'pwe_qr_code_url');

        if ($pwe_url !== '') {
            return [
                'url'      => $pwe_url,
                'value'    => $this->extract_qr_value($pwe_url),
                'system'   => 'pwe_qr',
                'feed_id'  => 0,
                'meta_key' => 'pwe_qr_code_url',
            ];
        }

        // Legacy SpGfQRCode system stores one URL per feed:
        // qr-code_feed_{feed_id}_url.
        foreach ($feeds as $feed) {
            if (($feed['_qr_system'] ?? '') !== 'qr-code') {
                continue;
            }

            $feed_id = absint($feed['id'] ?? 0);

            if (!$feed_id) {
                continue;
            }

            $meta_key = 'qr-code_feed_' . $feed_id . '_url';
            $legacy_url = (string) gform_get_meta($entry_id, $meta_key);

            if ($legacy_url === '') {
                continue;
            }

            $derived_value = $this->get_legacy_derived_qr_value($entry_id, $feeds);

            return [
                'url'      => $legacy_url,
                'value'    => $derived_value !== ''
                    ? $derived_value
                    : $this->extract_qr_value($legacy_url),
                'system'   => 'qr-code',
                'feed_id'  => $feed_id,
                'meta_key' => $meta_key,
            ];
        }

        $derived_legacy_value = $this->get_legacy_derived_qr_value($entry_id, $feeds);

        if ($derived_legacy_value !== '') {
            return [
                'url'      => '',
                'value'    => $derived_legacy_value,
                'system'   => 'qr-code',
                'feed_id'  => 0,
                'meta_key' => '',
            ];
        }

        return [
            'url'      => '',
            'value'    => '',
            'system'   => '',
            'feed_id'  => 0,
            'meta_key' => '',
        ];
    }


    private function extract_qr_value($qr_url) {
        if ($qr_url === '') {
            return '';
        }

        $query = wp_parse_url($qr_url, PHP_URL_QUERY);
        if (!is_string($query) || $query === '') {
            return '';
        }

        parse_str($query, $args);

        return isset($args['value']) ? (string) $args['value'] : '';
    }


    private function compare_entry_qr($form_id, $entry, $feeds, $saved_value) {
        if ($saved_value === '') {
            return 'none';
        }

        $active_feeds = array_values(array_filter($feeds, static function($feed) {
            return !empty($feed['is_active']);
        }));

        if (empty($active_feeds)) {
            return 'none';
        }

        foreach ($active_feeds as $feed) {
            $name = $this->get_feed_name($feed);

            if ($name === '') {
                continue;
            }

            $data = $this->qr->get_qr_data_for_feed($name, $form_id, $entry);

            if (!empty($data['value']) && hash_equals((string) $data['value'], (string) $saved_value)) {
                return 'ok';
            }
        }

        return 'bad';
    }


    private function get_table_names() {
        global $wpdb;

        $entry_table = method_exists('GFFormsModel', 'get_entry_table_name')
            ? GFFormsModel::get_entry_table_name()
            : $wpdb->prefix . 'gf_entry';

        $meta_table = method_exists('GFFormsModel', 'get_entry_meta_table_name')
            ? GFFormsModel::get_entry_meta_table_name()
            : $wpdb->prefix . 'gf_entry_meta';

        return [$entry_table, $meta_table];
    }
}
