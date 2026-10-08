<?php
if (!defined('ABSPATH')) { exit; }

final class PWE_System_Tests_Report
{
    public static function init(): void
    {
        add_action('admin_post_pwe_system_tests_report', [self::class, 'open_admin_report']);
        add_action('rest_api_init', static function () {
            register_rest_route('pwe-system/v1', '/diagnostic', [
                'methods' => 'GET', 'callback' => [self::class, 'read'],
                'permission_callback' => [self::class, 'authorize'],
            ]);
        });
    }

    public static function open_admin_report(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die('Brak dostępu do raportu.', '', ['response' => 403]);
        }
        check_admin_referer('pwe_system_tests_report');
        $report = self::read();
        if (is_wp_error($report)) {
            $data = $report->get_error_data();
            wp_die(esc_html($report->get_error_message()), 'Raport diagnostyczny', ['response' => $data['status'] ?? 500]);
        }
        nocache_headers();
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: inline; filename="pwe-system-diagnostic.json"');
        header('X-Content-Type-Options: nosniff');
        header('X-Robots-Tag: noindex, nofollow');
        echo self::encode($report->get_data());
        exit;
    }
    public static function authorize(WP_REST_Request $request)
    {
        $secret = defined('PWE_API_KEY_3') ? constant('PWE_API_KEY_3') : null;
        $token = $request->get_param('token');
        $header = $request->get_header('authorization');
        if (is_string($header) && preg_match('/^Bearer\s+(.+)$/i', $header, $match)) { $token = $match[1]; }
        if (!is_string($secret) || $secret === '' || !is_string($token) || !hash_equals($secret, $token)) {
            return new WP_Error('pwe_report_forbidden', 'Brak dostępu do raportu.', ['status' => 403]);
        }
        return true;
    }

    /** Keep language-code lists on one line without changing their JSON array type. */
    private static function encode(array $report): string
    {
        $json = wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) { throw new RuntimeException('Nie udało się zakodować raportu JSON.'); }
        return preg_replace_callback('/\[\s*"[a-z]{2}(?:-[a-z0-9]+)?"(?:\s*,\s*"[a-z]{2}(?:-[a-z0-9]+)?")*\s*\]/', static function ($match) {
            return '[' . implode(', ', array_map(static fn($lang) => '"' . $lang . '"', json_decode($match[0], true))) . ']';
        }, $json);
    }
    private static function path(): string
    {
        $uploads = wp_upload_dir(null, false);
        if (!empty($uploads['error'])) { throw new RuntimeException('Katalog uploads jest niedostępny.'); }
        return trailingslashit($uploads['basedir']) . 'pwe-system/pwe-system-diagnostic.json';
    }

    public static function read()
    {
        try {
            $path = self::path();
            if (!is_file($path)) { return new WP_Error('pwe_report_missing', 'Raport nie został jeszcze zapisany.', ['status' => 404]); }
            $data = json_decode((string) file_get_contents($path), true);
            if (!is_array($data)) { throw new RuntimeException('Nie udało się odczytać raportu.'); }
            return new WP_REST_Response($data, 200, ['Cache-Control' => 'private, no-store, max-age=0', 'Pragma' => 'no-cache', 'X-Robots-Tag' => 'noindex, nofollow']);
        } catch (Throwable $error) {
            return new WP_Error('pwe_report_read', 'Nie udało się odczytać raportu.', ['status' => 500]);
        }
    }

    /** Lock the read/merge/write transaction; rename avoids exposing a partial JSON file. */
    public static function save(array $results, array $definitions): void
    {
        $path = self::path(); $dir = dirname($path);
        if (!wp_mkdir_p($dir)) { throw new RuntimeException('Nie udało się utworzyć katalogu raportu.'); }
        // Defense in depth on Apache/LiteSpeed; existing uploads protection is still required on Nginx.
        if (file_put_contents($dir . '/.htaccess', "Require all denied\n", LOCK_EX) === false) { throw new RuntimeException('Nie udało się zabezpieczyć katalogu raportu.'); }
        $lock = fopen($dir . '/.diagnostic.lock', 'c');
        if (!$lock) { throw new RuntimeException('Nie udało się otworzyć blokady raportu.'); }
        $temp = null;
        try {
            if (!flock($lock, LOCK_EX)) { throw new RuntimeException('Nie udało się zablokować raportu.'); }
            $report = is_file($path) ? json_decode((string) file_get_contents($path), true) : [];
            if (!is_array($report)) { throw new RuntimeException('Istniejący raport zawiera nieprawidłowy JSON.'); }
            $group = PWE_System_Tests::group();
            $tests = $report['groups'][$group]['tests'] ?? [];
            foreach ($definitions as $id => $definition) {
                $result = $results[$id] ?? null;
                if (is_array($result) && empty($result['continue']) && ($result['status'] ?? '') !== 'running') {
                    if (!isset($tests[$id]['timestamp']) || strcmp($result['timestamp'] ?? '', $tests[$id]['timestamp']) >= 0) {
                        $tests[$id] = ['name' => $definition['name']] + $result;
                    }
                } elseif (!isset($tests[$id])) {
                    $tests[$id] = ['name' => $definition['name'], 'status' => 'not_run', 'timestamp' => null];
                }
            }
            $report['schema_version'] = 2;
            $report['site_url'] = get_option('home');
            $report['updated_at'] = gmdate('c');
            $report['groups'][$group] = ['tests' => array_intersect_key($tests, $definitions)];
            foreach ($report['groups'] as &$savedGroup) {
                foreach ($savedGroup['tests'] as &$savedTest) {
                    // Presentation belongs to the admin UI; retain the diagnostic evidence.
                    unset($savedTest['view']);
                    if (isset($savedTest['details']['checked'])) {
                        foreach ($savedTest['details']['checked'] as &$row) { unset($row['flags']); }
                        unset($row);
                    }
                }
                unset($savedTest);
            }
            unset($savedGroup);
            $json = self::encode($report);
            if ($json === false) { throw new RuntimeException('Nie udało się zakodować raportu JSON.'); }
            $temp = tempnam($dir, '.diagnostic-');
            if (!$temp || file_put_contents($temp, $json . "\n") === false || !rename($temp, $path)) { throw new RuntimeException('Nie udało się zapisać pliku raportu.'); }
            $temp = null;
        } finally {
            if ($temp && is_file($temp)) { unlink($temp); }
            flock($lock, LOCK_UN); fclose($lock);
        }
    }
}
