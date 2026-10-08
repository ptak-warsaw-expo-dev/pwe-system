<?php
if (!defined('ABSPATH')) { exit; }

/**
 * Read-only scan of saved Gravity Forms notifications/confirmations AND translations.php files
 * for links pointing to the wrong language's page URL.
 */
final class PWE_System_Tests_Notification_Links
{
    /**
     * Slugs that only exist for a limited set of languages (not listed in website-translation.json at all);
     * any other language must fall back to one of the existing language versions instead of using its own prefix.
     */
    private const LIMITED_PAGES = [
        'add-to-calendar' => ['langs' => ['en'], 'fallback' => 'en'],
    ];

    public static function run(): array
    {
        $jsonPath = PWE_MULTILANG_PATH . 'website-translation.json';
        if (!is_file($jsonPath)) {
            return ['status' => 'error', 'message' => 'Nie znaleziono pliku website-translation.json.'];
        }
        $pages = json_decode((string) file_get_contents($jsonPath), true);
        if (!is_array($pages)) {
            return ['status' => 'error', 'message' => 'Plik website-translation.json zawiera nieprawidłowy JSON.'];
        }

        // Every {page,lang} gets its live URL: pl keeps the bare slug, other languages get a "/{lang}" prefix (see
        // PWE_Multilang_Form_Notification_Templates::replaceLangShortcodes()). Trivial "/" home slugs are excluded.
        $urlIndex = [];
        foreach ($pages as $pageKey => $translations) {
            if (!is_array($translations)) { continue; }
            foreach ($translations as $lang => $data) {
                $rawUrl = is_array($data) ? (string) ($data['url'] ?? '') : '';
                if ($rawUrl === '' || $rawUrl === '/') { continue; }
                $expected = $lang === 'pl' ? $rawUrl : '/' . $lang . $rawUrl;
                $urlIndex[$expected][] = ['page' => (string) $pageKey, 'lang' => (string) $lang];
            }
        }

        $checked = []; $errors = []; $warnings = [];
        $formCount = 0; $fileCount = 0; $checkedCount = 0;

        self::scanForms($urlIndex, $checked, $errors, $warnings, $formCount, $checkedCount);
        self::scanTemplateFiles($urlIndex, $checked, $errors, $checkedCount, $fileCount);

        return [
            'status' => $errors ? 'error' : ($warnings ? 'warning' : ($checkedCount ? 'success' : 'skipped')),
            'message' => "Sprawdzono formularze Multilang: $formCount; pliki translations.php: $fileCount",
            'details' => ['summary' => ['forms' => $formCount, 'files' => $fileCount, 'checked' => $checkedCount], 'checked' => $checked, 'errors' => $errors, 'warnings' => $warnings],
            'suggestions' => $errors ? ['Popraw wskazane linki tak, aby używały URL-a właściwego dla danego języka (lub jego zamiennika, jeśli strona nie istnieje we wszystkich językach), i zapisz zmiany ponownie. Test tylko odczytuje formularze i pliki — niczego nie zmienia.'] : [],
            'view' => [
                'icon' => 'language',
                'columnLabel' => 'Formularz / powiadomienie / plik',
                'metrics' => [
                    ['label' => 'Sprawdzone pozycje', 'value' => $checkedCount, 'icon' => 'mail'],
                    ['label' => 'Problemy', 'value' => count($errors), 'icon' => 'warning', 'tone' => 'error'],
                    ['label' => 'Ostrzeżenia', 'value' => count($warnings), 'icon' => 'info', 'tone' => 'warning'],
                ],
            ],
        ];
    }

