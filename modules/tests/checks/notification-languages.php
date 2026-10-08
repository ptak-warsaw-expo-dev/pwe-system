<?php
if (!defined('ABSPATH')) { exit; }

/** Read-only audit: WPML support, saved notification languages and template completeness. */
final class PWE_System_Tests_Notification_Languages
{
    public static function run(): array
    {
        $active = PWE_Multilang_Language_Catalog::active_wpml();
        if (!$active) {
            return ['status' => 'warning', 'message' => 'Nie udało się odczytać aktywnych języków WPML. Nie sprawdzono powiadomień.'];
        }
        $supported = PWE_Multilang_Language_Catalog::codes();
        $checked = []; $errors = []; $warnings = []; $formCount = 0;
        $unsupported = array_values(array_diff($active, $supported));
        $message = $unsupported ? 'Nieobsługiwane języki WPML: ' . implode(', ', $unsupported) . '.' : 'Wszystkie aktywne języki WPML są obsługiwane przez wtyczkę.';
        if (in_array('sl', $unsupported, true)) { $message .= ' SL oznacza słoweński; kod języka słowackiego to SK.'; }
        self::row($checked, $errors, $warnings, 'WPML', 'Aktywne języki WPML', $unsupported ? 'error' : 'success', implode(', ', $supported), implode(', ', $active), $message);        if (!class_exists('GFAPI')) {
            self::row($checked, $errors, $warnings, '—', 'Gravity Forms', 'error', 'Dostępne formularze', 'Brak', 'Gravity Forms jest niedostępne.');
            return self::result($checked, $errors, $warnings, $formCount);
        }
        $forms = GFAPI::get_forms(null, false);
        if (is_wp_error($forms) || !is_array($forms)) {
            self::row($checked, $errors, $warnings, '—', 'Gravity Forms', 'error', 'Dostępne formularze', 'Błąd odczytu', 'Nie udało się odczytać formularzy.');
            return self::result($checked, $errors, $warnings, $formCount);
        }
        $legacy = null;
        foreach ($forms as $summary) {
            $id = (int) ($summary['id'] ?? 0);
            $form = GFAPI::get_form($id);
            if (is_wp_error($form) || !is_array($form)) {
                self::row($checked, $errors, $warnings, "#$id", 'Formularz', 'error', 'Dane formularza', 'Błąd odczytu', 'Nie udało się odczytać formularza.');
                continue;
            }
            if (empty($form['pwe_multilang_managed']) || !empty($form['is_trash'])) { continue; }
            $formCount++;
            $title = (string) ($form['title'] ?? "#$id");
            $saved = is_array($form['notifications'] ?? null) ? $form['notifications'] : [];
            $fields = [];
            foreach ($form['fields'] ?? [] as $field) {
                $field = (array) $field;
                if (strtolower((string) ($field['adminLabel'] ?? '')) === 'lang') { $fields[] = (string) ($field['id'] ?? ''); }
            }
            $payload = null; $payloadError = null;
            try { $payload = self::payload($form, $forms, $legacy); }
            catch (Throwable $error) { $payloadError = $error; }
            $policy = $payload['_pwe_notification_language_policy'] ?? [];
            $languageNeutral = !empty($policy['language_neutral']);
            // Always inspect saved notifications, even if template identity cannot be resolved.
            foreach ($saved as $item) {
                if (!is_array($item)) { continue; }
                $langs = self::languages($item, $fields);
                if ($languageNeutral && !$langs) {
                    self::row($checked, $errors, $warnings, "#$id", $title . ' / ' . ($item['name'] ?? 'Bez nazwy'), 'skipped', 'Powiadomienie wewnętrzne bez języka', 'Bez języka', 'Szablon definiuje formularz wewnętrzny bez wersji językowych.');
                    continue;
                }
                $issues = [];
                foreach ($langs as $lang) {
                    if (!in_array($lang, $active, true)) { $issues[] = strtoupper($lang) . ': język nieaktywny w WPML'; }
                    if (!in_array($lang, $supported, true)) { $issues[] = strtoupper($lang) . ': język nieobsługiwany przez wtyczkę' . ($lang === 'sl' ? ' (słowacki ma kod SK)' : ''); }
                }
                if (count($langs) > 1) { $issues[] = 'Sprzeczne oznaczenia języka w kluczu, nazwie, metadanych lub regułach'; }
                self::row($checked, $errors, $warnings, "#$id", $title . ' / ' . ($item['name'] ?? 'Bez nazwy'), $issues ? 'error' : ($langs ? 'success' : 'warning'), implode(', ', array_intersect($active, $supported)), $langs ? implode(', ', $langs) : 'Nieustalony', $issues ? implode('; ', $issues) . '.' : ($langs ? 'Język zapisanej pozycji jest aktywny i obsługiwany.' : 'Brak jednoznacznego języka — powiadomienie może być wspólne dla wszystkich języków.'));
            }
            try {
                if ($payloadError) { throw $payloadError; }
                $expected = is_array($payload['notifications'] ?? null) ? $payload['notifications'] : [];
                // A template can itself omit an active language. Check each multilingual family
                // independently, scoped to the target's render languages (not every site language).
                $declared = $policy['languages'] ?? $payload['_pwe_declared_template_langs'] ?? $payload['_template_langs'] ?? null;
                $declarationLabel = isset($policy['languages']) ? 'polityce języków powiadomień' : 'danych szablonu';
                if (!is_array($declared)) {
                    // Current providers keep $template_langs local. Their generated notifications
                    // are authoritative; never infer requirements from the saved form itself.
                    $declared = self::notificationLanguages($expected);
                    $declarationLabel = 'powiadomieniach aktualnego wariantu szablonu';
                }
                $declared = array_values(array_unique(array_map(static fn($lang) => strtolower(trim((string) $lang)), $declared)));
                $scope = $payload['_pwe_group_langs'] ?? (!empty($payload['_pwe_separate_lang']) ? [$payload['_pwe_separate_lang']] : $declared);
                $variantLangs = is_array($scope) ? $scope : $declared;
                $scope = array_intersect($variantLangs, $declared, $active, $supported);
                foreach ($saved as $item) {
                    if (!is_array($item)) { continue; }
                    $outside = array_diff(self::languages($item, $fields), $scope);
                    $reasons = [];
                    foreach ($outside as $lang) {
                        if (!in_array($lang, $declared, true)) {
                            $reasons[] = strtoupper($lang) . ': język nie jest zadeklarowany w ' . $declarationLabel . ' tego szablonu';
                        } elseif (!in_array($lang, $variantLangs, true)) {
                            $reasons[] = strtoupper($lang) . ': język jest zadeklarowany w szablonie, ale nie należy do tego wariantu formularza';
                        }
                        // Inactive and unsupported languages are already reported above.
                    }
                    if ($reasons) {
                        self::row($checked, $errors, $warnings, "#$id", $title . ' / ' . ($item['name'] ?? 'Bez nazwy'), 'error', implode(', ', $scope), implode(', ', $outside), implode('; ', $reasons) . '. To kontrola deklaracji języków; obecność powiadomienia w szablonie jest sprawdzana osobno.');
                    }
                }
                $families = [];
                foreach ($expected as $item) {
                    if (!is_array($item)) { continue; }
                    $langs = self::languages($item, []);
                    if (count($langs) !== 1) { continue; }
                    $family = preg_replace('/__[a-z][a-z0-9-]*$/i', '', (string) ($item['pwe_notification_key'] ?? ''));
                    if ($family === '') { $family = preg_replace('/-\s*[a-z]{2}(?:-[a-z0-9]+)?\s*$/i', '', (string) ($item['name'] ?? '')); }
                    $families[$family][] = $langs[0];
                }
                foreach ($families as $family => $langs) {
                    $missing = array_diff($scope, $langs);
                    if ($missing) {
                        self::row($checked, $errors, $warnings, "#$id", $title . ' / ' . $family, 'warning', implode(', ', $scope), implode(', ', array_unique($langs)), 'Szablon nie przewiduje powiadomień dla aktywnych języków tego wariantu: ' . implode(', ', $missing) . '. Sprawdź, czy ograniczenie jest zamierzone (np. wersja zastępcza EN), czy brakuje obsługi języka w szablonie.');
                    }
                }
                $used = [];
                foreach ($expected as $item) {
                    if (!is_array($item)) { continue; }
                    $itemLangs = self::languages($item, []);
                    $matches = [];
                    foreach ($saved as $key => $actual) {
                        if (!is_array($actual)) { continue; }
                        $expectedKey = (string) ($item['pwe_notification_key'] ?? '');
                        $actualKey = (string) ($actual['pwe_notification_key'] ?? '');
                        if ($expectedKey !== '' && $actualKey !== '' ? $expectedKey === $actualKey : ($item['name'] ?? '') === ($actual['name'] ?? '')) {
                            $matches[$key] = $actual;
                            $used[$key] = true;
                        }
                    }
                    // Match every template entry before deciding whether an absent entry is required.
                    if (!$matches && $itemLangs && array_diff($itemLangs, $scope)) { continue; }
                    $issues = [];
                    if (!$matches) { $issues[] = 'Brak powiadomienia wymaganego przez aktualny wariant szablonu'; }
                    if (count($matches) > 1) { $issues[] = 'Powiadomienie jest zduplikowane'; }
                    foreach ($matches as $actual) {
                        if (($actual['isActive'] ?? true) != ($item['isActive'] ?? true)) { $issues[] = 'Stan aktywności różni się od szablonu'; }
                    }
                    self::row($checked, $errors, $warnings, "#$id", $title . ' / ' . ($item['name'] ?? 'Bez nazwy'), $issues ? 'error' : 'success', '1 powiadomienie; ' . (!empty($item['isActive'] ?? true) ? 'aktywne' : 'nieaktywne'), count($matches) . ' zapisanych', $issues ? implode('; ', $issues) . '.' : 'Powiadomienie występuje w aktualnym wariancie szablonu i jest zapisane. Zgodność języka sprawdzana jest osobno.');
                }
                foreach ($saved as $key => $item) {
                    if (isset($used[$key]) || !is_array($item)) { continue; }
                    $generated = !empty($item['pwe_notification_key']);
                    self::row($checked, $errors, $warnings, "#$id", $title . ' / ' . ($item['name'] ?? 'Bez nazwy'), $generated ? 'error' : 'warning', 'Powiadomienia aktualnego wariantu szablonu', 'Dodatkowe powiadomienie', $generated ? 'Zapisane powiadomienie nie występuje w aktualnym wariancie szablonu.' : 'Dodatkowa pozycja bez klucza generatora — sprawdź, czy została dodana ręcznie.');
                }
            } catch (Throwable $error) {
                self::row($checked, $errors, $warnings, "#$id", $title, 'warning', 'Jednoznaczny wariant szablonu', 'Nieustalony', 'Nie sprawdzono kompletności: ' . $error->getMessage());
            }
        }
        return self::result($checked, $errors, $warnings, $formCount);
    }

