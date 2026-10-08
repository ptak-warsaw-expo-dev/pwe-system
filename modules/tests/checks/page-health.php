<?php
if (!defined('ABSPATH')) { exit; }

/** Bounded HTTP-only audit. State is scoped to the current administrator and site group. */
final class PWE_System_Tests_Page_Health
{
    private const LIMIT = 4194304;

    private static function key(): string
    {
        return 'pwe_system_page_health_v4_' . get_current_user_id() . '_' . md5(PWE_System_Tests::group());
    }

    public static function run(bool $continue = false): array
    {
        if (!class_exists('DOMDocument')) { throw new RuntimeException('Page Health wymaga rozszerzenia PHP DOM.'); }
        $state = $continue ? get_transient(self::key()) : false;
        if ($continue && !is_array($state)) { throw new RuntimeException('Przebieg Page Health wygasł. Uruchom test ponownie.'); }
        if (!$continue) {
            $activeLanguages = class_exists('PWE_Multilang_Language_Catalog')
                ? PWE_Multilang_Language_Catalog::active_wpml() : [];
            if (!$activeLanguages) {
                return ['status' => 'skipped', 'message' => 'Brak aktywnych języków WPML. Nie sprawdzono stron z mapy.'];
            }
            $map = json_decode((string) file_get_contents(PWE_MULTILANG_PATH . 'website-translation.json'), true);
            if (!is_array($map) || !$map) { throw new RuntimeException('Nieprawidłowy website-translation.json.'); }
            $home = rtrim((string) get_option('home'), '/');
            $pages = [];
            foreach ($map as $key => $translations) {
                if (!is_array($translations)) { throw new RuntimeException('Nieprawidłowa mapa strony: ' . $key); }
                foreach ($translations as $lang => $data) {
                    if (!in_array((string) $lang, $activeLanguages, true)) { continue; }
                    $path = is_array($data) ? ($data['url'] ?? null) : null;
                    if (!is_string($path) || !preg_match('#^/(?!/)#', $path) || !preg_match('/^[a-z][a-z0-9-]*$/i', (string) $lang)) {
                        throw new RuntimeException('Nieprawidłowy adres w JSON: ' . $key . '/' . $lang);
                    }
                    $pages[] = ['id' => $key . '/' . $lang, 'title' => ($data['label'] ?? $key) . ' [' . strtoupper($lang) . ']', 'url' => $home . ($lang === 'pl' ? '' : '/' . $lang) . $path];
                }
            }
            if (!$pages) {
                return ['status' => 'skipped', 'message' => 'Mapa stron nie zawiera adresów dla aktywnych języków WPML.'];
            }
            $state = ['pages' => $pages, 'index' => 0, 'rows' => [], 'cache' => [], 'current' => null, 'started' => microtime(true), 'home' => $home];
        }
        $deadline = microtime(true) + 6;
        do {
            if ($state['current'] === null) {
                if ($state['index'] >= count($state['pages'])) { break; }
                $page = $state['pages'][$state['index']];
                $response = self::fetch($page['url'], $state['home']);
                $page += ['errors' => $response['errors'], 'warnings' => [], 'assets' => [], 'asset_index' => 0, 'http' => $response['status']];
                foreach ($response['urls'] as $url) {
                    if (self::development($url, $state['home'])) { $page['errors'][] = 'Adres środowiska testowego: ' . $url; }
                }
                if ($response['body'] !== '') {
                    self::inspect($response['body'], $response['url'], $state['home'], $page);
                    if (strlen($response['body']) >= self::LIMIT) { $page['warnings'][] = 'Odpowiedź osiągnęła limit 4 MiB — analiza HTML może być niepełna.'; }
                } elseif (!$response['errors']) { $page['errors'][] = 'Pusta odpowiedź strony.'; }
                $state['current'] = $page;
            } else {
                $page = &$state['current'];
                if ($page['asset_index'] < count($page['assets'])) {
                    $url = $page['assets'][$page['asset_index']++];
                    if (!isset($state['cache'][$url])) {
                        $response = self::fetch($url, $state['home'], 262144);
                        $issues = $response['errors'];
                        if (self::fatal($response['body'])) { $issues[] = 'Błąd PHP w odpowiedzi zasobu'; }
                        if (preg_match('/^\s*(?:<!doctype\s+html|<html\b)/i', $response['body'])) { $issues[] = 'Zamiast JS/CSS zwrócono dokument HTML'; }
                        foreach ($response['urls'] as $hop) {
                            if (self::development($hop, $state['home'])) { $issues[] = 'Przekierowanie do środowiska testowego: ' . $hop; }
                        }
                        $state['cache'][$url] = $issues;
                    }
                    foreach ($state['cache'][$url] as $issue) { $page['errors'][] = 'JS/CSS ' . $url . ': ' . $issue; }
                }
                if ($page['asset_index'] >= count($page['assets'])) {
                    $page['errors'] = array_values(array_unique($page['errors']));
                    $state['rows'][] = [
                        'id' => $page['id'], 'title' => $page['title'], 'url' => $page['url'],
                        'status' => $page['errors'] ? 'error' : ($page['warnings'] ? 'warning' : 'success'),
                        'expected' => 'HTTP 2xx, bez błędów PHP, sprawne lokalne JS/CSS, bez adresów localhost/dev/staging',
                        'actual' => array_merge(['HTTP ' . $page['http'] . '; lokalne JS/CSS: ' . count($page['assets'])], $page['errors'], $page['warnings']),
                        'actualText' => ($page['errors'] || $page['warnings']) ? implode(' / ', array_filter([$page['errors'] ? count($page['errors']) . ' errors' : '', $page['warnings'] ? count($page['warnings']) . ' warnings' : ''])) : 'OK · HTTP ' . $page['http'],
                        'message' => implode("\n", array_merge($page['errors'], $page['warnings'])) ?: 'Nie znaleziono problemów w odpowiedzi HTTP i zasobach wskazanych w HTML.',
                    ];
                    $state['index']++;
                    unset($page);
                    $state['current'] = null;
                }
                unset($page);
            }
        } while (microtime(true) < $deadline);
        $pending = $state['index'] < count($state['pages']);
        if ($pending) { set_transient(self::key(), $state, HOUR_IN_SECONDS); }
        else { delete_transient(self::key()); }
        $errors = []; $warnings = [];
        foreach ($state['rows'] as $row) {
            if ($row['status'] === 'error') { $errors[] = $row['url'] . ': ' . $row['message']; }
            if ($row['status'] === 'warning') { $warnings[] = $row['url'] . ': ' . $row['message']; }
        }
        return [
            'status' => $pending ? 'running' : ($errors ? 'error' : ($warnings ? 'warning' : 'success')),
            'continue' => $pending,
            'message' => 'Page Health: ' . $state['index'] . '/' . count($state['pages']) . ' stron; unikalne JS/CSS: ' . count($state['cache']),
            'execution_time' => round((microtime(true) - $state['started']) * 1000, 2),
            'details' => ['checked' => $state['rows'], 'errors' => $errors, 'warnings' => $warnings],
            'view' => ['icon' => 'forms', 'columnLabel' => 'Strona / język', 'metrics' => [
                ['label' => 'Sprawdzone strony', 'value' => $state['index'], 'icon' => 'forms'],
                ['label' => 'Strony z błędami', 'value' => count($errors), 'icon' => 'warning', 'tone' => 'error'],
                ['label' => 'Ostrzeżenia', 'value' => count($warnings), 'icon' => 'info', 'tone' => 'warning'],
            ]],
        ];
    }

