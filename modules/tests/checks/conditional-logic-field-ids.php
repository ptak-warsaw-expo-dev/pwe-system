<?php
if (!defined('ABSPATH')) { exit; }

/** Read-only validation of field references in conditional logic for Multilang forms. */
final class PWE_System_Tests_Conditional_Logic_Field_Ids
{
    public static function run(): array
    {
        if (!class_exists('GFAPI')) {
            return ['status' => 'error', 'message' => 'Gravity Forms jest niedostępne. Nie sprawdzono reguł conditional logic.'];
        }
        $forms = GFAPI::get_forms(null, false);
        if (is_wp_error($forms) || !is_array($forms)) {
            return ['status' => 'error', 'message' => 'Nie udało się odczytać formularzy.'];
        }
        $checked = []; $errors = []; $formCount = 0; $ruleCount = 0;
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
            $sections = [
                'fields' => ['label' => 'pole', 'items' => $form['fields'] ?? []],
                'notifications' => ['label' => 'powiadomienie', 'items' => $form['notifications'] ?? []],
                'confirmations' => ['label' => 'potwierdzenie', 'items' => $form['confirmations'] ?? []],
            ];
            foreach ($sections as $section) {
                foreach ((is_array($section['items']) ? $section['items'] : []) as $item) {
                    if (!is_array($item) && !is_object($item)) { continue; }
                    $item = is_object($item) ? (array) $item : $item;
                    $itemName = (string) ($item['label'] ?? $item['name'] ?? $item['id'] ?? 'Bez nazwy');
                    $rules = []; self::collectRules($item, $rules);
                    $invalid = [];
                    foreach ($rules as $rule) {
                        $fieldId = (string) ($rule['fieldId'] ?? $rule['field'] ?? '');
                        // Some custom logic uses symbolic keys; only numeric Gravity Forms IDs are field references.
                        if (!is_numeric($fieldId)) { continue; }
                        $ruleCount++;
                        $baseId = (string) (float) explode('.', $fieldId, 2)[0];
                        if (!isset($fieldIds[$baseId])) { $invalid[] = $fieldId; }
                    }
                    $invalid = array_values(array_unique($invalid));
                    $rowTitle = $title . ' / ' . $section['label'] . ': ' . $itemName;
                    if ($invalid) { $errors[] = $rowTitle . ': nieistniejące field ID: ' . implode(', ', $invalid); }
                    if ($rules) {
                        $checked[] = ['id' => '#' . $id, 'title' => $rowTitle, 'status' => $invalid ? 'error' : 'success',
                            'expected' => 'Każde field ID reguły wskazuje istniejące pole formularza',
                            'actual' => $invalid ?: 'Wszystkie field ID istnieją',
                            'message' => $invalid ? 'Conditional logic odwołuje się do nieistniejącego field ID: ' . implode(', ', $invalid)
                                : 'Wszystkie numeryczne field ID w regułach istnieją.'];
                    }
                }
            }
        }
        return [
            'status' => $errors ? 'error' : ($formCount ? 'success' : 'skipped'),
            'message' => $formCount ? "Sprawdzono formularze Multilang: $formCount" : 'Brak formularzy Multilang poza koszem.',
            'details' => ['summary' => ['forms' => $formCount, 'rules' => $ruleCount], 'checked' => $checked, 'errors' => $errors, 'warnings' => []],
            'suggestions' => $errors ? ['Popraw conditional logic w wskazanych elementach, aby odwoływała się do istniejących pól. Test tylko odczytuje formularze i niczego nie zmienia.'] : [],
            'view' => ['icon' => 'forms', 'columnLabel' => 'Formularz / element', 'metrics' => [
                ['label' => 'Reguły', 'value' => $ruleCount, 'icon' => 'forms'],
                ['label' => 'Problemy', 'value' => count($errors), 'icon' => 'warning', 'tone' => 'error'],
            ]],
        ];
    }

    private static function collectRules($value, array &$rules): void
    {
        if (!is_array($value)) { return; }
        foreach ($value as $key => $item) {
            if ($key === 'conditionalLogic' && is_array($item)) {
                foreach (($item['rules'] ?? []) as $rule) { if (is_array($rule)) { $rules[] = $rule; } }
            }
            self::collectRules($item, $rules);
        }
    }
}