    private static function notificationLanguages(array $notifications): array
    {
        $languages = [];
        foreach ($notifications as $notification) {
            if (is_array($notification)) {
                $languages = array_merge($languages, self::languages($notification, []));
            }
        }
        return array_values(array_unique($languages));
    }
    private static function payload(array $form, array $forms, ?array &$legacy): array
    {
        // Forms dependencies may be loaded lazily outside the Multilang admin page.
        if (!class_exists('PWE_Multilang_Form_Template_Registry')) {
            if (!class_exists('PWE_Multilang_Forms') && defined('PWE_MULTILANG_PATH')) {
                $module = PWE_MULTILANG_PATH . 'modules/forms/forms-module.php';
                if (is_file($module)) { require_once $module; }
            }
            if (class_exists('PWE_Multilang_Forms') && is_callable(['PWE_Multilang_Forms', 'load_dependencies'])) {
                PWE_Multilang_Forms::load_dependencies();
            }
            if (!class_exists('PWE_Multilang_Form_Template_Registry')) {
                throw new RuntimeException('Moduł szablonów formularzy PWE Multilang jest niedostępny.');
            }
        }
        $slug = (string) ($form['pwe_multilang_template_slug'] ?? '');
        if ($slug !== '') {
            $provider = PWE_Multilang_Form_Template_Registry::providers()[$slug] ?? null;
            if (!$provider) { throw new RuntimeException('Nie znaleziono przypisanego szablonu.'); }
            $base = $provider->payload(PWE_Multilang_Year_Resolver::configured());
            if (!$base || empty($base['title'])) { throw new RuntimeException('Szablon nie zwrócił danych.'); }
            $targets = PWE_Multilang_Form_Payload_Expander::expand($base);
            $matches = array_filter($targets, static function ($target) use ($form) {
                $key = (string) ($form['pwe_multilang_target_key'] ?? '');
                return $key !== '' ? PWE_Multilang_Form_Identity::targetKey($target) === $key : ($target['title'] ?? '') === ($form['title'] ?? '');
            });
        } else {
            if ($legacy === null) { $legacy = PWE_Multilang_Form_Template_Registry::getTemplates(null, $forms); }
            $matches = [];
            foreach ($legacy as $template) {
                foreach ($template['targets'] ?? [] as $target) {
                    if ((int) ($target['form_id'] ?? 0) === (int) $form['id']) { $matches[] = $target['payload']; }
                }
            }
        }
        if (count($matches) !== 1) { throw new RuntimeException('Nie można jednoznacznie dopasować aktualnego wariantu szablonu.'); }
        return reset($matches);
    }