    /** Follow only local redirects, checking every destination before requesting it. */
    private static function fetch(string $url, string $home, int $limit = self::LIMIT): array
    {
        $result = ['url' => $url, 'urls' => [], 'body' => '', 'status' => 0, 'errors' => []];
        for ($hop = 0; $hop < 4; $hop++) {
            $result['url'] = $url; $result['urls'][] = $url;
            if (!self::local($url, $home)) { $result['errors'][] = 'Przekierowanie poza lokalną witrynę: ' . $url; break; }
            $response = wp_safe_remote_get($url, ['timeout' => 10, 'redirection' => 0, 'limit_response_size' => $limit, 'cookies' => [], 'headers' => ['Cache-Control' => 'no-cache'], 'user-agent' => 'PWE-Page-Health/1.0']);
            if (is_wp_error($response)) { $result['errors'][] = 'Błąd HTTP: ' . $response->get_error_message(); break; }
            $status = (int) wp_remote_retrieve_response_code($response);
            $result['status'] = $status;
            $result['body'] = (string) wp_remote_retrieve_body($response);
            if (in_array($status, [301, 302, 303, 307, 308], true)) {
                $location = (string) wp_remote_retrieve_header($response, 'location');
                $next = self::absolute($location, $url);
                if (!$next) { $result['errors'][] = 'HTTP ' . $status . ': brak poprawnego Location'; break; }
                if (in_array($next, $result['urls'], true) || $hop === 3) { $result['errors'][] = 'Pętla lub zbyt wiele przekierowań: ' . $next; break; }
                $url = $next;
                continue;
            }
            if ($status < 200 || $status >= 300) { $result['errors'][] = 'Nieprawidłowy status HTTP ' . $status; }
            break;
        }
        return $result;
    }

