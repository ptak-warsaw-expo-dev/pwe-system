<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

final class PWE_System_Resend_Matcher
{
    public static function get_entry_lang(array $form, array $entry): string
    {
        $value = PWE_System_Resend_Field_Locator::value($form, $entry, ['lang']);

        return sanitize_key(strtolower($value));
    }

    public static function get_entry_location(array $form, array $entry): string
    {
        return mb_strtolower(PWE_System_Resend_Field_Locator::value($form, $entry, ['location']));
    }

    public static function entry_has_platyna(array $form, array $entry): bool
    {
        return strpos(self::get_entry_location($form, $entry), 'platyna') !== false;
    }

    public static function notification_is_platyna(array $notification): bool
    {
        return strpos(mb_strtolower((string) ($notification['name'] ?? '')), 'platyna') !== false;
    }

    public static function extract_lang_from_notification_name(string $name): string
    {
        return PWE_System_Resend_Notification_Name::strict_language($name);
    }

    
    public static function is_entry_matching_notification(
        array $form,
        array $entry,
        array $notification
    ): bool {
        $notification_name = (string) ($notification['name'] ?? '');

        $notification_lang = self::extract_lang_from_notification_name(
            $notification_name
        );

        $entry_lang = self::get_entry_lang($form, $entry);

        if ($notification_lang !== '') {
            $available_languages = [];

            $base_name = mb_strtolower(
                PWE_System_Resend_Notification_Name::base_title(
                    $notification_name,
                    $notification_lang
                )
            );

            foreach (
                PWE_System_Resend_Gravity::get_resend_notifications($form)
                as $candidate
            ) {
                if (!is_array($candidate)) {
                    continue;
                }

                $candidate_name = (string) ($candidate['name'] ?? '');

                $candidate_lang = self::extract_lang_from_notification_name(
                    $candidate_name
                );

                if ($candidate_lang === '') {
                    continue;
                }

                $candidate_base = mb_strtolower(
                    PWE_System_Resend_Notification_Name::base_title(
                        $candidate_name,
                        $candidate_lang
                    )
                );

                if ($candidate_base === $base_name) {
                    $available_languages[] = $candidate_lang;
                }
            }

            $available_languages = array_unique($available_languages);

            // Jeśli język wpisu nie jest dostępny,
            // użyj EN, o ile powiadomienie EN istnieje.
            if (!in_array($entry_lang, $available_languages, true)) {
                $entry_lang = in_array('en', $available_languages, true)
                    ? 'en'
                    : '';
            }

            if ($entry_lang === '' || $notification_lang !== $entry_lang) {
                return false;
            }
        }

        $entry_is_platyna = self::entry_has_platyna($form, $entry);
        $notification_is_platyna = self::notification_is_platyna($notification);

        return $notification_is_platyna
            ? $entry_is_platyna
            : !$entry_is_platyna;
    }

    public static function notification_should_send(
        array $notification,
        array $form,
        array $entry
    ): bool {
        if (!self::is_entry_matching_notification($form, $entry, $notification)) {
            return false;
        }

        $logic = PWE_System_Resend_Conditional_Logic::compile(
            $notification['conditionalLogic'] ?? null,
            'notification',
            $notification
        );

        return PWE_System_Resend_Conditional_Logic::evaluate($logic, $form, $entry);
    }

    public static function entry_has_valid_email(array $form, array $entry): bool
    {
        $email = PWE_System_Resend_Field_Locator::value(
            $form,
            $entry,
            ['email', 'pwe_email'],
            ['email']
        );

        return $email !== '' && is_email($email);
    }

    public static function sent_meta_key(array $notification): string
    {
        $key = (string) ($notification['id'] ?? $notification['name'] ?? '');

        return '_pwe_system_resend_sent_' . md5($key);
    }

    public static function skipped_meta_key(array $notification): string
    {
        return self::sent_meta_key($notification) . '_skipped';
    }

    public static function is_already_processed(array $entry, array $notification): bool
    {
        $entry_id = (int) ($entry['id'] ?? 0);

        if ($entry_id < 1) {
            return true;
        }

        return (bool) gform_get_meta($entry_id, self::sent_meta_key($notification))
            || (bool) gform_get_meta($entry_id, self::skipped_meta_key($notification));
    }
}

