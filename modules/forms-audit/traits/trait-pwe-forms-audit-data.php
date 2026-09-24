<?php

if (!defined('ABSPATH')) {
    exit;
}

trait PWE_System_Forms_Audit_Data_Trait {

    private function get_audit_session_cache_key($active_form_ids) {
        $ids = array_values(array_unique(array_filter(array_map('absint', (array) $active_form_ids))));
        sort($ids, SORT_NUMERIC);

        $token = function_exists('wp_get_session_token') ? (string) wp_get_session_token() : '';
        $seed = get_current_user_id() . '|' . $token . '|' . implode(',', $ids) . '|forms-audit-v2';

        return 'pwe_faudit_' . md5($seed);
    }


    private function decode_audit_session_cache($packed) {
        if (!is_string($packed) || $packed === '') {
            return null;
        }

        if (strpos($packed, 'gz:') === 0) {
            $binary = base64_decode(substr($packed, 3), true);

            if ($binary === false || !function_exists('gzdecode')) {
                return null;
            }

            $json = gzdecode($binary);
        } elseif (strpos($packed, 'js:') === 0) {
            $json = substr($packed, 3);
        } else {
            return null;
        }

        if (!is_string($json) || $json === '') {
            return null;
        }

        $rows = json_decode($json, true);
        return is_array($rows) ? $rows : null;
    }


    private function encode_audit_session_cache($rows) {
        $json = wp_json_encode(array_values((array) $rows));

        if (!is_string($json) || $json === '') {
            return '';
        }

        if (function_exists('gzencode')) {
            $compressed = gzencode($json, 6);

            if (is_string($compressed) && $compressed !== '') {
                return 'gz:' . base64_encode($compressed);
            }
        }

        return 'js:' . $json;
    }


    public function clear_audit_session_cache() {
        $forms = class_exists('GFAPI') ? GFAPI::get_forms(true, false, 'title', 'ASC') : [];
        $ids = [];

        if (is_array($forms)) {
            foreach ($forms as $form) {
                $form_id = absint($form['id'] ?? 0);

                if ($form_id && !empty($this->get_pwe_feeds($form_id))) {
                    $ids[] = $form_id;
                }
            }
        }

        if (!empty($ids)) {
            delete_transient($this->get_audit_session_cache_key($ids));
        }

        $this->audit_session_rows = null;
        $this->audit_session_cache_key = '';
    }


    private function prime_audit_session_cache($active_form_ids, $force_refresh = false) {
        $active_form_ids = array_values(array_unique(array_filter(array_map('absint', (array) $active_form_ids))));
        sort($active_form_ids, SORT_NUMERIC);

        if (empty($active_form_ids)) {
            $this->audit_session_rows = [];
            $this->audit_session_cache_key = '';
            return [];
        }

        $cache_key = $this->get_audit_session_cache_key($active_form_ids);

        if ($force_refresh) {
            delete_transient($cache_key);
            $this->audit_session_rows = null;
            $this->audit_session_cache_key = '';
        }

        if (
            $this->audit_session_cache_key === $cache_key &&
            is_array($this->audit_session_rows)
        ) {
            return $this->audit_session_rows;
        }

        $cached = $this->decode_audit_session_cache(get_transient($cache_key));

        if (is_array($cached)) {
            $this->audit_session_cache_key = $cache_key;
            $this->audit_session_rows = $cached;
            return $cached;
        }

        $rows = $this->build_audit_session_rows($active_form_ids);
        $packed = $this->encode_audit_session_cache($rows);

        if ($packed !== '') {
            set_transient($cache_key, $packed, 12 * HOUR_IN_SECONDS);
        }

        $this->audit_session_cache_key = $cache_key;
        $this->audit_session_rows = $rows;

        return $rows;
    }


    private function build_audit_session_rows($active_form_ids) {
        global $wpdb;

        $active_form_ids = array_values(array_unique(array_filter(array_map('absint', (array) $active_form_ids))));

        if (empty($active_form_ids)) {
            return [];
        }

        [$entry_table, $meta_table] = $this->get_table_names();
        $placeholders = implode(',', array_fill(0, count($active_form_ids), '%d'));

        $sql = "SELECT e.id, e.form_id, e.date_created,
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
                       rsu.meta_value AS pwe_qr_resend_success
                FROM {$entry_table} e
                LEFT JOIN {$meta_table} qm
                  ON qm.entry_id = e.id
                 AND qm.meta_key = 'pwe_qr_code_url'
                LEFT JOIN {$meta_table} rqm
                  ON rqm.entry_id = e.id
                 AND rqm.meta_key = 'pwe_qr_resend_code_url'
                LEFT JOIN {$meta_table} rsu
                  ON rsu.entry_id = e.id
                 AND rsu.meta_key = 'pwe_qr_resend_success'
                WHERE e.status = 'active'
                  AND e.form_id IN ({$placeholders})
                ORDER BY e.id DESC";

        $query = $wpdb->prepare($sql, $active_form_ids);
        $rows = (array) $wpdb->get_results($query, ARRAY_A);
        $feeds_cache = [];
        $forms_cache = [];
        $prepared = [];

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
                $forms_cache[$form_id] = (!is_wp_error($form) && is_array($form)) ? $form : [];
            }

