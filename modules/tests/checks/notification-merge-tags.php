<?php
if (!defined('ABSPATH')) { exit; }

/** Read-only validation of field merge tags in saved Multilang form notifications. */
final class PWE_System_Tests_Notification_Merge_Tags
{
    /**
     * Gravity Forms field merge tags end in a numeric field ID, optionally followed
     * by a multi-input suffix (for example {Name (First):5.3}). System tags such
     * as {form_title} and {all_fields} do not match this pattern.
     */
    private const FIELD_TAG_PATTERN = '/\{[^{}:]+:(\d+(?:\.\d+)?)\}/';

    public static function run(): array
    {
        if (!class_exists('GFAPI')) {
            return ['status' => 'error', 'message' => 'Gravity Forms jest niedostępne. Nie sprawdzono powiadomień.'];
        }
        $forms = GFAPI::get_forms(null, false);
        if (is_wp_error($forms) || !is_array($forms)) {
            return ['status' => 'error', 'message' => 'Nie udało się odczytać formularzy.'];
        }

        $checked = []; $errors = []; $formCount = 0; $notificationCount = 0;
        foreach ($forms as $summary) {
            $id = (int) ($summary['id'] ?? 0);
            $form = GFAPI::get_form($id);
            if (is_wp_error($form) || !is_array($form)) {
                $errors[] = "Formularz #$id: nie udało się odczytać danych.";
                continue;
            }
            if (empty($form['pwe_multilang_managed']) || !empty($form['is_trash'])) { continue; }
            $formCount++;
            $formTitle = '#' . $id . ' · ' . (string) ($form['title'] ?? 'Bez nazwy');
            $fieldIds = [];
            foreach (($form['fields'] ?? []) as $field) {
                $fieldId = is_array($field) ? ($field['id'] ?? null) : (is_object($field) ? ($field->id ?? null) : null);
                if (is_numeric($fieldId)) { $fieldIds[(string) (float) $fieldId] = true; }
            }
            $notifications = is_array($form['notifications'] ?? null) ? $form['notifications'] : [];
            foreach ($notifications as $notification) {
                if (!is_array($notification)) { continue; }
                $notificationCount++;
                $name = (string) ($notification['name'] ?? $notification['id'] ?? 'Bez nazwy');
                $rowTitle = $formTitle . ' / ' . $name;
                $tags = [];
                self::collectTags($notification, $tags);
                $invalid = [];
                foreach ($tags as $tag) {
                    if (!isset($fieldIds[self::normaliseFieldId($tag['id'])])) { $invalid[] = $tag['tag']; }
                }
                $invalid = array_values(array_unique($invalid));
                if ($invalid) { $errors[] = $rowTitle . ': nieistniejące pola: ' . implode(', ', $invalid); }
                $checked[] = [
                    'id' => '#' . $id,
                    'title' => $rowTitle,
                    'status' => $invalid ? 'error' : 'success',
                    'expected' => 'Każdy merge tag pola wskazuje istniejące pole formularza',
                    'actual' => $invalid ?: 'Wszystkie pola istnieją',
                    'message' => $invalid
                        ? 'Powiadomienie zawiera merge tagi z nieistniejącym ID pola: ' . implode(', ', $invalid)
                        : 'Wszystkie merge tagi pól wskazują istniejące pola. Tagi systemowe pominięto.',
                ];
            }
        }

        return [
            'status' => $errors ? 'error' : ($formCount ? 'success' : 'skipped'),
            'message' => $formCount ? "Sprawdzono formularze Multilang: $formCount" : 'Brak formularzy Multilang poza koszem.',
            'details' => [
                'summary' => ['forms' => $formCount, 'notifications' => $notificationCount],
                'checked' => $checked,
                'errors' => $errors,
                'warnings' => [],
            ],
            'suggestions' => $errors ? ['Popraw lub usuń wskazane merge tagi w powiadomieniach. Test tylko odczytuje formularze i niczego nie zmienia.'] : [],
            'view' => [
                'icon' => 'mail',
                'columnLabel' => 'Formularz / powiadomienie',
                'metrics' => [
                    ['label' => 'Powiadomienia', 'value' => $notificationCount, 'icon' => 'mail'],
                    ['label' => 'Problemy', 'value' => count($errors), 'icon' => 'warning', 'tone' => 'error'],
                ],
            ],
        ];
    }

    private static function collectTags($value, array &$tags): void
    {
        if (is_array($value)) {
            foreach ($value as $item) { self::collectTags($item, $tags); }
            return;
        }
        if (!is_string($value) || !preg_match_all(self::FIELD_TAG_PATTERN, $value, $matches, PREG_SET_ORDER)) { return; }
        foreach ($matches as $match) { $tags[] = ['tag' => $match[0], 'id' => $match[1]]; }
    }

    private static function normaliseFieldId(string $id): string
    {
        // Sub-input IDs (e.g. 5.3) belong to the parent field with ID 5.
        return (string) (float) explode('.', $id, 2)[0];
    }
}
