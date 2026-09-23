<?php

if (!defined('ABSPATH')) {
    exit;
}

trait PWE_System_Forms_Audit_Notifications_Trait {

    public function ajax_bulk_language_preview() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Brak uprawnień.'], 403);
        }

        check_ajax_referer('pwe_qr_bulk_language_preview', 'nonce');

        if (!class_exists('GFAPI')) {
            wp_send_json_error(['message' => 'Gravity Forms nie jest dostępne.'], 500);
        }

        $form_id = absint($_POST['form_id'] ?? 0);
        $status_filter = sanitize_key((string) ($_POST['status_filter'] ?? ''));
        $notification_filter = sanitize_key((string) ($_POST['notification_filter'] ?? ''));
        $search = sanitize_text_field((string) ($_POST['search'] ?? ''));

        if (!$form_id) {
            wp_send_json_error(['message' => 'Brak formularza.'], 400);
        }

        if (!in_array($status_filter, ['', 'ok', 'bad', 'none'], true)) {
            $status_filter = '';
        }

        if (!in_array($notification_filter, ['', 'none_configured', 'sent', 'missing', 'error', 'resend'], true)) {
            $notification_filter = '';
        }

        $form = GFAPI::get_form($form_id);

        if (!$form || is_wp_error($form)) {
            wp_send_json_error(['message' => 'Nie znaleziono formularza.'], 404);
        }

        $feeds = $this->get_pwe_feeds($form_id);
        $email_fields = $this->get_email_field_ids($form);
        $entries_to_send = [];
        $groups = [];

        $paging = [
            'offset'    => 0,
            'page_size' => 200,
        ];

        do {
            $entries = GFAPI::get_entries(
                $form_id,
                ['status' => 'active'],
                null,
                $paging
            );

            if (is_wp_error($entries)) {
                wp_send_json_error(['message' => $entries->get_error_message()], 500);
            }

            foreach ($entries as $entry) {
                $entry_id = absint($entry['id'] ?? 0);

                if (!$entry_id) {
                    continue;
                }

                $email = $this->get_entry_email($entry, $email_fields);

                if ($search !== '') {
                    $haystack = $entry_id . ' ' . $email . ' ' . implode(' ', array_map(
                        static function($value) {
                            return is_scalar($value) ? (string) $value : '';
                        },
                        $entry
                    ));

                    if (stripos($haystack, $search) === false) {
                        continue;
                    }
                }

                $saved_qr = $this->get_entry_saved_qr($entry_id, $feeds);
                $saved_value = (string) ($saved_qr['value'] ?? '');

                if ($saved_value === '') {
                    $saved_value = $this->extract_qr_value((string) ($saved_qr['url'] ?? ''));
                }

                if ($saved_value === '') {
                    $saved_value = $this->get_legacy_derived_qr_value($entry_id, $feeds);
                }

                $resend_success = (string) gform_get_meta($entry_id, 'pwe_qr_resend_success');
                $resend_url = (string) gform_get_meta($entry_id, 'pwe_qr_resend_code_url');

                $comparison_value = $saved_value;

                if ($resend_success === '1' && $resend_url !== '') {
                    $resend_value = $this->extract_qr_value($resend_url);

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

                $has_resend = (
                    $resend_success === '1' ||
                    $resend_url !== ''
                );
                $has_sent = $this->has_sent_notification($entry_id);
                $has_error = $this->has_notification_error($entry_id);
                $has_active_notifications = $this->form_has_active_notifications($form);

                $notification_status = $has_resend
                    ? 'resend'
                    : ($has_sent ? 'sent' : ($has_error ? 'error' : ($has_active_notifications ? 'missing' : 'none_configured')));

                $effective_comparison = (!$has_sent && !$has_resend)
                    ? 'unsent'
                    : $comparison;

                if ($status_filter !== '' && $effective_comparison !== $status_filter) {
                    continue;
                }

                if (
                    $notification_filter !== '' &&
                    $notification_status !== $notification_filter
                ) {
                    continue;
                }

                $notifications_for_resend = $this->get_resend_notifications_for_entry(
                    $form,
                    $entry,
                    $comparison
                );

                if (empty($notifications_for_resend)) {
                    continue;
                }

                $notification_ids = array_values(array_filter(array_map(
                    static function($notification) {
                        return (string) ($notification['id'] ?? '');
                    },
                    $notifications_for_resend
                )));

                $notification_names = array_values(array_filter(array_map(
                    static function($notification) {
                        return (string) ($notification['name'] ?? '');
                    },
                    $notifications_for_resend
                )));

                if (empty($notification_ids)) {
                    continue;
                }

                sort($notification_ids);
                $group_key = implode('|', $notification_ids);

                if (!isset($groups[$group_key])) {
                    $groups[$group_key] = [
                        'count'         => 0,
                        'notifications' => $notification_names,
                    ];
                }

                $groups[$group_key]['count']++;

                $entries_to_send[] = [
                    'entry_id'        => $entry_id,
                    'notification_ids' => $notification_ids,
                    'notifications'   => $notification_names,
                    'status'          => $notification_status,
                ];
            }

            $paging['offset'] += $paging['page_size'];
        } while (count($entries) === $paging['page_size']);

        wp_send_json_success([
            'entries' => $entries_to_send,
            'groups'  => array_values($groups),
        ]);
    }


    private function detect_language_from_source_url($source_url) {
        $source_url = trim((string) $source_url);

        if ($source_url === '') {
            return 'PL';
        }

        $path = (string) wp_parse_url($source_url, PHP_URL_PATH);
        $path = '/' . ltrim(strtolower($path), '/');

        if (preg_match('#^/(en|de|cs)(?:/|$)#i', $path, $match)) {
            return strtoupper($match[1]);
        }

        return 'PL';
    }


    private function get_active_notifications_by_language($form) {
        $result = [
            'PL' => [],
            'EN' => [],
            'DE' => [],
            'CS' => [],
        ];

        $notifications = $form['notifications'] ?? [];

        if (!is_array($notifications)) {
            return $result;
        }

        foreach ($notifications as $notification_id => $notification) {
            if (empty($notification['isActive'])) {
                continue;
            }

            $name = trim((string) ($notification['name'] ?? ''));

            if (!preg_match('/(?:-|–|—)\s*(PL|EN|DE|CS)\s*$/iu', $name, $match)) {
                continue;
            }

            $lang = strtoupper($match[1]);

            $result[$lang][] = [
                'id'   => (string) $notification_id,
                'name' => $name !== '' ? $name : ('Powiadomienie ' . $notification_id),
            ];
        }

        return $result;
    }


    public function ajax_resend_notifications() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Brak uprawnień.'], 403);
        }

        check_ajax_referer('pwe_qr_resend_notifications', 'nonce');

        if (!class_exists('GFAPI')) {
            wp_send_json_error(['message' => 'Gravity Forms nie jest dostępne.'], 500);
        }

        $items_raw = isset($_POST['items']) ? wp_unslash($_POST['items']) : '[]';
        $items = json_decode($items_raw, true);

        if (!is_array($items)) {
            wp_send_json_error(['message' => 'Nieprawidłowe dane.'], 400);
        }

        // One AJAX request deliberately handles only a small batch.
        $items = array_slice($items, 0, 10);

        $sent = 0;
        $failed = 0;
        $errors = [];

        foreach ($items as $item) {
            $entry_id = absint($item['entry_id'] ?? 0);
            $notification_ids = $item['notification_ids'] ?? [];
            $manual_selection = !empty($item['manual']);

            if (!is_array($notification_ids)) {
                $notification_ids = [];
            }

            $notification_ids = array_values(array_filter(array_map(
                static function($notification_id) {
                    return sanitize_text_field((string) $notification_id);
                },
                $notification_ids
            )));

            if (!$entry_id || empty($notification_ids)) {
                $failed++;
                continue;
            }

            $entry = GFAPI::get_entry($entry_id);

            if (is_wp_error($entry)) {
                $failed++;
                $errors[] = 'Entry ' . $entry_id . ': nie znaleziono wpisu.';
                continue;
            }

            $form_id = absint($entry['form_id'] ?? 0);
            $form = $form_id ? GFAPI::get_form($form_id) : null;

            if (!$form || is_wp_error($form)) {
                $failed++;
                $errors[] = 'Entry ' . $entry_id . ': nie znaleziono formularza.';
                continue;
            }

            $feeds = $this->get_pwe_feeds($form_id);
            $qr_url = (string) gform_get_meta($entry_id, 'pwe_qr_code_url');
            $saved_value = $this->extract_qr_value($qr_url);

            $email = $this->get_entry_email($entry, $this->get_email_field_ids($form));
            $match = $this->get_notification_for_entry($form, $entry, $email);
            $notifications = $form['notifications'] ?? [];

            if (!$manual_selection) {
                $expected_ids = !empty($match['ids']) && is_array($match['ids'])
                    ? array_map('strval', $match['ids'])
                    : [strval($match['id'] ?? '')];

                sort($expected_ids);
                $requested_ids = array_map('strval', $notification_ids);
                sort($requested_ids);

                if (
                    empty($expected_ids) ||
                    !empty($match['ambiguous']) ||
                    $expected_ids !== $requested_ids
                ) {
                    $failed++;
                    $errors[] = 'Entry ' . $entry_id . ': zestaw powiadomień nie jest już zgodny.';
                    continue;
                }
            }

            try {
                $original_qr_url = (string) gform_get_meta($entry_id, 'pwe_qr_code_url');
                $original_qr_url_encoded = (string) gform_get_meta($entry_id, 'pwe_qr_code_url_encoded');

                $legacy_original_meta = [];

                foreach ($feeds as $feed) {
                    if (($feed['_qr_system'] ?? '') !== 'qr-code') {
                        continue;
                    }

                    $feed_id = absint($feed['id'] ?? 0);

                    if (!$feed_id) {
                        continue;
                    }

                    $legacy_meta_key = 'qr-code_feed_' . $feed_id . '_url';
                    $legacy_original_meta[$legacy_meta_key] = (string) gform_get_meta(
                        $entry_id,
                        $legacy_meta_key
                    );
                }

                gform_delete_meta($entry_id, 'pwe_qr_resend_success');

                $sent_notification_names = [];
                $sent_recipients = [];

                foreach ($notification_ids as $notification_id) {
                    $notification = $notifications[$notification_id] ?? null;

                    if (!$notification || empty($notification['isActive'])) {
                        throw new RuntimeException(
                            'Powiadomienie ' . $notification_id . ' jest nieaktywne lub nie istnieje.'
                        );
                    }

                    $mail_result = [
                        'called'  => false,
                        'success' => false,
                        'to'      => '',
                        'subject' => '',
                    ];

                    $after_email_callback = function(
                        $is_success,
                        $to,
                        $subject,
                        $message,
                        $headers,
                        $attachments,
                        $message_format,
                        $from,
                        $from_name,
                        $bcc,
                        $reply_to,
                        $email_entry
                    ) use (&$mail_result, $entry_id) {
                        if (absint($email_entry['id'] ?? 0) !== $entry_id) {
                            return;
                        }

                        $mail_result['called'] = true;
                        $mail_result['success'] = (bool) $is_success;
                        $mail_result['to'] = is_array($to) ? implode(', ', $to) : (string) $to;
                        $mail_result['subject'] = (string) $subject;
                    };

                    add_action('gform_after_email', $after_email_callback, 999, 12);

                    try {
                        if (!class_exists('GFCommon') || !method_exists('GFCommon', 'send_notification')) {
                            throw new RuntimeException(
                                'Ta wersja Gravity Forms nie udostępnia GFCommon::send_notification().'
                            );
                        }

                        $notification_to_send = $notification;
                        $notification_to_send['isActive'] = true;

                        if ($manual_selection) {
                            $notification_to_send['conditionalLogic'] = null;
                        }

                        GFCommon::send_notification(
                            $notification_to_send,
                            $form,
                            $entry
                        );
                    } finally {
                        remove_action('gform_after_email', $after_email_callback, 999);
                    }

                    if (!$mail_result['called']) {
                        throw new RuntimeException(
                            'Gravity Forms nie uruchomił faktycznej wysyłki e-mail dla "' .
                            (string) ($notification['name'] ?? $notification_id) .
                            '".'
                        );
                    }

                    if (!$mail_result['success']) {
                        throw new RuntimeException(
                            'wp_mail() zwrócił błąd dla "' .
                            (string) ($notification['name'] ?? $notification_id) .
                            '".'
                        );
                    }

                    $sent_notification_names[] = (string) ($notification['name'] ?? $notification_id);

                    if ($mail_result['to'] !== '') {
                        $sent_recipients[] = $mail_result['to'];
                    }
                }

                $generated_qr_url = (string) gform_get_meta($entry_id, 'pwe_qr_code_url');
                $generated_qr_url_encoded = (string) gform_get_meta($entry_id, 'pwe_qr_code_url_encoded');

                if ($generated_qr_url !== '' && $generated_qr_url !== $original_qr_url) {
                    gform_update_meta($entry_id, 'pwe_qr_resend_code_url', $generated_qr_url);

                    if ($generated_qr_url_encoded !== '') {
                        gform_update_meta($entry_id, 'pwe_qr_resend_code_url_encoded', $generated_qr_url_encoded);
                    }
                } else {
                    // Legacy qr-code can regenerate its own feed-specific URL.
                    foreach ($legacy_original_meta as $legacy_meta_key => $legacy_original_url) {
                        $legacy_generated_url = (string) gform_get_meta(
                            $entry_id,
                            $legacy_meta_key
                        );

                        if (
                            $legacy_generated_url !== '' &&
                            $legacy_generated_url !== $legacy_original_url
                        ) {
                            gform_update_meta(
                                $entry_id,
                                'pwe_qr_resend_code_url',
                                $legacy_generated_url
                            );
                            break;
                        }
                    }
                }

                gform_update_meta($entry_id, 'pwe_qr_resend_success', '1');
                gform_update_meta($entry_id, 'pwe_qr_resend_sent_at', current_time('mysql'));
                gform_update_meta($entry_id, 'pwe_qr_resend_notification_ids', $notification_ids);
                gform_update_meta($entry_id, 'pwe_qr_resend_notification_names', $sent_notification_names);

                if ($original_qr_url !== '') {
                    gform_update_meta($entry_id, 'pwe_qr_code_url', $original_qr_url);
                } else {
                    gform_delete_meta($entry_id, 'pwe_qr_code_url');
                }

                if ($original_qr_url_encoded !== '') {
                    gform_update_meta($entry_id, 'pwe_qr_code_url_encoded', $original_qr_url_encoded);
                } else {
                    gform_delete_meta($entry_id, 'pwe_qr_code_url_encoded');
                }

                foreach ($legacy_original_meta as $legacy_meta_key => $legacy_original_url) {
                    if ($legacy_original_url !== '') {
                        gform_update_meta($entry_id, $legacy_meta_key, $legacy_original_url);
                    } else {
                        gform_delete_meta($entry_id, $legacy_meta_key);
                    }
                }

                if (method_exists('GFAPI', 'add_note')) {
                    $current_user = wp_get_current_user();

                    $note = 'PWE QR: ponownie wysłano powiadomienia: ' .
                        implode(', ', $sent_notification_names);

                    if (!empty($sent_recipients)) {
                        $note .= ' | odbiorcy: ' . implode(' ; ', array_unique($sent_recipients));
                    }

                    GFAPI::add_note(
                        $entry_id,
                        absint($current_user->ID ?? 0),
                        (string) ($current_user->display_name ?? 'PWE QR'),
                        $note
                    );
                }

                $sent++;
            } catch (Throwable $e) {
                $failed++;
                $errors[] = 'Entry ' . $entry_id . ': ' . $e->getMessage();
            }
        }

        wp_send_json_success([
            'sent'   => $sent,
            'failed' => $failed,
            'errors' => $errors,
        ]);
    }


    private function add_admin_notifications_to_unsent_match($form, $entry, $match) {
        $entry_id = absint($entry['id'] ?? 0);

        if (!$entry_id || $this->has_sent_notification($entry_id)) {
            return $match;
        }

        if (!empty($match['ambiguous']) || empty($match['id'])) {
            return $match;
        }

        $ids = !empty($match['ids']) && is_array($match['ids'])
            ? array_values(array_map('strval', $match['ids']))
            : [(string) $match['id']];

        $names = !empty($match['names']) && is_array($match['names'])
            ? array_values(array_map('strval', $match['names']))
            : [(string) ($match['name'] ?? '')];

        foreach (($form['notifications'] ?? []) as $notification_id => $notification) {
            if (empty($notification['isActive'])) {
                continue;
            }

            $name = trim((string) ($notification['name'] ?? ''));

            if (stripos($name, 'Admin Notification') === false) {
                continue;
            }

            if (!$this->notification_conditional_logic_passes($notification, $form, $entry)) {
                continue;
            }

            $notification_id = (string) $notification_id;

            if ($notification_id === '' || in_array($notification_id, $ids, true)) {
                continue;
            }

            $ids[] = $notification_id;
            $names[] = $name !== '' ? $name : ('Powiadomienie ' . $notification_id);
        }

        $names = array_values(array_filter($names));

        $match['ids'] = $ids;
        $match['names'] = $names;
        $match['id'] = $ids[0] ?? '';
        $match['name'] = implode(' + ', $names);

        return $match;
    }


    private function get_notification_for_entry($form, $entry, $entry_email) {
        $entry_id = absint($entry['id'] ?? 0);
        $notifications = $form['notifications'] ?? [];

        if (!$entry_id || empty($notifications) || !is_array($notifications)) {
            return [
                'id' => '',
                'name' => '',
                'source' => 'none',
                'ambiguous' => false,
                'candidates' => [],
            ];
        }

        // Historical sources can overlap. Merge our own history with Gravity Forms
        // successful notification notes instead of stopping at the first source.
        $historical_notifications = [];

        $history = $this->get_notification_history($entry_id);

        if (!empty($history)) {
            foreach ($history as $row) {
                $notification_id = (string) ($row['id'] ?? '');

                if ($notification_id === '' || !isset($notifications[$notification_id])) {
                    continue;
                }

                $notification = $notifications[$notification_id];

                if (empty($notification['isActive'])) {
                    continue;
                }

                $history_to = trim((string) ($row['to'] ?? ''));

                if (
                    $entry_email !== '' &&
                    $history_to !== '' &&
                    !$this->email_list_contains($history_to, $entry_email)
                ) {
                    continue;
                }

                $historical_notifications[$notification_id] = [
                    'id'   => $notification_id,
                    'name' => (string) ($notification['name'] ?? $row['name'] ?? ''),
                ];
            }
        }

        $gf_note_notifications = $this->get_notifications_from_gf_notes($form, $entry_id);

        foreach ($gf_note_notifications as $notification) {
            $notification_id = (string) ($notification['id'] ?? '');

            if ($notification_id === '') {
                continue;
            }

            $historical_notifications[$notification_id] = [
                'id'   => $notification_id,
                'name' => (string) ($notification['name'] ?? ''),
            ];
        }

        if (!empty($historical_notifications)) {
            $historical_notifications = array_values($historical_notifications);

            $ids = array_values(array_filter(array_map(
                static function($notification) {
                    return (string) ($notification['id'] ?? '');
                },
                $historical_notifications
            )));

            $names = array_values(array_filter(array_map(
                static function($notification) {
                    return (string) ($notification['name'] ?? '');
                },
                $historical_notifications
            )));

            return [
                'id'         => $ids[0] ?? '',
                'ids'        => $ids,
                'name'       => implode(', ', $names),
                'names'      => $names,
                'source'     => 'history',
                'ambiguous'  => false,
                'candidates' => $names,
            ];
        }

        // No historical send was found: determine the language from the entry source URL.
        $source_url = (string) ($entry['source_url'] ?? '');
        $source_lang = $this->detect_language_from_source_url($source_url);
        $language_matches = $this->get_notifications_for_language($form, $source_lang);

        if (!empty($language_matches)) {
            $ids = array_values(array_filter(array_map(
                static function($notification) {
                    return (string) ($notification['id'] ?? '');
                },
                $language_matches
            )));

            $names = array_values(array_filter(array_map(
                static function($notification) {
                    return (string) ($notification['name'] ?? '');
                },
                $language_matches
            )));

            return $this->add_admin_notifications_to_unsent_match(
                $form,
                $entry,
                [
                    'id'               => $ids[0] ?? '',
                    'ids'              => $ids,
                    'name'             => implode(' + ', $names),
                    'names'            => $names,
                    'source'           => 'source_url',
                    'lang'             => $source_lang,
                    'ambiguous'        => false,
                    'candidates'       => $names,
                    'multi_send'       => true,
                ]
            );
        }

        // Historical entries created before notification logging:
        // fall back to the older recipient/conditional-logic inference only when
        // no language-based notification could be resolved.
        $candidates = [];

        foreach ($notifications as $notification_id => $notification) {
            if (empty($notification['isActive'])) {
                continue;
            }

            if (!empty($notification['event']) && $notification['event'] !== 'form_submission') {
                continue;
            }

            if (!$this->notification_conditional_logic_passes($notification, $form, $entry)) {
                continue;
            }

            $recipients = $this->resolve_notification_recipients($notification, $form, $entry);

            if ($entry_email === '' || !$this->recipient_array_contains($recipients, $entry_email)) {
                continue;
            }

            $candidates[(string) $notification_id] = (string) ($notification['name'] ?? ('Powiadomienie ' . $notification_id));
        }

        if (count($candidates) === 1) {
            $id = (string) array_key_first($candidates);

            return $this->add_admin_notifications_to_unsent_match(
                $form,
                $entry,
                [
                    'id' => $id,
                    'ids' => [$id],
                    'name' => $candidates[$id],
                    'names' => [$candidates[$id]],
                    'source' => 'inferred',
                    'ambiguous' => false,
                    'candidates' => $candidates,
                ]
            );
        }

        return [
            'id' => '',
            'name' => '',
            'source' => empty($candidates) ? 'none' : 'inferred',
            'ambiguous' => count($candidates) > 1,
            'candidates' => $candidates,
        ];
    }


    private function get_notification_history($entry_id) {
        $raw = gform_get_meta($entry_id, 'pwe_qr_notification_history');

        if (is_array($raw)) {
            return $raw;
        }

        if (!is_string($raw) || $raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }


    private function get_notification_error_message($entry_id) {
        $entry_id = absint($entry_id);

        if (!$entry_id || !class_exists('GFAPI') || !method_exists('GFAPI', 'get_notes')) {
            return '';
        }

        $notes = GFAPI::get_notes([
            'entry_id'  => $entry_id,
            'note_type' => 'notification',
        ]);

        if (!is_array($notes) || empty($notes)) {
            return '';
        }

        foreach ($notes as $note) {
            $values = is_object($note) ? get_object_vars($note) : (array) $note;
            $sub_type = strtolower(trim((string) ($values['sub_type'] ?? '')));
            $note_text = '';

            foreach ($values as $key => $value) {
                if (!is_scalar($value)) {
                    continue;
                }

                if (in_array((string) $key, ['value', 'note', 'message'], true)) {
                    $note_text .= ' ' . (string) $value;
                }
            }

            if ($note_text === '') {
                foreach ($values as $value) {
                    if (is_scalar($value)) {
                        $note_text .= ' ' . (string) $value;
                    }
                }
            }

            $note_text = trim(wp_strip_all_tags($note_text));

            $looks_like_error = (
                in_array($sub_type, ['error', 'failed', 'failure'], true) ||
                stripos($note_text, 'nie był w stanie wysłać') !== false ||
                stripos($note_text, 'could not send') !== false ||
                stripos($note_text, 'smtp error') !== false ||
                stripos($note_text, 'recipient failed') !== false ||
                stripos($note_text, 'recipients failed') !== false ||
                stripos($note_text, 'could not authenticate') !== false
            );

            if (!$looks_like_error || $note_text === '') {
                continue;
            }

            // Prefer the useful SMTP/mail part instead of repeating the whole note wrapper.
            foreach ([
                'SMTP Error:',
                'smtp error:',
                'Could not authenticate',
                'could not authenticate',
                'The following recipients failed:',
                'recipient failed',
                'recipients failed',
            ] as $marker) {
                $pos = stripos($note_text, $marker);

                if ($pos !== false) {
                    return trim(substr($note_text, $pos));
                }
            }

            return $note_text;
        }

        return '';
    }


    private function has_notification_error($entry_id) {
        $entry_id = absint($entry_id);

        if (!$entry_id) {
            return false;
        }

        if (array_key_exists($entry_id, $this->notification_error_cache)) {
            return $this->notification_error_cache[$entry_id];
        }

        if (!class_exists('GFAPI') || !method_exists('GFAPI', 'get_notes')) {
            $this->notification_error_cache[$entry_id] = false;
            return false;
        }

        $error_notes = GFAPI::get_notes([
            'entry_id'  => $entry_id,
            'note_type' => 'notification',
            'sub_type'  => 'error',
        ]);

        if (is_array($error_notes) && !empty($error_notes)) {
            $this->notification_error_cache[$entry_id] = true;
            return true;
        }

        $notes = GFAPI::get_notes([
            'entry_id'  => $entry_id,
            'note_type' => 'notification',
        ]);

        if (is_array($notes)) {
            foreach ($notes as $note) {
                $values = is_object($note) ? get_object_vars($note) : (array) $note;
                $sub_type = strtolower(trim((string) ($values['sub_type'] ?? '')));

                if (in_array($sub_type, ['error', 'failed', 'failure'], true)) {
                    $this->notification_error_cache[$entry_id] = true;
                    return true;
                }

                $note_text = '';

                foreach ($values as $value) {
                    if (is_scalar($value)) {
                        $note_text .= ' ' . (string) $value;
                    }
                }

                if (
                    stripos($note_text, 'nie był w stanie wysłać') !== false ||
                    stripos($note_text, 'could not send') !== false ||
                    stripos($note_text, 'smtp error') !== false ||
                    stripos($note_text, 'recipient failed') !== false ||
                    stripos($note_text, 'recipients failed') !== false ||
                    stripos($note_text, 'could not authenticate') !== false
                ) {
                    $this->notification_error_cache[$entry_id] = true;
                    return true;
                }
            }
        }

        $this->notification_error_cache[$entry_id] = false;

        return false;
    }


    private function get_failed_notifications_from_gf_notes($form, $entry_id) {
        $entry_id = absint($entry_id);

        if (!$entry_id || !class_exists('GFAPI') || !method_exists('GFAPI', 'get_notes')) {
            return [];
        }

        $notes = GFAPI::get_notes([
            'entry_id'  => $entry_id,
            'note_type' => 'notification',
        ]);

        if (!is_array($notes) || empty($notes)) {
            return [];
        }

        $notifications = $form['notifications'] ?? [];

        if (!is_array($notifications) || empty($notifications)) {
            return [];
        }

        $failed_note_texts = [];

        foreach ($notes as $note) {
            $values = is_object($note) ? get_object_vars($note) : (array) $note;
            $sub_type = strtolower(trim((string) ($values['sub_type'] ?? '')));
            $note_text = '';

            foreach ($values as $value) {
                if (is_scalar($value)) {
                    $note_text .= ' ' . (string) $value;
                }
            }

            $looks_failed = (
                in_array($sub_type, ['error', 'failed', 'failure'], true) ||
                stripos($note_text, 'nie był w stanie wysłać') !== false ||
                stripos($note_text, 'could not send') !== false ||
                stripos($note_text, 'smtp error') !== false ||
                stripos($note_text, 'recipient failed') !== false ||
                stripos($note_text, 'recipients failed') !== false ||
                stripos($note_text, 'could not authenticate') !== false
            );

            if ($looks_failed) {
                $failed_note_texts[] = $note_text;
            }
        }

        if (empty($failed_note_texts)) {
            return [];
        }

        $failed_text = implode(' ', $failed_note_texts);
        $matches = [];

        foreach ($notifications as $notification_id => $notification) {
            $notification_id = (string) $notification_id;
            $name = trim((string) ($notification['name'] ?? ''));

            if (empty($notification['isActive'])) {
                continue;
            }

            $matches_id = (
                $notification_id !== '' &&
                stripos($failed_text, $notification_id) !== false
            );

            $matches_name = (
                $name !== '' &&
                stripos($failed_text, $name) !== false
            );

            if (!$matches_id && !$matches_name) {
                continue;
            }

            $matches[$notification_id] = [
                'id'   => $notification_id,
                'name' => $name !== '' ? $name : ('Powiadomienie ' . $notification_id),
            ];
        }

        return array_values($matches);
    }


    private function get_notifications_from_gf_notes($form, $entry_id) {
        $entry_id = absint($entry_id);

        if (!$entry_id || !class_exists('GFAPI') || !method_exists('GFAPI', 'get_notes')) {
            return [];
        }

        $notes = GFAPI::get_notes([
            'entry_id'  => $entry_id,
            'note_type' => 'notification',
            'sub_type'  => 'success',
        ]);

        if (!is_array($notes) || empty($notes)) {
            return [];
        }

        $notifications = $form['notifications'] ?? [];

        if (!is_array($notifications) || empty($notifications)) {
            return [];
        }

        $note_text = '';

        foreach ($notes as $note) {
            $values = is_object($note) ? get_object_vars($note) : (array) $note;

            foreach ($values as $value) {
                if (is_scalar($value)) {
                    $note_text .= ' ' . (string) $value;
                }
            }
        }

        $matches = [];

        foreach ($notifications as $notification_id => $notification) {
            $notification_id = (string) $notification_id;
            $name = trim((string) ($notification['name'] ?? ''));

            $matches_id = (
                $notification_id !== '' &&
                stripos($note_text, $notification_id) !== false
            );

            $matches_name = (
                $name !== '' &&
                stripos($note_text, $name) !== false
            );

            if (!$matches_id && !$matches_name) {
                continue;
            }

            $matches[] = [
                'id'   => $notification_id,
                'name' => $name !== '' ? $name : ('Powiadomienie ' . $notification_id),
            ];
        }

        return $matches;
    }


    private function has_sent_notification($entry_id) {
        $entry_id = absint($entry_id);

        if (!$entry_id) {
            return false;
        }

        if (array_key_exists($entry_id, $this->notification_sent_cache)) {
            return $this->notification_sent_cache[$entry_id];
        }

        // pwe_qr_notification_history is written before wp_mail(), so it proves only
        // that Gravity Forms attempted a notification. It is not proof of delivery.
        $resend_success = (string) gform_get_meta($entry_id, 'pwe_qr_resend_success');

        if ($resend_success === '1') {
            $this->notification_sent_cache[$entry_id] = true;
            return true;
        }

        if (class_exists('GFAPI') && method_exists('GFAPI', 'get_notes')) {
            $success_notes = GFAPI::get_notes([
                'entry_id'  => $entry_id,
                'note_type' => 'notification',
                'sub_type'  => 'success',
            ]);

            if (is_array($success_notes) && !empty($success_notes)) {
                $this->notification_sent_cache[$entry_id] = true;
                return true;
            }
        }

        $this->notification_sent_cache[$entry_id] = false;

        return false;
    }


    private function get_notifications_for_language($form, $lang) {
        $lang = strtoupper(trim((string) $lang));
        $notifications = $form['notifications'] ?? [];
        $matches = [];

        if (!is_array($notifications) || $lang === '') {
            return [];
        }

        foreach ($notifications as $notification_id => $notification) {
            if (empty($notification['isActive'])) {
                continue;
            }

            $name = trim((string) ($notification['name'] ?? ''));

            if (!preg_match('/(?:-|–|—)\s*' . preg_quote($lang, '/') . '\s*$/iu', $name)) {
                continue;
            }

            $matches[(string) $notification_id] = [
                'id'   => (string) $notification_id,
                'name' => $name !== '' ? $name : ('Powiadomienie ' . $notification_id),
            ];
        }

        return array_values($matches);
    }


    private function notification_contains_qr($notification) {
        if (!is_array($notification)) {
            return false;
        }

        if (!empty($notification['pwe_attach_qr_image'])) {
            return true;
        }

        $message = (string) ($notification['message'] ?? '');

        if ($message === '') {
            return false;
        }

        return (bool) preg_match(
            '/(?:pwe_qr_url_encoded|pwe_qr_url|pwe_qr_img|qr-code|qrcode|qr[_ -]?code)/i',
            $message
        );
    }


    private function get_historical_notifications_for_entry($form, $entry) {
        $entry_id = absint($entry['id'] ?? 0);
        $notifications = $form['notifications'] ?? [];
        $historical = [];

        if (!$entry_id || !is_array($notifications)) {
            return [];
        }

        $history = $this->get_notification_history($entry_id);

        foreach ($history as $row) {
            $notification_id = (string) ($row['id'] ?? '');

            if ($notification_id === '' || !isset($notifications[$notification_id])) {
                continue;
            }

            $historical[$notification_id] = [
                'id'   => $notification_id,
                'name' => (string) ($notifications[$notification_id]['name'] ?? $row['name'] ?? ''),
            ];
        }

        foreach ($this->get_notifications_from_gf_notes($form, $entry_id) as $notification) {
            $notification_id = (string) ($notification['id'] ?? '');

            if ($notification_id === '' || !isset($notifications[$notification_id])) {
                continue;
            }

            $historical[$notification_id] = [
                'id'   => $notification_id,
                'name' => (string) ($notification['name'] ?? $notifications[$notification_id]['name'] ?? ''),
            ];
        }

        return array_values($historical);
    }


    private function get_qr_resend_notifications_for_entry($form, $entry) {
        $notifications = $form['notifications'] ?? [];
        $result = [];

        if (!is_array($notifications)) {
            return [];
        }

        foreach ($this->get_historical_notifications_for_entry($form, $entry) as $historical) {
            $notification_id = (string) ($historical['id'] ?? '');

            if ($notification_id === '' || !isset($notifications[$notification_id])) {
                continue;
            }

            $notification = $notifications[$notification_id];

            // For a QR mismatch resend ONLY the notification that actually carried
            // the QR code is eligible. Admin/other notifications are not resent
            // merely because they share the same language.
            if (!$this->notification_contains_qr($notification)) {
                continue;
            }

            $result[$notification_id] = [
                'id'   => $notification_id,
                'name' => (string) ($notification['name'] ?? $historical['name'] ?? ''),
            ];
        }

        return array_values($result);
    }


    private function get_never_sent_notifications_for_entry($form, $entry) {
        $entry_id = absint($entry['id'] ?? 0);

        if (!$entry_id) {
            return [];
        }

        $result = [];

        // First use exactly the same user-facing notification resolution as the audit table.
        // If the audit already knows that this entry should use e.g. "Platyna" or
        // "Dziękujemy za rejestrację na Targi", use that exact notification.
        $email = $this->get_entry_email(
            $entry,
            $this->get_email_field_ids($form)
        );

        $match = $this->get_notification_for_entry($form, $entry, $email);

        if (
            empty($match['ambiguous']) &&
            (
                !empty($match['id']) ||
                !empty($match['ids'])
            )
        ) {
            $ids = !empty($match['ids']) && is_array($match['ids'])
                ? $match['ids']
                : [(string) ($match['id'] ?? '')];

            foreach ($ids as $notification_id) {
                $notification_id = (string) $notification_id;

                if ($notification_id === '') {
                    continue;
                }

                $notification = $form['notifications'][$notification_id] ?? null;

                if (!$notification || empty($notification['isActive'])) {
                    continue;
                }

                // This notification was already resolved unambiguously by the audit
                // for this exact entry. Do not reject it by running conditional logic
                // a second time here, because older entries can no longer reproduce
                // the original submission context perfectly.
                $result[$notification_id] = [
                    'id'   => $notification_id,
                    'name' => (string) ($notification['name'] ?? ('Powiadomienie ' . $notification_id)),
                ];
            }
        }

        // For entries where NOTHING was ever sent, also include matching active
        // Admin Notification(s). This is intentionally limited to admin notifications;
        // we still do not resend every active notification in the form.
        foreach (($form['notifications'] ?? []) as $notification_id => $notification) {
            if (empty($notification['isActive'])) {
                continue;
            }

            $name = (string) ($notification['name'] ?? '');

            if (stripos($name, 'Admin Notification') === false) {
                continue;
            }

            if (!$this->notification_conditional_logic_passes($notification, $form, $entry)) {
                continue;
            }

            $notification_id = (string) $notification_id;

            if ($notification_id === '') {
                continue;
            }

            $result[$notification_id] = [
                'id'   => $notification_id,
                'name' => $name !== '' ? $name : ('Powiadomienie ' . $notification_id),
            ];
        }

        if (!empty($result)) {
            return array_values($result);
        }

        // Final fallback for newer multilingual forms where no notification could
        // be resolved by recipient/current configuration.
        $source_url = (string) ($entry['source_url'] ?? '');
        $lang = $this->detect_language_from_source_url($source_url);
        $notifications = $this->get_notifications_for_language($form, $lang);

        foreach ($notifications as $notification) {
            $notification_id = (string) ($notification['id'] ?? '');
            $name = (string) ($notification['name'] ?? '');

            if ($notification_id === '') {
                continue;
            }

            $form_notification = $form['notifications'][$notification_id] ?? null;

            if (!$form_notification || empty($form_notification['isActive'])) {
                continue;
            }

            if (!$this->notification_conditional_logic_passes($form_notification, $form, $entry)) {
                continue;
            }

            $is_admin = stripos($name, 'Admin Notification') !== false;
            $is_registration = stripos($name, 'Registration') !== false;

            if (!$is_admin && !$is_registration) {
                continue;
            }

            $result[$notification_id] = [
                'id'   => $notification_id,
                'name' => $name,
            ];
        }

        return array_values($result);
    }


    private function get_resend_notifications_for_entry($form, $entry, $comparison = '') {
        $entry_id = absint($entry['id'] ?? 0);

        if (!$entry_id) {
            return [];
        }

        // If the original send failed, use the EXACT notification(s) that Gravity Forms
        // recorded as failed for this entry. This is especially important for legacy
        // qr-code forms whose notification names do not follow the new naming convention.
        $failed_notifications = $this->get_failed_notifications_from_gf_notes($form, $entry_id);

        if (!empty($failed_notifications) && !$this->has_sent_notification($entry_id)) {
            return $failed_notifications;
        }

        $has_sent = $this->has_sent_notification($entry_id);

        if (!$has_sent) {
            return $this->get_never_sent_notifications_for_entry($form, $entry);
        }

        if ($comparison === 'bad') {
            return $this->get_qr_resend_notifications_for_entry($form, $entry);
        }

        return [];
    }


    private function form_has_active_notifications($form) {
        $notifications = $form['notifications'] ?? [];

        if (!is_array($notifications) || empty($notifications)) {
            return false;
        }

        foreach ($notifications as $notification) {
            if (!empty($notification['isActive'])) {
                return true;
            }
        }

        return false;
    }


    private function get_available_notifications($form) {
        $available = [];
        $notifications = $form['notifications'] ?? [];

        if (!is_array($notifications)) {
            return $available;
        }

        foreach ($notifications as $notification_id => $notification) {
            if (empty($notification['isActive'])) {
                continue;
            }

            $available[(string) $notification_id] = [
                'id'   => (string) $notification_id,
                'name' => (string) ($notification['name'] ?? ('Powiadomienie ' . $notification_id)),
            ];
        }

        return $available;
    }


    private function notification_conditional_logic_passes($notification, $form, $entry) {
        $logic = $notification['conditionalLogic'] ?? null;

        if (empty($logic) || empty($logic['rules']) || !is_array($logic['rules'])) {
            return true;
        }

        if (class_exists('GFCommon') && method_exists('GFCommon', 'evaluate_conditional_logic')) {
            return (bool) GFCommon::evaluate_conditional_logic($logic, $form, $entry);
        }

        $results = [];

        foreach ($logic['rules'] as $rule) {
            $field_id = (string) ($rule['fieldId'] ?? '');
            $actual = (string) ($entry[$field_id] ?? '');
            $expected = (string) ($rule['value'] ?? '');
            $operator = (string) ($rule['operator'] ?? 'is');

            $results[] = $this->compare_rule_value($actual, $operator, $expected);
        }

        $matched = (($logic['logicType'] ?? 'all') === 'any')
            ? in_array(true, $results, true)
            : !in_array(false, $results, true);

        return (($logic['actionType'] ?? 'show') === 'hide') ? !$matched : $matched;
    }


    private function resolve_notification_recipients($notification, $form, $entry) {
        $type = (string) ($notification['toType'] ?? 'email');
        $recipients = [];

        if ($type === 'field') {
            $field_id = (string) ($notification['to'] ?? '');
            $value = trim((string) ($entry[$field_id] ?? ''));

            if ($value !== '') {
                $recipients[] = $value;
            }

            return $recipients;
        }

        if ($type === 'routing') {
            foreach (($notification['routing'] ?? []) as $rule) {
                $field_id = (string) ($rule['fieldId'] ?? '');
                $actual = (string) ($entry[$field_id] ?? '');
                $expected = (string) ($rule['value'] ?? '');
                $operator = (string) ($rule['operator'] ?? 'is');

                if (!$this->compare_rule_value($actual, $operator, $expected)) {
                    continue;
                }

                $email = $this->replace_notification_variables((string) ($rule['email'] ?? ''), $form, $entry);

                if ($email !== '') {
                    $recipients[] = $email;
                }
            }

            return $recipients;
        }

        $to = $this->replace_notification_variables((string) ($notification['to'] ?? ''), $form, $entry);

        if ($to !== '') {
            $recipients[] = $to;
        }

        return $recipients;
    }


    private function replace_notification_variables($value, $form, $entry) {
        if ($value === '') {
            return '';
        }

        if (class_exists('GFCommon') && method_exists('GFCommon', 'replace_variables')) {
            return (string) GFCommon::replace_variables($value, $form, $entry, false, false, false, 'text', []);
        }

        return $value;
    }


    private function compare_rule_value($actual, $operator, $expected) {
        switch ($operator) {
            case 'isnot':
                return $actual !== $expected;
            case '>':
                return (float) $actual > (float) $expected;
            case '<':
                return (float) $actual < (float) $expected;
            case 'contains':
                return strpos($actual, $expected) !== false;
            case 'starts_with':
                return strncmp($actual, $expected, strlen($expected)) === 0;
            case 'ends_with':
                return $expected === '' || substr($actual, -strlen($expected)) === $expected;
            case 'is':
            default:
                return $actual === $expected;
        }
    }


    private function recipient_array_contains($recipients, $target_email) {
        foreach ($recipients as $recipient_string) {
            if ($this->email_list_contains((string) $recipient_string, $target_email)) {
                return true;
            }
        }

        return false;
    }


    private function email_list_contains($emails, $target_email) {
        $target_email = strtolower(trim($target_email));

        if ($target_email === '') {
            return false;
        }

        $parts = preg_split('/[,;]+/', (string) $emails);

        foreach ($parts as $part) {
            $part = trim($part);

            if (preg_match('/<([^>]+)>/', $part, $match)) {
                $part = $match[1];
            }

            if (strtolower(trim($part)) === $target_email) {
                return true;
            }
        }

        return false;
    }
}