            $saved_qr_url = (string) ($row['pwe_qr_code_url'] ?? '');

            if ($saved_qr_url === '') {
                $saved_qr_url = (string) ($row['legacy_qr_code_url'] ?? '');
            }

            $saved_value = $this->extract_qr_value($saved_qr_url);

            if ($saved_value === '') {
                $saved_value = $this->get_legacy_derived_qr_value($entry_id, $feeds_cache[$form_id]);
            }

            $delivery_state = $this->get_notification_delivery_state($forms_cache[$form_id], $entry_id);
            $has_audit_resend = (
                (string) ($row['pwe_qr_resend_success'] ?? '') === '1' ||
                !empty($row['pwe_qr_resend_code_url'])
            );
            $has_resend = !empty($delivery_state['resend']) || $has_audit_resend;
            $comparison_value = $saved_value;

            if (
                (string) ($row['pwe_qr_resend_success'] ?? '') === '1' &&
                !empty($row['pwe_qr_resend_code_url'])
            ) {
                $resend_value = $this->extract_qr_value((string) $row['pwe_qr_resend_code_url']);

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
            $has_sent_notification = !empty($delivery_state['sent']);
            $has_notification_error = $this->has_active_notification_error($forms_cache[$form_id], $entry_id);
            $has_active_notifications = $this->form_has_active_notifications($forms_cache[$form_id]);

            // Keep the session cache intentionally compact. Full entry/meta data for the
            // current page is read only when those rows are actually rendered.
            $prepared[] = [
                'id'                       => $entry_id,
                'form_id'                  => $form_id,
                'date_created'              => (string) ($row['date_created'] ?? ''),
                'comparison'               => $comparison,
                'has_resend'               => $has_resend,
                'has_audit_resend'         => $has_audit_resend,
                'has_sent_notification'     => $has_sent_notification,
                'has_notification_error'    => $has_notification_error,
                'has_active_notifications'  => $has_active_notifications,
                'notification_status'       => $has_sent_notification
                    ? 'sent'
                    : ($has_notification_error ? 'error' : ($has_active_notifications ? 'missing' : 'none_configured')),
            ];
        }