    private static function scanForms(array $urlIndex, array &$checked, array &$errors, array &$warnings, int &$formCount, int &$checkedCount): void
    {
        if (!class_exists('GFAPI')) {
            $warnings[] = 'Gravity Forms jest niedostępne. Nie sprawdzono zapisanych formularzy.';
            return;
        }
        // null includes active and inactive forms; false excludes trash.
        $forms = GFAPI::get_forms(null, false);
        if (is_wp_error($forms) || !is_array($forms)) {
            $errors[] = 'Nie udało się odczytać formularzy.';
            return;
        }
        $sections = ['notifications' => 'powiadomienie', 'confirmations' => 'potwierdzenie'];
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
            if (($form['pwe_multilang_template_slug'] ?? '') === 'badge_generator_local'
                || preg_match('/Badge generator\s*\(local\)/i', (string) ($form['title'] ?? ''))) {
                $checked[] = ['id' => '#' . $id, 'title' => $title, 'status' => 'skipped',
                    'message' => 'Wewnętrzny generator identyfikatorów — powiadomienia nie wymagają tłumaczeń.'];
                continue;
            }
            foreach ($sections as $section => $label) {
                $items = is_array($form[$section] ?? null) ? $form[$section] : [];
                foreach ($items as $item) {
                    if (!is_array($item)) { continue; }
                    $name = (string) ($item['name'] ?? $item['id'] ?? 'Bez nazwy');
                    $rowTitle = "$title / $label: $name";
                    $content = self::linkContent($item, $section);
                    if ($section === 'confirmations' && ($item['type'] ?? 'message') === 'redirect'
                        && trim((string) ($item['url'] ?? '')) === '{embed_url}'
                        && empty($item['queryString'])) {
                        $checkedCount++;
                        $checked[] = ['id' => '#' . $id, 'title' => $rowTitle, 'status' => 'success',
                            'message' => '{embed_url} wskazuje stronę formularza w bieżącym języku.'];
                        continue;
                    }
                    if (!self::containsLink($content) && !($section === 'confirmations' && ($item['type'] ?? '') === 'page')) {
                        $checked[] = ['id' => '#' . $id, 'title' => $rowTitle, 'status' => 'skipped',
                            'message' => 'Brak linków do sprawdzenia (pusta treść lub wiadomość tekstowa).'];
                        continue;
                    }
                    $lang = self::detectLang($item);
                    if (!$lang) {
                        $warnings[] = "$rowTitle: nie udało się ustalić języka, pominięto sprawdzenie linków.";
                        $savedValues = array_intersect_key($item, array_flip([
                            'name', '_pwe_lang', 'conditionalLogic', 'subject', 'message', 'type', 'url', 'pageId', 'queryString',
                        ]));
                        $description = 'Nie udało się ustalić języka — pominięto sprawdzenie linków.';
                        $checked[] = ['id' => '#' . $id, 'title' => $rowTitle, 'status' => 'warning', 'message' => $description, 'savedValues' => $savedValues];
                        continue;
                    }
                    $checkedCount++;
                    self::checkItem($content, $urlIndex, $lang, '#' . $id, $rowTitle, $checked, $errors);
                }
            }
        }
    }

    private static function scanTemplateFiles(array $urlIndex, array &$checked, array &$errors, int &$checkedCount, int &$fileCount): void
    {
        $root = PWE_MULTILANG_PATH . 'modules/forms/form-templates';
        $files = is_dir($root) ? glob($root . '/*/notifications/*/translations.php') : [];
        if (!is_array($files)) { return; }
        sort($files);
        foreach ($files as $file) {
            $notificationDir = dirname($file);
            $templateSlug = basename(dirname($notificationDir, 2));
            $id = $templateSlug . '/' . basename($notificationDir);
            $fileCount++;
            try {
                $data = require $file;
            } catch (Throwable $error) {
                $errors[] = "$id: błąd wczytywania pliku — {$error->getMessage()}.";
                continue;
            }
            if (!is_array($data)) { continue; }
            foreach ($data as $lang => $strings) {
                if (!is_array($strings) || !is_string($lang) || $lang === '') { continue; }
                $checkedCount++;
                self::checkItem($strings, $urlIndex, $lang, $id, "$id [" . strtoupper($lang) . ']', $checked, $errors);
            }
        }
    }

    /** Runs both the website-translation.json check and the limited-pages fallback check, and records a row. */
    private static function checkItem($content, array $urlIndex, string $lang, string $rowId, string $rowTitle, array &$checked, array &$errors): void
    {
        $found = [];
        self::scan($content, $urlIndex, $lang, $found);
        self::scanLimitedPages($content, $found);
        if ($found) {
            $errors[] = "$rowTitle [" . strtoupper($lang) . ']: ' . implode('; ', array_map(
                static fn($hit) => "{$hit['field']}: {$hit['found_url']} ({$hit['reason']})",
                $found
            ));
        }
        $checked[] = [
            'id' => $rowId,
            'title' => $rowTitle,
            'subtitle' => strtoupper($lang),
            'status' => $found ? 'error' : 'success',
            'expected' => ($lang === 'abroad' ? 'Linki zagraniczne (wszystkie języki poza PL)' : 'Tylko linki dla języka ' . strtoupper($lang)),
            'actual' => $found
                ? array_map(static fn($hit) => "{$hit['field']}: {$hit['found_url']} — {$hit['reason']}", $found)
                : 'Brak linków innego języka',
            'message' => $found
                ? 'Znaleziono nieprawidłowe linki: ' . implode(', ', array_map(static fn($hit) => $hit['found_url'], $found))
                : 'Wszystkie znalezione linki stron odpowiadają językowi ' . strtoupper($lang) . '.',
        ];
    }

    /** Only inspect content actually used by this notification/confirmation. */
    private static function linkContent(array $item, string $section): array
    {
        if ($section === 'notifications') {
            return array_intersect_key($item, array_flip(['subject', 'message']));
        }
        $type = $item['type'] ?? 'message';
        if ($type === 'redirect') { return array_intersect_key($item, array_flip(['url', 'queryString'])); }
        if ($type === 'page') {
            return ['url' => get_permalink((int) ($item['pageId'] ?? 0)) ?: '', 'queryString' => $item['queryString'] ?? ''];
        }
        return ['message' => $item['message'] ?? ''];
    }

    private static function containsLink(array $content): bool
    {
        foreach ($content as $value) {
            if (is_string($value) && preg_match('~https?://|(?:href|src)\s*=|(?:^|[\s"\x27>])/(?!/)|\{embed_url\}|\[\w*(?:url|link)\w*\b~i', $value)) { return true; }
        }
        return false;
    }

    /** Mirrors PWE_Multilang_Form_Notification_Templates::getLangFromNotification() for saved (already built) items. */
    private static function detectLang(array $item): ?string
    {
        if (preg_match('/(?:^|[\s-])Abroad$/i', trim((string) ($item['name'] ?? '')))) { return 'abroad'; }
        if (!empty($item['_pwe_lang'])) { return strtolower((string) $item['_pwe_lang']); }
        $name = (string) ($item['name'] ?? '');
        if ($name !== '') {
            if (preg_match('/-\s*([A-Z]{2})(?:\s-|$)/', $name, $matches)) { return strtolower($matches[1]); }
            if (preg_match('/-([a-z]{2})$/', $name, $matches)) { return strtolower($matches[1]); }
        }
        foreach ((is_array($item['conditionalLogic']['rules'] ?? null) ? $item['conditionalLogic']['rules'] : []) as $rule) {
            if (($rule['field'] ?? '') === 'lang' && !empty($rule['value'])) {
                if (($rule['operator'] ?? 'is') === 'is') { return strtolower((string) $rule['value']); }
                if (($rule['operator'] ?? '') === 'isnot' && strtolower((string) $rule['value']) === 'pl' && ($item['conditionalLogic']['logicType'] ?? 'all') === 'all') { return 'abroad'; }
            }
        }
        return null;
    }

    /** Recursively finds page URLs whose language differs from $ownLang, ignoring occurrences nested inside another lang's prefix. */
    private static function scan($value, array $urlIndex, string $ownLang, array &$found, string $field = ''): void
    {
        if (is_array($value)) {
            foreach ($value as $key => $item) {
                self::scan($item, $urlIndex, $ownLang, $found, $field !== '' ? $field : (string) $key);
            }
            return;
        }
        if (!is_string($value) || $value === '') { return; }
        foreach ($urlIndex as $url => $owners) {
            if (!self::hasBareOccurrence($value, $url)) { continue; }
            foreach ($owners as $owner) {
                if ($owner['lang'] === $ownLang || ($ownLang === 'abroad' && $owner['lang'] !== 'pl')) { continue; }
                $found[] = ['field' => $field, 'found_url' => $url, 'reason' => "należy do {$owner['page']}/" . strtoupper($owner['lang'])];
            }
        }
    }

    /** Flags "/{lang}/{slug}/" links whose slug only really exists for a limited set of languages. */
    private static function scanLimitedPages($value, array &$found, string $field = ''): void
    {
        if (is_array($value)) {
            foreach ($value as $key => $item) {
                self::scanLimitedPages($item, $found, $field !== '' ? $field : (string) $key);
            }
            return;
        }
        if (!is_string($value) || $value === '') { return; }
        foreach (self::LIMITED_PAGES as $slug => $config) {
            if (preg_match_all('#/([a-z]{2})/' . preg_quote($slug, '#') . '/#', $value, $matches, PREG_SET_ORDER) === false) { continue; }
            foreach ($matches as $match) {
                $foundLang = $match[1];
                if (in_array($foundLang, $config['langs'], true)) { continue; }
                $found[] = [
                    'field' => $field,
                    'found_url' => $match[0],
                    'reason' => "strona /$slug/ istnieje tylko dla: " . implode(', ', array_map('strtoupper', $config['langs'])) . '; oczekiwano /' . $config['fallback'] . "/$slug/",
                ];
            }
        }
    }

    /** True if $needle occurs in $haystack at a real boundary, i.e. not immediately after another "/xx" language prefix. */
    private static function hasBareOccurrence(string $haystack, string $needle): bool
    {
        $pos = 0;
        while (($pos = strpos($haystack, $needle, $pos)) !== false) {
            $before = substr($haystack, max(0, $pos - 3), min(3, $pos));
            if (preg_match('/\/[a-z]{2}$/', $before) !== 1) { return true; }
            $pos++;
        }
        return false;
    }
}