    private static function inspect(string $html, string $url, string $home, array &$page): void
    {
        if (self::fatal($html)) { $page['errors'][] = 'Odpowiedź zawiera komunikat krytycznego błędu PHP.'; }
        $dom = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        try { $loaded = $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING); }
        finally { libxml_clear_errors(); libxml_use_internal_errors($previous); }
        if (!$loaded) { $page['warnings'][] = 'Nie udało się przeanalizować HTML.'; return; }
        $base = $url;
        $baseNode = $dom->getElementsByTagName('base')->item(0);
        if ($baseNode && $baseNode->hasAttribute('href')) { $base = self::absolute($baseNode->getAttribute('href'), $url) ?: $url; }
        $assets = [];
        foreach ($dom->getElementsByTagName('*') as $node) {
            foreach (['href', 'src', 'action', 'poster', 'data-src'] as $attribute) {
                if (!$node->hasAttribute($attribute)) { continue; }
                $link = self::absolute($node->getAttribute($attribute), $base);
                if ($link && self::development($link, $home)) { $page['errors'][] = 'Adres środowiska testowego: ' . $link; }
            }
            $asset = null;
            if ($node->tagName === 'script' && $node->hasAttribute('src')) { $asset = self::absolute($node->getAttribute('src'), $base); }
            if ($node->tagName === 'link' && preg_match('/(?:^|\s)(?:stylesheet|modulepreload)(?:\s|$)/i', $node->getAttribute('rel'))) { $asset = self::absolute($node->getAttribute('href'), $base); }
            if ($asset && self::local($asset, $home)) { $assets[$asset] = true; }
        }
        // Also catch absolute URLs in inline configuration, CSS, srcset and data attributes.
        $decoded = str_replace('\/', '/', html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        preg_match_all('~(?:https?:)?//[^\s<>"\x27\\\\)]+~i', $decoded, $matches);
        foreach ($matches[0] as $raw) {
            $link = self::absolute($raw, $base);
            if ($link && self::development($link, $home)) { $page['errors'][] = 'Adres środowiska testowego: ' . $link; }
        }
        $page['assets'] = array_keys($assets);
    }

    private static function fatal(string $body): bool
    {
        $text = html_entity_decode(strip_tags($body), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return (bool) preg_match('/(?:PHP\s+)?(?:Fatal\s+error|Parse\s+error)\s*:|Uncaught\s+(?:[a-zA-Z_\\\\]+\\\\)?(?:[a-zA-Z_]*Error|[a-zA-Z_]*Exception)\b|There has been a critical error on this website|W witrynie wystąpił błąd krytyczny|Allowed memory size of \d+ bytes exhausted|Maximum execution time of \d+ seconds exceeded/iu', $text);
    }

    private static function absolute(string $url, string $base): ?string
    {
        $url = trim($url);
        if ($url === '' || $url[0] === '#' || preg_match('/^(?:data|javascript|mailto|tel|blob):/i', $url)) { return null; }
        $url = WP_Http::make_absolute_url($url, $base);
        $parts = wp_parse_url($url);
        if (!$parts || !in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true) || empty($parts['host'])) { return null; }
        return preg_replace('/#.*$/s', '', $url);
    }

    private static function local(string $url, string $home): bool
    {
        $a = wp_parse_url($url); $b = wp_parse_url($home);
        return is_array($a) && is_array($b) && in_array(strtolower($a['scheme'] ?? ''), ['http', 'https'], true)
            && strtolower($a['host'] ?? '') === strtolower($b['host'] ?? '')
            && !isset($a['user']) && !isset($a['pass'])
            && (!isset($a['port']) || in_array($a['port'], [80, 443, $b['port'] ?? 443], true));
    }

    private static function development(string $url, string $home): bool
    {
        $host = strtolower(trim((string) wp_parse_url($url, PHP_URL_HOST), '[]'));
        // The site's configured host is valid even when auditing a staging site.
        $homeHost = strtolower(trim((string) wp_parse_url($home, PHP_URL_HOST), '[]'));
        if ($host !== '' && $host === $homeHost) { return false; }
        return $host === '::1' || (bool) preg_match('/^127\./', $host)
            || (bool) preg_match('/(?:^|[.-])(?:localhost|dev\d*|development|staging\d*|stage|test|local)(?:[.-]|$)/i', $host);
    }
}