    /** Negative rules implement the EN fallback; they are not language declarations. */
    private static function languages(array $item, array $fields): array
    {
        $langs = [];
        if (!empty($item['_pwe_lang'])) { $langs[] = strtolower(trim((string) $item['_pwe_lang'])); }
        if (preg_match('/__([a-z][a-z0-9-]*)$/i', (string) ($item['pwe_notification_key'] ?? ''), $m) && strtolower($m[1]) !== 'all') { $langs[] = strtolower($m[1]); }
        if (preg_match('/-\s*([a-z]{2}(?:-[a-z0-9]+)?)\s*$/i', (string) ($item['name'] ?? ''), $m)) { $langs[] = strtolower($m[1]); }
        $logic = $item['conditionalLogic'] ?? [];
        if (is_array($logic) && ($logic['actionType'] ?? 'show') === 'show' && ($logic['logicType'] ?? 'all') === 'all') {
            foreach ($logic['rules'] ?? [] as $rule) {
                if (!is_array($rule)) { continue; }
                $field = (string) ($rule['fieldId'] ?? $rule['field'] ?? '');
                if (($field === 'lang' || in_array($field, $fields, true)) && ($rule['operator'] ?? '') === 'is' && isset($rule['value'])) {
                    $langs[] = strtolower(trim((string) $rule['value']));
                }
            }
        }
        return array_values(array_unique($langs));
    }

