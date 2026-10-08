<?php
if (!defined('ABSPATH')) { exit; }

/** Read-only comparison of translations.php notification files: every language must translate the same keys as pl. */
final class PWE_System_Tests_Translations_Consistency
{
    /** @var array<string, string> template slug => "#12, #34" (or empty) */
    private static array $formIdCache = [];

    public static function run(): array
    {
        $root = PWE_MULTILANG_PATH . 'modules/forms/form-templates';
        if (!is_dir($root)) {
            return ['status' => 'error', 'message' => 'Katalog form-templates jest niedostępny.'];
        }
        $files = glob($root . '/*/notifications/*/translations.php');
        if (!is_array($files)) {
            return ['status' => 'error', 'message' => 'Nie udało się odczytać plików translations.php.'];
        }
        sort($files);
        self::$formIdCache = [];
        $checked = []; $errors = []; $warnings = []; $fileCount = 0; $langCount = 0;
        foreach ($files as $file) {
            $notificationDir = dirname($file);
            $templateDir = dirname($notificationDir, 2);
            $templateSlug = basename($templateDir);
            $id = $templateSlug . '/' . basename($notificationDir);
            $fileCount++;
            try {
                $data = require $file;
            } catch (Throwable $error) {
                $errors[] = "$id: błąd wczytywania pliku — {$error->getMessage()}.";
                $checked[] = ['title' => $id, 'status' => 'error', 'message' => 'Błąd wczytywania pliku translations.php.'];
                continue;
            }
            if (!is_array($data) || !$data) {
                $errors[] = "$id: plik translations.php nie zwraca tablicy języków.";
                $checked[] = ['title' => $id, 'status' => 'error', 'message' => 'Brak poprawnej tablicy języków.'];
                continue;
            }
            $referenceLang = is_array($data['pl'] ?? null) && $data['pl'] ? 'pl' : null;
            if (!$referenceLang) {
                // No pl base to compare against; use the language with the most keys instead.
                $warnings[] = "$id: brak języka bazowego pl, użyto najbardziej kompletnego języka jako wzorca.";
                $best = null; $bestCount = -1;
                foreach ($data as $lang => $strings) {
                    if (is_array($strings) && count($strings) > $bestCount) { $best = $lang; $bestCount = count($strings); }
                }
                $referenceLang = $best;
            }
            $referenceKeys = is_array($data[$referenceLang] ?? null) ? array_keys($data[$referenceLang]) : [];
            $otherLangs = [];
            $allKeys = $referenceKeys;
            foreach ($data as $lang => $strings) {
                $langCount++;
                if ($lang === $referenceLang) { continue; }
                $otherLangs[] = $lang;
                if (!is_array($strings)) {
                    $errors[] = "$id [$lang]: wartość języka nie jest tablicą.";
                    continue;
                }
                $allKeys = array_merge($allKeys, array_keys($strings));
            }
            $allKeys = array_values(array_unique($allKeys));

            // Build a variable -> present-languages matrix so every row lines up across the expected/actual panels.
            $expectedMap = []; $actualMap = []; $flagsMap = [$referenceLang => self::flagUrl((string) $referenceLang)];
            $langMissing = []; $langExtra = [];
            foreach ($allKeys as $key) {
                $inReference = in_array($key, $referenceKeys, true);
                $presentIn = [];
                foreach ($otherLangs as $lang) {
                    $strings = $data[$lang] ?? null;
                    if (!is_array($strings)) { continue; }
                    $flagsMap[$lang] = $flagsMap[$lang] ?? self::flagUrl((string) $lang);
                    $has = array_key_exists($key, $strings);
                    if ($has) { $presentIn[] = $lang; }
                    elseif ($inReference) { $langMissing[$lang][] = $key; }
                    if (!$inReference && $has) { $langExtra[$lang][] = $key; }
                }
                // Extra keys have no pl flag to show; the actual panel still lists which languages carry them.
                $expectedMap[$key] = $inReference ? [$referenceLang] : [];
                $actualMap[$key] = $presentIn;
            }

            $issueLangs = array_unique(array_merge(array_keys($langMissing), array_keys($langExtra)));
            foreach ($issueLangs as $lang) {
                $parts = [];
                if (!empty($langMissing[$lang])) { $parts[] = 'Brakuje: ' . implode(', ', $langMissing[$lang]); }
                if (!empty($langExtra[$lang])) { $parts[] = 'Nadmiarowe: ' . implode(', ', $langExtra[$lang]); }
                $errors[] = "$id [$lang]: " . implode(' ', $parts);
            }
            $missingCount = array_sum(array_map('count', $langMissing));
            $extraCount = array_sum(array_map('count', $langExtra));
            $status = $issueLangs ? 'error' : 'success';
            $formIds = self::formIdsFor($templateSlug);
            $checked[] = [
                'id' => $formIds !== '' ? $formIds : '—',
                'title' => $id,
                'subtitle' => strtoupper((string) $referenceLang) . ' → ' . count($otherLangs) . ' j.',
                'status' => $status,
                'expected' => $expectedMap,
                'actual' => $actualMap,
                'expectedText' => count($referenceKeys) . ' zmiennych (' . $referenceLang . ')',
                'actualText' => $issueLangs ? ("Braki: $missingCount, nadmiarowe: $extraCount") : 'Zgodne we wszystkich językach',
                'flags' => $flagsMap,
                'message' => $issueLangs
                    ? 'Niezgodności w: ' . implode(', ', array_map('strtoupper', $issueLangs))
                    : 'Wszystkie języki zawierają dokładnie te same zmienne co ' . $referenceLang . '.',
            ];
        }
        return [
            'status' => $errors ? 'error' : ($warnings ? 'warning' : ($fileCount ? 'success' : 'skipped')),
            'message' => $fileCount
                ? "Sprawdzono pliki translations.php: $fileCount"
                : 'Nie znaleziono żadnego pliku translations.php w szablonach powiadomień.',
            'details' => ['summary' => ['files' => $fileCount, 'languages' => $langCount], 'checked' => $checked, 'errors' => $errors, 'warnings' => $warnings],
            'suggestions' => $errors || $warnings ? ['Sprawdź wskazane pliki translations.php i uzupełnij lub usuń niezgodne klucze. Test tylko odczytuje pliki — niczego nie zmienia.'] : [],
            'view' => [
                'icon' => 'language',
                'columnLabel' => 'Szablon / powiadomienie',
                'fieldLabels' => ['expected' => 'Zmienna w języku bazowym', 'actual' => 'Języki z tłumaczeniem'],
                'metrics' => [
                    ['label' => 'Sprawdzone języki', 'value' => $langCount, 'icon' => 'language'],
                    ['label' => 'Problemy', 'value' => count($errors), 'icon' => 'warning', 'tone' => 'error'],
                    ['label' => 'Ostrzeżenia', 'value' => count($warnings), 'icon' => 'info', 'tone' => 'warning'],
                ],
            ],
        ];
    }

    /** Every real Gravity Forms form built from this template, formatted as "#12, #34". */
    private static function formIdsFor(string $templateSlug): string
    {
        if (array_key_exists($templateSlug, self::$formIdCache)) {
            return self::$formIdCache[$templateSlug];
        }
        $label = '';
        if (class_exists('GFAPI') && class_exists('PWE_Multilang_Form_Template_Registry')) {
            $template = PWE_Multilang_Form_Template_Registry::getTemplates()[$templateSlug] ?? null;
            $ids = [];
            foreach ($template['targets'] ?? [] as $target) {
                $formId = (int) ($target['form_id'] ?? 0);
                if ($formId > 0) { $ids[] = $formId; }
            }
            $ids = array_unique($ids);
            sort($ids);
            $label = implode(', ', array_map(static fn($formId) => '#' . $formId, $ids));
        }
        return self::$formIdCache[$templateSlug] = $label;
    }

    private static function flagUrl(string $lang): string
    {
        return defined('ICL_PLUGIN_URL') ? ICL_PLUGIN_URL . '/res/flags/' . sanitize_key($lang) . '.svg' : '';
    }
}