        return $prepared;
    }


    private function get_search_entry_ids($search, $active_form_ids, $selected_form_id = 0) {
        global $wpdb;

        $search = trim((string) $search);

        if ($search === '') {
            return null;
        }

        [$entry_table, $meta_table] = $this->get_table_names();
        $where = ["e.status = 'active'"];
        $params = [];

        if ($selected_form_id) {
            $where[] = 'e.form_id = %d';
            $params[] = absint($selected_form_id);
        } else {
            $ids = array_values(array_unique(array_filter(array_map('absint', (array) $active_form_ids))));

            if (empty($ids)) {
                return [];
            }

            $placeholders = implode(',', array_fill(0, count($ids), '%d'));
            $where[] = "e.form_id IN ({$placeholders})";
            $params = array_merge($params, $ids);
        }

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

        $sql = 'SELECT e.id FROM ' . $entry_table . ' e WHERE ' . implode(' AND ', $where);
        $query = !empty($params) ? $wpdb->prepare($sql, $params) : $sql;

        return array_fill_keys(array_map('absint', (array) $wpdb->get_col($query)), true);
    }

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

        // During the audit request the full data set is prepared once and kept in a
        // per-login-session cache. Reuse those already calculated states here instead
        // of scanning the same entries again for every form row.
        if (is_array($this->audit_session_rows)) {
            foreach ($this->audit_session_rows as $row) {
                if (absint($row['form_id'] ?? 0) !== $form_id) {
                    continue;
                }

                $comparison = (string) ($row['comparison'] ?? 'none');
                $has_sent_notification = !empty($row['has_sent_notification']);
                $has_audit_resend = !empty($row['has_audit_resend']);
                $has_resend = !empty($row['has_resend']);
                $has_notification_error = !empty($row['has_notification_error']);
                $has_active_notifications = !empty($row['has_active_notifications']);

                if (!$has_sent_notification && !$has_audit_resend) {
                    if ($has_notification_error) {
                        $stats['notification_error']++;
                    } elseif (!$has_active_notifications) {
                        $stats['notification_none']++;
                    } else {
                        $stats['notification_missing']++;
                    }
                } elseif (isset($stats[$comparison])) {
                    $stats[$comparison]++;
                }

                if ($has_resend) {
                    $stats['resend']++;
                }
            }

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
            $delivery_state = $this->get_notification_delivery_state($form, $entry_id);
            $has_audit_resend = (
                $resend_success === '1' ||
                !empty($row['pwe_qr_resend_code_url'])
            );
            $has_resend = !empty($delivery_state['resend']) || $has_audit_resend;

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
            $has_sent_notification = !empty($delivery_state['sent']);
            $has_notification_error = $this->has_active_notification_error($form, $entry_id);

            // Resend is an independent flag. A resend sent by the dedicated Resend
            // module does not rewrite the original delivery state, while a resend made
            // from the audit tool is an explicit repair and may promote the base status
            // back to the QR comparison result.
            if (!$has_sent_notification && !$has_audit_resend) {
                if ($has_notification_error) {
                    $stats['notification_error']++;
                } elseif (!$has_active_notifications) {
                    $stats['notification_none']++;
                } else {
                    $stats['notification_missing']++;
                }
            } elseif (isset($stats[$comparison])) {
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
        if (empty($active_form_ids)) {
            return [
                'entries'   => [],
                'total'     => 0,
                'all_total' => 0,
                'counts'    => ['ok' => 0, 'bad' => 0, 'none' => 0, 'notification_none' => 0, 'notification_missing' => 0, 'notification_error' => 0, 'resend' => 0],
            ];
        }

        $rows = $this->prime_audit_session_cache($active_form_ids);
        $search_ids = $this->get_search_entry_ids($search, $active_form_ids, $selected_form_id);
        $counts = ['ok' => 0, 'bad' => 0, 'none' => 0, 'notification_none' => 0, 'notification_missing' => 0, 'notification_error' => 0, 'resend' => 0];
        $filtered_rows = [];

        foreach ($rows as $row) {
            $entry_id = absint($row['id'] ?? 0);
            $form_id = absint($row['form_id'] ?? 0);

            if (!$entry_id || !$form_id) {
                continue;
            }

            if ($selected_form_id && $form_id !== absint($selected_form_id)) {
                continue;
            }

            if (is_array($search_ids) && !isset($search_ids[$entry_id])) {
                continue;
            }

            $comparison = (string) ($row['comparison'] ?? 'none');
            $has_resend = !empty($row['has_resend']);
            $has_audit_resend = !empty($row['has_audit_resend']);
            $has_sent_notification = !empty($row['has_sent_notification']);
            $has_notification_error = !empty($row['has_notification_error']);
            $has_active_notifications = !empty($row['has_active_notifications']);
            $notification_status = (string) ($row['notification_status'] ?? 'none_configured');

            // Keep the row's original statuses intact. An audit resend is the only
            // operation that repairs the base comparison itself; a resend sent by the
            // dedicated Resend module remains an independent row badge.
            $effective_comparison = (!$has_sent_notification && !$has_audit_resend)
                ? 'unsent'
                : $comparison;

            if ($status_filter !== '' && $effective_comparison !== $status_filter) {
                continue;
            }

            if ($notification_filter !== '') {
                if ($notification_filter === 'resend') {
                    if (!$has_resend) {
                        continue;
                    }
                } elseif ($notification_status !== $notification_filter) {
                    continue;
                }
            }

            // The top summary is an unresolved-work counter. Once ANY resend exists,
            // do not keep that row in the top "Nie wysłane" or "Rozbieżne" number.
            // The table row itself still shows both original status + Resend.
            if ($effective_comparison === 'unsent') {
                if ($has_notification_error) {
                    $counts['notification_error']++;
                } elseif (!$has_active_notifications) {
                    $counts['notification_none']++;
                } elseif (!$has_resend) {
                    $counts['notification_missing']++;
                }
            } elseif ($comparison === 'bad' && $has_resend) {
                // Repaired by resend: keep the row badge, remove only from top counter.
            } elseif (isset($counts[$comparison])) {
                $counts[$comparison]++;
            }

            if ($has_resend) {
                $counts['resend']++;
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