    private static function row(array &$checked, array &$errors, array &$warnings, string $id, string $title, string $status, string $expected, string $actual, string $message): void
    {
        $checked[] = compact('id', 'title', 'status', 'expected', 'actual', 'message');
        if ($status === 'error') { $errors[] = "$id · $title: $message"; }
        if ($status === 'warning') { $warnings[] = "$id · $title: $message"; }
    }

    /** Compact presentation; raw checks and error lists remain available in the report. */
    private static function groupedRows(array $checked): array
    {
        $groups = [];
        $rank = ['skipped' => 0, 'success' => 1, 'warning' => 2, 'error' => 3];
        foreach ($checked as $row) {
            $wpml = $row['id'] === 'WPML';
            $title = $wpml ? 'Aktywne języki WPML' : $row['title'];
            if (!$wpml && strpos($title, ' / ') !== false) {
                $title = preg_replace('/\s+-\s+(?:[a-z]{2}(?:-[a-z0-9]+)?|Abroad)\s*$/i', '', $title);
            }
            $key = $row['id'] . '|' . $title;
            if (!isset($groups[$key])) {
                $groups[$key] = ['id' => $row['id'], 'title' => $title, 'status' => 'skipped',
                    'expected' => [], 'actual' => [], 'expectedLanguages' => [], 'actualLanguages' => [], 'messages' => []];
            }
            $group = &$groups[$key];
            if (($rank[$row['status']] ?? 0) > $rank[$group['status']]) { $group['status'] = $row['status']; }
            $label = $row['title'];
            $number = count($group['expected']) + 1;
            $label .= ' — kontrola ' . $number;
            $group['expected'][$label] = $row['expected'];
            $group['actual'][$label] = $row['actual'] . ' [' . $row['status'] . ']';
            foreach (['expected', 'actual'] as $side) {
                if (preg_match('/^[a-z]{2}(?:-[a-z0-9]+)?(?:,\s*[a-z]{2}(?:-[a-z0-9]+)?)*$/i', $row[$side])) {
                    $group[$side . 'Languages'] = array_merge($group[$side . 'Languages'], preg_split('/,\s*/', strtolower($row[$side])));
                }
            }
            $group['messages'][] = $row['title'] . ': ' . $row['message'];
            unset($group);
        }
        foreach ($groups as &$group) {
            foreach (['expected', 'actual'] as $side) {
                $langs = array_values(array_unique($group[$side . 'Languages']));
                sort($langs);
                $group[$side . 'Text'] = $langs ? strtoupper(implode(', ', $langs)) : implode('; ', array_unique(array_values($group[$side])));
                unset($group[$side . 'Languages']);
            }
            if ($group['id'] === 'WPML') {
                $group['expectedText'] = 'Aktywne języki obsługiwane przez Multilang';
                $group['message'] = 'Kontrola zgodności aktywnych języków WPML z językami obsługiwanymi przez Multilang. Nie wymaga włączania wszystkich obsługiwanych języków.';
            } else {
                $group['message'] = implode("\n", array_unique($group['messages']));
            }
            unset($group['messages']);
        }
        unset($group);
        return array_values($groups);
    }
    private static function result(array $checked, array $errors, array $warnings, int $formCount): array
    {
        return [
            'status' => $errors ? 'error' : ($warnings ? 'warning' : ($formCount ? 'success' : 'skipped')),
            'message' => "Sprawdzono konfigurację WPML i formularze Multilang: $formCount",
            'details' => ['checked' => $checked, 'errors' => $errors, 'warnings' => $warnings],
            'suggestions' => $errors || $warnings ? ['Sprawdź aktywne języki WPML i wskazane powiadomienia. Po poprawieniu konfiguracji zsynchronizuj właściwe formularze i ponów test.'] : [],
            'view' => ['rows' => self::groupedRows($checked), 'icon' => 'language', 'columnLabel' => 'Konfiguracja / formularz / powiadomienie', 'metrics' => [
                ['label' => 'Formularze', 'value' => $formCount, 'icon' => 'forms'],
                ['label' => 'Problemy', 'value' => count($errors), 'icon' => 'warning', 'tone' => 'error'],
                ['label' => 'Ostrzeżenia', 'value' => count($warnings), 'icon' => 'info', 'tone' => 'warning'],
            ]],
        ];
    }
}
