<?php
if (!defined('ABSPATH')) { exit; }

/** Read-only scan of saved Gravity Forms notifications for leftover {{...}} translation placeholders. */
final class PWE_System_Tests_Notification_Placeholders
{
    private const PATTERN = '/\{\{[^{}]+\}\}/';

    public static function run(): array
    {
        if (!class_exists('GFAPI')) {
            return ['status' => 'error', 'message' => 'Gravity Forms jest niedostępne. Nie sprawdzono powiadomień.'];
        }
        // null includes active and inactive forms; false excludes trash.
        $forms = GFAPI::get_forms(null, false);
        if (is_wp_error($forms) || !is_array($forms)) {
            return ['status' => 'error', 'message' => 'Nie udało się odczytać formularzy.'];
        }
        $checked = []; $errors = []; $warnings = []; $formCount = 0; $notificationCount = 0;
        foreach ($forms as $summary) {
            $id = (int) ($summary['id'] ?? 0);
            $form = GFAPI::get_form($id);
            if (is_wp_error($form) || !is_array($form)) {
                $errors[] = "Formularz #$id: nie udało się odczytać danych.";
                continue;
            }
            if (empty($form['pwe_multilang_managed']) || !empty($form['is_trash'])) { continue; }
            $formCount++;
            $title = '#' . $id . ' · ' . ($form['title'] ?? 'Bez nazwy');
            $notifications = is_array($form['notifications'] ?? null) ? $form['notifications'] : [];
            if (!$notifications) {
                $checked[] = ['id' => '#' . $id, 'title' => $title, 'status' => 'skipped', 'message' => 'Formularz nie ma zapisanych powiadomień.'];
                continue;
            }
            foreach ($notifications as $notification) {
                if (!is_array($notification)) { continue; }
                $notificationCount++;
                $name = (string) ($notification['name'] ?? $notification['id'] ?? 'Bez nazwy');
                $rowTitle = $title . ' / ' . $name;
                $found = [];
                self::scan($notification, $found);
                if ($found) {
                    $parts = [];
                    foreach ($found as $field => $tokens) { $parts[] = "$field: " . implode(', ', $tokens); }
                    $errors[] = "$rowTitle: " . implode(' | ', $parts);
                }
                $checked[] = [
                    'id' => '#' . $id,
                    'title' => $rowTitle,
                    'status' => $found ? 'error' : 'success',
                    'expected' => 'Brak nieprzetłumaczonych placeholderów {{...}}',
                    'actual' => $found ?: 'Brak',
                    'message' => $found
                        ? 'Znaleziono nieprzetłumaczone placeholdery: ' . implode('; ', array_map(static fn($tokens) => implode(', ', $tokens), $found))
                        : 'Treść powiadomienia nie zawiera pozostałości szablonu tłumaczeń.',
                ];
            }
        }
        return [
            'status' => $errors ? 'error' : ($warnings ? 'warning' : ($formCount ? 'success' : 'skipped')),
            'message' => $formCount ? "Sprawdzono formularze Multilang: $formCount" : 'Brak formularzy Multilang poza koszem.',
            'details' => ['summary' => ['forms' => $formCount, 'notifications' => $notificationCount], 'checked' => $checked, 'errors' => $errors, 'warnings' => $warnings],
            'suggestions' => $errors ? ['Uzupełnij brakujący klucz w translations.php dla wskazanego powiadomienia i zapisz formularz ponownie, aby placeholder został podmieniony. Test tylko odczytuje formularze — niczego nie zmienia.'] : [],
            'view' => [
                'icon' => 'mail',
                'columnLabel' => 'Formularz / powiadomienie',
                'metrics' => [
                    ['label' => 'Powiadomienia', 'value' => $notificationCount, 'icon' => 'mail'],
                    ['label' => 'Problemy', 'value' => count($errors), 'icon' => 'warning', 'tone' => 'error'],
                    ['label' => 'Ostrzeżenia', 'value' => count($warnings), 'icon' => 'info', 'tone' => 'warning'],
                ],
            ],
        ];
    }

    /** Recursively collects {{...}} matches from every string in a notification, keyed by its top-level field name. */
    private static function scan($value, array &$found, string $field = ''): void
    {
        if (is_array($value)) {
            foreach ($value as $key => $item) {
                self::scan($item, $found, $field !== '' ? $field : (string) $key);
            }
            return;
        }
        if (!is_string($value) || $value === '' || !preg_match_all(self::PATTERN, $value, $matches) || !$matches[0]) {
            return;
        }
        $found[$field] = array_values(array_unique(array_merge($found[$field] ?? [], $matches[0])));
    }
}
