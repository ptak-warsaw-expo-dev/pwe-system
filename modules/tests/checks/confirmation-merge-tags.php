<?php
if (!defined('ABSPATH')) { exit; }

/** Read-only validation of field merge tags in saved Multilang confirmations. */
final class PWE_System_Tests_Confirmation_Merge_Tags
{
    private const FIELD_TAG_PATTERN = '/\{[^{}:]+:(\d+(?:\.\d+)?)\}/';

    public static function run(): array
    {
        if (!class_exists('GFAPI')) {
            return ['status' => 'error', 'message' => 'Gravity Forms jest niedostępne. Nie sprawdzono potwierdzeń.'];
        }
        $forms = GFAPI::get_forms(null, false);
        if (is_wp_error($forms) || !is_array($forms)) {
            return ['status' => 'error', 'message' => 'Nie udało się odczytać formularzy.'];
        }
        $checked = []; $errors = []; $formCount = 0; $confirmationCount = 0;
        foreach ($forms as $summary) {
            $id = (int) ($summary['id'] ?? 0);
            $form = GFAPI::get_form($id);
            if (is_wp_error($form) || !is_array($form)) { $errors[] = "Formularz #$id: nie udało się odczytać danych."; continue; }
            if (empty($form['pwe_multilang_managed']) || !empty($form['is_trash'])) { continue; }
            $formCount++;
            $title = '#' . $id . ' · ' . (string) ($form['title'] ?? 'Bez nazwy');
            $fieldIds = [];
            foreach (($form['fields'] ?? []) as $field) {
                $fieldId = is_array($field) ? ($field['id'] ?? null) : (is_object($field) ? ($field->id ?? null) : null);
                if (is_numeric($fieldId)) { $fieldIds[(string) (float) $fieldId] = true; }
            }
            foreach ((is_array($form['confirmations'] ?? null) ? $form['confirmations'] : []) as $confirmation) {
                if (!is_array($confirmation)) { continue; }
                $confirmationCount++;
                $name = (string) ($confirmation['name'] ?? $confirmation['id'] ?? 'Bez nazwy');
                $rowTitle = $title . ' / ' . $name;
                $tags = []; self::collectTags($confirmation, $tags); $invalid = [];
                foreach ($tags as $tag) {
                    $baseId = (string) (float) explode('.', $tag['id'], 2)[0];
                    if (!isset($fieldIds[$baseId])) { $invalid[] = $tag['tag']; }
                }
                $invalid = array_values(array_unique($invalid));
                if ($invalid) { $errors[] = $rowTitle . ': nieistniejące pola: ' . implode(', ', $invalid); }
                $checked[] = ['id' => '#' . $id, 'title' => $rowTitle, 'status' => $invalid ? 'error' : 'success',
                    'expected' => 'Każdy merge tag pola wskazuje istniejące pole formularza',
                    'actual' => $invalid ?: 'Wszystkie pola istnieją',
                    'message' => $invalid ? 'Potwierdzenie zawiera merge tagi z nieistniejącym ID pola: ' . implode(', ', $invalid)
                        : 'Wszystkie merge tagi pól wskazują istniejące pola. Tagi systemowe pominięto.'];
            }
        }
        return [
            'status' => $errors ? 'error' : ($formCount ? 'success' : 'skipped'),
            'message' => $formCount ? "Sprawdzono formularze Multilang: $formCount" : 'Brak formularzy Multilang poza koszem.',
            'details' => ['summary' => ['forms' => $formCount, 'confirmations' => $confirmationCount], 'checked' => $checked, 'errors' => $errors, 'warnings' => []],
            'suggestions' => $errors ? ['Popraw lub usuń wskazane merge tagi w potwierdzeniach. Test tylko odczytuje formularze i niczego nie zmienia.'] : [],
            'view' => ['icon' => 'mail', 'columnLabel' => 'Formularz / potwierdzenie', 'metrics' => [
                ['label' => 'Potwierdzenia', 'value' => $confirmationCount, 'icon' => 'mail'],
                ['label' => 'Problemy', 'value' => count($errors), 'icon' => 'warning', 'tone' => 'error'],
            ]],
        ];
    }

    private static function collectTags($value, array &$tags): void
    {
        if (is_array($value)) { foreach ($value as $item) { self::collectTags($item, $tags); } return; }
        if (!is_string($value) || !preg_match_all(self::FIELD_TAG_PATTERN, $value, $matches, PREG_SET_ORDER)) { return; }
        foreach ($matches as $match) { $tags[] = ['tag' => $match[0], 'id' => $match[1]]; }
    }
}
