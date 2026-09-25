<?php
if (! defined('ABSPATH')) exit;

class PWE_System_Functions {

    /**
     * Base directory of the PWE Elements AutoSwitch plugin.
     *
     * The implementation lives in PWE System now, but several helpers still
     * operate on elements/components stored in pwe-elements-auto-switch.
     */
    private static function elements_plugin_path() {
        if (defined('PWE_PLUGIN_PATH') && is_dir(PWE_PLUGIN_PATH)) {
            return trailingslashit(PWE_PLUGIN_PATH);
        }

        $path = trailingslashit(WP_PLUGIN_DIR) . 'pwe-elements-auto-switch/';

        return trailingslashit(apply_filters('pwe_system_elements_plugin_path', $path));
    }

    /**
     * Base URL of the PWE Elements AutoSwitch plugin.
     */
    private static function elements_plugin_url() {
        if (defined('PWE_PLUGIN_FILE') && is_file(PWE_PLUGIN_FILE)) {
            return trailingslashit(plugin_dir_url(PWE_PLUGIN_FILE));
        }

        $url = trailingslashit(plugins_url('pwe-elements-auto-switch'));

        return trailingslashit(apply_filters('pwe_system_elements_plugin_url', $url));
    }

    // TRANSLATIONS <==================================================================================>
    private static $translation_context = [
        'element_slug' => null,
        'group' => null,
        'element_type' => 'main'
    ];

    // Setting translation context for multi_translation function
    public static function set_translation_context($element_slug, $group, $element_type = 'main') {
        self::$translation_context['element_slug'] = $element_slug;
        self::$translation_context['group'] = $group;
        self::$translation_context['element_type'] = $element_type;
    }

    // Cache for loaded translations
    private static $translation_cache = [];
    public static function multi_translation($key) {
        $ctx = self::$translation_context;

        if (empty($ctx['element_slug']) || empty($ctx['group'])) {
            return $key;
        }

        $locale = get_locale();

        /*
        * Cache depends on the element and preset because preset translations
        * can override translations from the main assets directory.
        */
        $cache_key = implode('/', [
            $ctx['element_type'],
            $ctx['element_slug'],
            $ctx['group'],
        ]);

        if (!isset(self::$translation_cache[$cache_key])) {
            /*
            * Determine the main element directory.
            *
            * Components:
            * components/{element-slug}/
            *
            * Other elements:
            * elements/{element-type}/{element-slug}/
            */
            if ($ctx['element_type'] === 'components') {
                $element_path = self::elements_plugin_path()
                    . 'components/'
                    . $ctx['element_slug']
                    . '/';
            } else {
                $element_path = self::elements_plugin_path()
                    . 'elements/'
                    . $ctx['element_type']
                    . '/'
                    . $ctx['element_slug']
                    . '/';
            }

            // Shared translations for all presets.
            $global_translations_file = $element_path
                . 'assets/translations.json';

            // Translations assigned only to the current preset.
            $preset_translations_file = $element_path
                . 'presets/'
                . $ctx['group']
                . '/assets/translations.json';

            $global_translations = self::load_translation_file(
                $global_translations_file
            );

            $preset_translations = self::load_translation_file(
                $preset_translations_file
            );

            /*
            * Merge recursively by locale.
            *
            * Values from the preset file override values from the global file.
            */
            self::$translation_cache[$cache_key] = array_replace_recursive(
                $global_translations,
                $preset_translations
            );
        }

        $translations_data = self::$translation_cache[$cache_key];

        /*
        * First use the current locale.
        * If it does not exist, fall back to en_US.
        */
        $map = $translations_data[$locale]
            ?? $translations_data['en_US']
            ?? [];

        return $map[$key] ?? $key;
    }

    /**
     * Load and decode one translations JSON file.
     */
    private static function load_translation_file($file_path) {
        if (!is_string($file_path) || !file_exists($file_path)) {
            return [];
        }

        $json = file_get_contents($file_path);

        if ($json === false) {
            return [];
        }

        $data = json_decode($json, true);

        return is_array($data) ? $data : [];
    }


    // Assets per element
    public static function assets_per_element($element_slug, $element_type = 'main', $folder = 'elements') {
        $src = $folder . '/' . (!empty($element_type) ? $element_type . '/' : '') . $element_slug . '/assets/';

        $base_dir = self::elements_plugin_path() . $src;
        $base_url = self::elements_plugin_url() . $src;

        $el_name = 'el=' . urlencode($element_slug);

        // CSS
        if (file_exists($base_dir . 'style.css')) {
            $version = filemtime($base_dir . 'style.css');
            wp_enqueue_style(
                'pwe-' . $element_slug . '-style',
                $base_url . 'style.css?' . $el_name,
                [],
                $version
            );
        }

        // JS
        if (file_exists($base_dir . 'script.js')) {
            $version = filemtime($base_dir . 'script.js');
            wp_enqueue_script(
                'pwe-' . $element_slug . '-script',
                $base_url . 'script.js?' . $el_name,
                ['jquery'],
                $version,
                true
            );
        }
    }

    // Assets per group
    public static function assets_per_group($element_slug, $group, $element_type = 'main', $folder = 'elements', $atts = null) {
        $src = $folder . '/' . (!empty($element_type) ? $element_type . '/' : '') . $element_slug . '/presets/' . $group . '/assets/';

        $base_dir = self::elements_plugin_path() . $src;
        $base_url = self::elements_plugin_url() . $src;

        $el_name = 'el=' . urlencode($element_slug);
        $el_group = 'gr=' . urlencode($group);

        // CSS
        if (file_exists($base_dir . 'style.css')) {
            $version = filemtime($base_dir . 'style.css');
            wp_enqueue_style(
                'pwe-' . $element_slug . '-' . $group . '-style',
                $base_url . 'style.css?' . $el_name . '&' . $el_group,
                [],
                $version
            );
        }

        // JS
        if (file_exists($base_dir . 'script.js')) {
            $handle = 'pwe-' . $element_slug . '-' . $group . '-script';
            $version = filemtime($base_dir . 'script.js');

            wp_enqueue_script(
                $handle,
                $base_url . 'script.js?' . $el_name . '&' . $el_group,
                ['jquery'],
                $version,
                true
            );
            if (!empty($atts)) {
                wp_localize_script($handle, 'pwe_element_atts', $atts);
            }
        }
    }

    /**
     * Get exhibitor logos.
     *
     * @param int        $count       Maximum number of logos
     * @param bool       $shuffle     Should logos be randomized (default true)
     * @return array
     */
    public static function exhibitor_logos($count = 16, $shuffle = true) {

        $catalog_ids_old  = do_shortcode('[trade_fair_catalog]');
        $catalog_ids_new  = do_shortcode('[trade_fair_catalog_id]');
        $fair_date        = do_shortcode('[trade_fair_date]');

        if (stripos($fair_date, 'nowa data') !== false) {
            return [];
        }

        try {

            $mapped = [];
            $usedNames = [];

            /**
             * =========================================================
             * PRIORITY 1: NEW CATALOG (pwe-exhibitors.json)
             * =========================================================
             */
            if (!empty($catalog_ids_new)) {

                $local_file = $_SERVER['DOCUMENT_ROOT']
                    . '/wp-content/uploads/exhibitor-catalogs/pwe-exhibitors.json';

                if (!file_exists($local_file)) {
                    throw new Exception("Brak pliku: {$local_file}");
                }

                $data = json_decode(file_get_contents($local_file), true);

                if (!is_array($data)) {
                    throw new Exception("Błędny JSON: pwe-exhibitors.json");
                }

                foreach ($data as $item) {

                    $name = trim(
                        $item['companyInfo']['displayName']
                        ?? $item['companyInfo']['name']
                        ?? ''
                    );

                    if ($name === '') continue;

                    $nameKey = mb_strtolower($name);
                    $logo    = $item['companyInfo']['logoUrl'] ?? '';

                    if (empty($logo)) continue;
                    if (isset($usedNames[$nameKey])) continue;

                    $usedNames[$nameKey] = true;

                    $mapped[] = [
                        'name'  => $name,
                        'stand' => $item['stand']['standNumber'] ?? '',
                        'logo'  => $logo,
                        'www'   => $item['companyInfo']['website'] ?? ''
                    ];
                }
            }

            /**
             * =========================================================
             * PRIORITY 2: OLD CATALOG (old-pwe-exhibitors.json)
             * =========================================================
             */
            elseif (!empty($catalog_ids_old)) {

                $local_file = $_SERVER['DOCUMENT_ROOT']
                    . '/wp-content/uploads/exhibitor-catalogs/old-pwe-exhibitors.json';

                if (!file_exists($local_file)) {
                    throw new Exception("Brak pliku: {$local_file}");
                }

                $data = json_decode(file_get_contents($local_file), true);

                if (!is_array($data)) {
                    throw new Exception("Błędny JSON: old-pwe-exhibitors.json");
                }

                if (empty($data[$catalog_ids_old]['Wystawcy'])) {
                    return [];
                }

                foreach ($data[$catalog_ids_old]['Wystawcy'] as $item) {

                    $name = trim($item['Nazwa_wystawcy'] ?? '');
                    if ($name === '') continue;

                    $nameKey = mb_strtolower($name);
                    $logo    = $item['URL_logo_wystawcy'] ?? '';

                    if (empty($logo)) continue;
                    if (isset($usedNames[$nameKey])) continue;

                    $usedNames[$nameKey] = true;

                    $mapped[] = [
                        'name'  => $name,
                        'stand' => $item['Numer_stoiska'] ?? '',
                        'logo'  => $logo,
                        'www'   => $item['www'] ?? ''
                    ];
                }
            }

            /**
             * =========================================================
             * FINAL PROCESSING
             * =========================================================
             */

            if ($shuffle) {
                shuffle($mapped);
            }

            if (!empty($count)) {
                $mapped = array_slice($mapped, 0, $count);
            }

            return $mapped;

        } catch (Throwable $e) {

            error_log("[exhibitor_logos] " . $e->getMessage());

            if (current_user_can('administrator')) {
                echo '<script>console.error("exhibitor_logos ERROR: ' . htmlentities($e->getMessage()) . '")</script>';
            }

            return [];
        }
    }

    /**
     * Get the latest active Gravity Forms form ID by normalized title.
     *
     * Leading parenthetical prefixes are ignored, for example:
     * - "(2027) Rejestracja"       => "Rejestracja"
     * - "(2027) (PL) Rejestracja"  => "Rejestracja"
     *
     * Titles containing additional text are not matched:
     * - "(2027) Rejestracja (FB)"
     * - "(2027) Rejestracja gości wystawców"
     *
     * @param string $base_title Base form title without leading prefixes.
     * @return int|null Latest matching form ID or null when not found.
     */
    private static array $gf_form_resolver_cache = [];
    public static function get_gf_form_id(string $base_title): ?int {
        global $wpdb;

        $base_title = trim($base_title);

        if ($base_title === '') {
            return null;
        }

        $normalized_base_title = mb_strtolower($base_title, 'UTF-8');
        $cache_key = $normalized_base_title;

        if (array_key_exists($cache_key, self::$gf_form_resolver_cache)) {
            return self::$gf_form_resolver_cache[$cache_key];
        }

        $transient_key = 'pwe_gf_form_resolver_' . md5($cache_key);
        $cached = get_transient($transient_key);

        if ($cached !== false) {
            self::$gf_form_resolver_cache[$cache_key] = $cached ? (int) $cached : null;

            return self::$gf_form_resolver_cache[$cache_key];
        }

        $table = $wpdb->prefix . 'gf_form';

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "
                SELECT id, title
                FROM {$table}
                WHERE is_active = 1
                AND is_trash = 0
                AND title LIKE %s
                ORDER BY id DESC
                ",
                '%' . $wpdb->esc_like($base_title) . '%'
            ),
            ARRAY_A
        );

        if ($wpdb->last_error || empty($rows)) {
            set_transient($transient_key, 0, 10 * MINUTE_IN_SECONDS);
            self::$gf_form_resolver_cache[$cache_key] = null;

            return null;
        }

        $resolved_id = null;
        $resolved_year = 0;

        foreach ($rows as $row) {
            $title = trim((string) $row['title']);

            /*
            * Gets all the parentheses at the beginning.
            *
            * Examples:
            * "(2027) Rejestracja"      => prefixes: "(2027)"
            * "(2027) (PL) Rejestracja" => prefixes: "(2027) (PL)"
            */
            preg_match('/^(?<prefixes>(?:\s*\([^)]*\))+)?\s*(?<title>.*)$/u', $title, $matches);

            $title_without_prefixes = trim($matches['title'] ?? $title);

            /*
            * After removing the initial brackets, the name must be identical.
            * This will prevent "Registration (FB)" and "Guest Registration..."
            * from matching.
            */
            if (mb_strtolower($title_without_prefixes, 'UTF-8') !== $normalized_base_title) {
                continue;
            }

            $year = 0;
            $prefixes = $matches['prefixes'] ?? '';

            /*
            * We look for the year only in parentheses located at the beginning.
            */
            if (
                $prefixes !== ''
                && preg_match_all('/\((?:[^)]*?\b)?((?:19|20)\d{2})\b[^)]*\)/u', $prefixes, $year_matches)
                && !empty($year_matches[1])
            ) {
                $year = max(array_map('intval', $year_matches[1]));
            }

            $row_id = (int) $row['id'];

            /*
            * First priority is the highest year.
            * In case of the same year, we choose the form with the higher ID.
            */
            if (
                $resolved_id === null
                || $year > $resolved_year
                || ($year === $resolved_year && $row_id > $resolved_id)
            ) {
                $resolved_id = $row_id;
                $resolved_year = $year;
            }
        }

        set_transient(
            $transient_key,
            $resolved_id ?: 0,
            10 * MINUTE_IN_SECONDS
        );

        self::$gf_form_resolver_cache[$cache_key] = $resolved_id;

        return $resolved_id;
    }

    public static function render_component($slug, $group = 'all', $params = []) {
        $components = PWE_Elements_Data::get_all_components();

        if (!isset($components[$slug])) {
            return '';
        }

        $component = $components[$slug];
        $class     = $component['class'];
        $file      = self::elements_plugin_path() . $component['file'];

        if (!class_exists($class)) {
            if (!file_exists($file)) {
                return '';
            }

            require_once $file;
        }

        if (!class_exists($class) || !method_exists($class, 'render')) {
            return '';
        }

        ob_start();
        $class::render($group, $params);
        return ob_get_clean();
    }

    /**
     * Zwraca pliki mapy URL w kolejności priorytetu.
     *
     * Głównym źródłem jest zawsze aktualizowany plik z PWE Multilang.
     * Kopia w PWE System jest wyłącznie lokalnym fallbackiem.
     *
     * @return array
     */
    public static function get_website_translation_files() {
        return [
            trailingslashit(WP_PLUGIN_DIR) . 'pwe-multilang/website-translation.json',
            PWE_SYSTEM_PATH . 'data/website-translation.json',
        ];
    }

    /**
     * Sprawdza, czy bieżące żądanie znajduje się na stronie wymagającej sesji.
     * Obsługuje zapytania AJAX oraz dynamicznie pobiera adresy URL z pliku translation JSON.
     *
     * @return bool
     */
    public static function is_pwe_session_page() {
        if (wp_doing_ajax()) {
            return true;
        }

        if (is_admin()) {
            return false;
        }

        static $allowed_paths = null;

        if ($allowed_paths === null) {
            $allowed_paths = [
                '/potwierdzenie-rejestracji',
            ];

            foreach (self::get_website_translation_files() as $json_file) {
                if (!is_file($json_file) || !is_readable($json_file)) {
                    continue;
                }

                $json_content = file_get_contents($json_file);
                $translations = json_decode((string) $json_content, true);

                if (!is_array($translations) || json_last_error() !== JSON_ERROR_NONE) {
                    continue;
                }

                $target_keys = ['rejestracja', 'zostan_wystawca', 'potwierdzenie_rejestracji_wystawcy', 'krok2'];

                foreach ($target_keys as $key) {
                    if (!empty($translations[$key]) && is_array($translations[$key])) {
                        foreach ($translations[$key] as $lang_data) {
                            if (!empty($lang_data['url'])) {
                                $allowed_paths[] = $lang_data['url'];
                            }
                        }
                    }
                }

                // Pierwszy poprawny plik jest źródłem danych.
                // PWE System sięga po fallback dopiero, gdy plik główny jest niedostępny lub błędny.
                break;
            }
        }

        $current_path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
        $current_path = '/' . trim($current_path, '/') . '/';

        foreach ($allowed_paths as $path) {
            $normalized_path = '/' . trim($path, '/') . '/';

            // 1. Dokładne dopasowanie (np. dla /rejestracja/)
            if ($current_path === $normalized_path) {
                return true;
            }

            // 2. Dopasowanie wielojęzyczne (np. gdy URL to /en/registration/ a $normalized_path to /registration/)
            if ($normalized_path !== '/' && strlen($normalized_path) <= strlen($current_path) && substr($current_path, -strlen($normalized_path)) === $normalized_path) {
                return true;
            }
        }

        return false;
    }

    // <============================================================================================>
    // Synchronized functions from plugin PWElements 3.6.1 (20.08.2026) <========================================================>
    // <============================================================================================>

    /**
     * Random number
     */
    public static function id_rnd() {
        $id_rnd = rand(10000, 99999);
        return $id_rnd;
    }

    /**
     * Get locale
     */
    public static function lang() {

        // 1. WPML
        if (defined('ICL_LANGUAGE_CODE') && !empty(ICL_LANGUAGE_CODE)) {
            return strtolower(ICL_LANGUAGE_CODE);
        }

        if (function_exists('apply_filters')) {
            $wpml_lang = apply_filters('wpml_current_language', null);
            if (!empty($wpml_lang)) {
                return strtolower($wpml_lang);
            }
        }

        // 2. fallback
        $lang = get_locale(); // np. "en_US", "pl_PL", "de_DE"
        $lang = strtolower(str_replace('-', '_', $lang));

        return strtolower(substr($lang, 0, 2));
    }

    /**
     * Add logs to uploads/logs/{$filename}.log
     */
    public static function add_log($message, $filename = 'logs') {
        $upload_dir = wp_upload_dir();
        $dir = $upload_dir['basedir'] . '/logs';
        $file = $dir . '/' . $filename . '.log';

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $line = date('Y-m-d H:i:s') . ' - ' . $message . PHP_EOL;

        file_put_contents($file, $line, FILE_APPEND);
    }

    /**
     * Collecting all logs
     */
    private static $debug_logs = [];
    private static function debug_log($message, $type = 'log') {

        if (!function_exists('wp_get_current_user')) {
            return;
        }

        if (!current_user_can('administrator') || is_admin()) {
            return;
        }

        $key = $type . '|' . $message;

        if (!isset(self::$debug_logs[$key])) {
            self::$debug_logs[$key] = [
                'type' => $type,
                'message' => $message,
                'count' => 1
            ];
        } else {
            self::$debug_logs[$key]['count']++;
        }
    }

    /**
     * Output console logs
     */
    public static function output_db_connection_logs() {

        if (empty(self::$debug_logs)) {
            return;
        }

        echo '<script>';
        echo 'console.groupCollapsed("DB CONNECTIONS (' . self::class . ')");';

        foreach (self::$debug_logs as $log) {

            $count = $log['count'] ?? 1;

            $msg = '[' . $count . 'x] ' . $log['message'];
            $msg = addslashes($msg);

            echo "console.{$log['type']}('{$msg}');";
        }

        echo 'console.groupEnd();';
        echo '</script>';
    }

    /**
     * Returning server hosts
     */
    private static function resolve_server_addr_fallback() {
        $host = php_uname('n');
        switch ($host) {
            case 'dedyk93.cyber-folks.pl':
                return '94.152.206.93';
            case 'dedyk180.cyber-folks.pl':
                return '94.152.207.180';
            case 'dedyk239.cyber-folks.pl':
                return '91.225.28.47';
            case 'dedyk1072.cyber-folks.pl':
                return '91.225.28.72';
            default:
                return '';
        }
    }

    /**
     * List of DB servers
     */
    private static function get_database_servers() {

        // All PWE servers
        $servers = [
            [
                'host' => 'dedyk93.cyber-folks.pl',
                'name' => defined('PWE_DB_NAME_93') ? PWE_DB_NAME_93 : null,
                'user' => defined('PWE_DB_USER_93') ? PWE_DB_USER_93 : null,
                'pass' => defined('PWE_DB_PASSWORD_93') ? PWE_DB_PASSWORD_93 : null,
                'ip'  => '94.152.206.93'
            ],
            [
                'host' => 'dedyk180.cyber-folks.pl',
                'name' => defined('PWE_DB_NAME_180') ? PWE_DB_NAME_180 : null,
                'user' => defined('PWE_DB_USER_180') ? PWE_DB_USER_180 : null,
                'pass' => defined('PWE_DB_PASSWORD_180') ? PWE_DB_PASSWORD_180 : null,
                'ip'  => '94.152.207.180'
            ],
            [
                'host' => 'dedyk239.cyber-folks.pl',
                'name' => defined('PWE_DB_NAME_239') ? PWE_DB_NAME_239 : null,
                'user' => defined('PWE_DB_USER_239') ? PWE_DB_USER_239 : null,
                'pass' => defined('PWE_DB_PASSWORD_239') ? PWE_DB_PASSWORD_239 : null,
                'ip'  => '91.225.28.47'
            ],
            [
                'host' => 'dedyk1072.cyber-folks.pl',
                'name' => defined('PWE_DB_NAME_1072') ? PWE_DB_NAME_1072 : null,
                'user' => defined('PWE_DB_USER_1072') ? PWE_DB_USER_1072 : null,
                'pass' => defined('PWE_DB_PASSWORD_1072') ? PWE_DB_PASSWORD_1072 : null,
                'ip'  => '91.225.28.72'
            ],
        ];

        // CRON fallback
        if (empty($_SERVER['SERVER_ADDR'])) {
            $_SERVER['SERVER_ADDR'] = self::resolve_server_addr_fallback();
        }

        $current_ip = $_SERVER['SERVER_ADDR'];

        // Finding which host is localhost
        foreach ($servers as &$server) {
            if ($server['ip'] === $current_ip) {
                $server['host'] = 'localhost';
                $server['is_local'] = true;
            } else {
                $server['is_local'] = false;
            }
        }
        unset($server);

        return $servers;
    }

    /**
     * Connecting to CAP database
     */
    private static $cached_db_connection = null;
    public static function connect_database() {

        // Return cached connection if already connected
        if (self::$cached_db_connection !== null) {
            if (self::$cached_db_connection->dbh) {
                return self::$cached_db_connection;
            }
        }

        $servers = self::get_database_servers();

        // Timeout filter (only once)
        static $timeout_filter_added = false;
        if (!$timeout_filter_added) {
            add_filter('wpdb_connect_timeout', [self::class, 'set_db_timeout']);
            $timeout_filter_added = true;
        }

        // Find local server
        $is_local_server = false;
        foreach ($servers as $s) {
            if (!empty($s['is_local'])) {
                $is_local_server = true;
                break;
            }
        }

        // If running on local server, only use that one
        if ($is_local_server) {
            $servers = array_values(array_filter($servers, function ($s) {
                return !empty($s['is_local']);
            }));
        }

        // Local in-request blocking (no transient)
        static $blocked_hosts = [];

        foreach ($servers as $server) {

            if (empty($server['user']) || empty($server['pass']) || empty($server['name'])) {
                continue;
            }

            $host = $server['host'] ?? 'localhost';

            // Skip if already marked as blocked in THIS request
            if (!empty($blocked_hosts[$host])) {
                continue;
            }

            // Try connecting
            $wpdb = new wpdb($server['user'], $server['pass'], $server['name'], $host);

            if (!$wpdb->dbh) {

                error_log("PWE DB: Cannot connect to $host (immediate block).");
                error_log(json_encode([
                    'error' => mysqli_connect_error(),
                    'host'  => $host,
                    'db'    => $server['name']
                ], JSON_UNESCAPED_UNICODE));

                self::add_log('[PWE_System_Functions] DB CONNECTION FAILED: Host: ' . $host . ' User: ' . $server['name'] . ' Error: ' . mysqli_connect_error(), 'db-connections');

                // Mark blocked for this request so next iterations skip it
                $blocked_hosts[$host] = true;
                continue;
            }

            // Test query
            $test = $wpdb->get_var("SELECT 1");
            if ((int)$test !== 1) {
                error_log("PWE DB: Test query failed on $host (immediate block).");
                $blocked_hosts[$host] = true;
                continue;
            }

            // Cache connection
            self::$cached_db_connection = $wpdb;
            return $wpdb;
        }

        // No server worked
        error_log('PWE DB: All connection attempts failed.');
        return false;
    }

    /**
     * Timeout filter for wpdb connection
     */
    public static function set_db_timeout() {
        return 2;
    }

    // DATABASE CONNECTIONS START <==================================================================================>

    /**
     * Persistent JSON cache for CAP database data.
     *
     * Normal read priority:
     * STATIC -> TRANSIENT -> JSON FILE -> DATABASE
     *
     * JSON FILE is the persistent fallback when TRANSIENT data is unavailable.
     *
     * Cron refresh priority:
     * DATABASE -> JSON FILE -> TRANSIENT -> STATIC
     *
     * If the database is unavailable during forced refresh:
     * JSON FILE -> TRANSIENT -> empty result
     */
    private static $database_cache_force_refresh = false;
    private static function get_database_json_cache_dir() {
        $uploads = wp_upload_dir(null, false);

        if (!empty($uploads['error']) || empty($uploads['basedir'])) {
            self::debug_log('PWE JSON cache: cannot resolve uploads directory.', 'error');
            return false;
        }

        $dir = trailingslashit($uploads['basedir']) . 'pwe_cache/' . self::class;

        if (!is_dir($dir) && !wp_mkdir_p($dir)) {
            self::debug_log('PWE JSON cache: cannot create directory: ' . $dir, 'error');
            return false;
        }

        // Apache protection. The cache may contain internal data.
        $htaccess = trailingslashit($dir) . '.htaccess';
        if (!file_exists($htaccess)) {
            @file_put_contents(
                $htaccess,
                "Require all denied\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n"
            );
        }

        $index = trailingslashit($dir) . 'index.php';
        if (!file_exists($index)) {
            @file_put_contents($index, "<?php\n// Silence is golden.\n");
        }

        return $dir;
    }

    private static function get_database_json_cache_path($source, $cache_key) {
        $dir = self::get_database_json_cache_dir();

        if (!$dir) {
            return false;
        }

        $safe_source = sanitize_file_name($source);
        return trailingslashit($dir) . $safe_source . '--' . md5((string) $cache_key) . '.json';
    }

    /**
     * Preserve the difference between arrays and wpdb/stdClass objects.
     */
    private static function pack_database_json_value($value) {
        if (is_object($value)) {
            $packed = [];
            foreach (get_object_vars($value) as $key => $item) {
                $packed[$key] = self::pack_database_json_value($item);
            }

            return [
                '__pwe_cache_type' => 'object',
                '__pwe_cache_value' => $packed,
            ];
        }

        if (is_array($value)) {
            $packed = [];
            foreach ($value as $key => $item) {
                $packed[$key] = self::pack_database_json_value($item);
            }
            return $packed;
        }

        return $value;
    }

    private static function unpack_database_json_value($value) {
        if (!is_array($value)) {
            return $value;
        }

        if (
            isset($value['__pwe_cache_type'], $value['__pwe_cache_value']) &&
            $value['__pwe_cache_type'] === 'object' &&
            is_array($value['__pwe_cache_value'])
        ) {
            $object = new \stdClass();

            foreach ($value['__pwe_cache_value'] as $key => $item) {
                $object->{$key} = self::unpack_database_json_value($item);
            }

            return $object;
        }

        $unpacked = [];
        foreach ($value as $key => $item) {
            $unpacked[$key] = self::unpack_database_json_value($item);
        }

        return $unpacked;
    }

    private static function read_database_json_cache($source, $cache_key): array {
        $path = self::get_database_json_cache_path($source, $cache_key);

        if (!$path || !is_file($path) || !is_readable($path)) {
            return ['hit' => false, 'data' => null];
        }

        $json = @file_get_contents($path);
        if ($json === false || $json === '') {
            return ['hit' => false, 'data' => null];
        }

        $document = json_decode($json, true);

        if (
            !is_array($document) ||
            ($document['version'] ?? null) !== 1 ||
            ($document['source'] ?? null) !== $source ||
            ($document['cache_key'] ?? null) !== (string) $cache_key ||
            !array_key_exists('data', $document)
        ) {
            self::debug_log('PWE JSON cache: invalid file → ' . $path, 'error');
            return ['hit' => false, 'data' => null];
        }

        return [
            'hit' => true,
            'data' => self::unpack_database_json_value($document['data']),
        ];
    }

    private static function write_database_json_cache($source, $cache_key, $data, array $args = []): bool {
        $path = self::get_database_json_cache_path($source, $cache_key);

        if (!$path) {
            return false;
        }

        $document = [
            'version' => 1,
            'source' => $source,
            'cache_key' => (string) $cache_key,
            'generated_at_utc' => gmdate('c'),
            'args' => $args,
            'data' => self::pack_database_json_value($data),
        ];

        $json = wp_json_encode(
            $document,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
        );

        if ($json === false) {
            self::debug_log('PWE JSON cache: json_encode failed → source=' . $source . ', key=' . $cache_key, 'error');
            return false;
        }

        // Atomic write: readers never see a half-written JSON file.
        $tmp = $path . '.tmp-' . getmypid() . '-' . random_int(1000, 999999);

        if (@file_put_contents($tmp, $json, LOCK_EX) === false) {
            self::debug_log('PWE JSON cache: cannot write temp file → ' . $tmp, 'error');
            return false;
        }

        if (!@rename($tmp, $path)) {
            @unlink($tmp);
            self::debug_log('PWE JSON cache: cannot replace file → ' . $path, 'error');
            return false;
        }

        return true;
    }

    /**
     * Force refresh JSON + transient cache from CAP database.
     *
     * The cron automatically scans existing JSON cache files.
     * Each JSON file stores:
     * - source => getter method name
     * - args   => arguments required to recreate the same cache variant
     *
     * Refresh flow:
     * DATABASE -> JSON FILE -> TRANSIENT -> STATIC
     *
     * Important:
     * A cache variant must be created at least once during normal usage
     * before cron can discover and refresh it automatically.
     *
     * @param string|null $domain Current domain used during refresh.
     * @return array Refresh report.
     */
    public static function refresh_database_json_cache($domain = null): array {

        if (empty($domain)) {
            $domain = $_SERVER['HTTP_HOST'] ?? '';
        }

        $domain = strtolower(trim((string) $domain));
        $domain = preg_replace('/:\d+$/', '', $domain);

        if ($domain === '') {

            self::debug_log(
                __FUNCTION__ . ': DOMAIN IS EMPTY',
                'error'
            );

            return [
                'success' => false,
                'error' => 'domain_empty',
                'jobs' => [],
            ];
        }

        self::debug_log(
            __FUNCTION__ . ': START → domain=' . $domain
        );


        /**
         * Save current HTTP_HOST.
         *
         * Some older database getters still rely on $_SERVER['HTTP_HOST'],
         * so during cron refresh we temporarily set it to the requested domain.
         */
        $old_http_host = $_SERVER['HTTP_HOST'] ?? null;

        $_SERVER['HTTP_HOST'] = $domain;


        /**
         * Whitelist of methods that are allowed to be executed automatically
         * based on JSON cache files.
         *
         * This prevents an arbitrary method name stored in a modified JSON file
         * from being executed by the cron.
         */
        $allowed_methods = [

            'get_database_fairs_data' => true,
            'get_database_fairs_data_adds' => true,

            'get_database_translations_data' => true,

            'get_database_associates_data' => true,

            'get_database_groups_data' => true,
            'get_database_groups_contacts_data' => true,
            'get_database_groups_callcenter_data' => true,

            'get_database_logotypes_data' => true,
            'get_database_meta_data' => true,

            'get_database_fairs_data_profiles' => true,
            'get_database_premieres_data' => true,
            'get_database_fairs_data_opinions' => true,
            'get_database_fairs_data_sectors' => true,
            'get_database_fairs_data_tickets' => true,
            'get_database_fairs_data_speakers' => true,
            'get_database_fairs_data_guests' => true,
            'get_database_fairs_data_attractions' => true,
            'get_database_fairs_data_files' => true,

            'get_database_conferences_data' => true,
            'get_database_conference_adds_data' => true,

            'get_database_week_data' => true,
            'get_database_week_all' => true,
            'get_all_week_domains' => true,

            'get_database_store_data' => true,
            'get_database_store_packages_data' => true,

            'get_database_elements_data' => true,
            'get_database_elements_order_data' => true,
        ];


        /**
         * Build jobs automatically from existing JSON files.
         */
        $jobs = [];

        $dir = self::get_database_json_cache_dir();

        if (!$dir || !is_dir($dir)) {

            self::debug_log(
                __FUNCTION__ . ': JSON cache directory not available.',
                'error'
            );

            if ($old_http_host !== null) {
                $_SERVER['HTTP_HOST'] = $old_http_host;
            } else {
                unset($_SERVER['HTTP_HOST']);
            }

            return [
                'success' => false,
                'domain' => $domain,
                'error' => 'cache_directory_unavailable',
                'jobs' => [],
            ];
        }


        /**
         * Scan all JSON cache files.
         */
        $files = glob(
            trailingslashit($dir) . '*.json'
        );

        if (!is_array($files)) {
            $files = [];
        }


        foreach ($files as $path) {

            if (!is_file($path) || !is_readable($path)) {
                continue;
            }

            $json = @file_get_contents($path);

            if ($json === false || $json === '') {
                continue;
            }

            $document = json_decode(
                $json,
                true
            );

            if (!is_array($document)) {
                continue;
            }


            /**
             * Validate minimum JSON structure.
             */
            if (
                empty($document['source']) ||
                !isset($document['args']) ||
                !is_array($document['args'])
            ) {
                continue;
            }


            $method = (string) $document['source'];
            $args = $document['args'];


            /**
             * Security whitelist.
             */
            if (!isset($allowed_methods[$method])) {

                self::debug_log(
                    __FUNCTION__ .
                    ': method not allowed → ' .
                    $method,
                    'error'
                );

                continue;
            }


            /**
             * Method must still exist in the class.
             */
            if (!method_exists(self::class, $method)) {

                self::debug_log(
                    __FUNCTION__ .
                    ': METHOD NOT FOUND → ' .
                    $method,
                    'error'
                );

                continue;
            }


            /**
             * Generate a unique signature from method + args.
             *
             * This prevents running the same cache variant multiple times
             * if duplicate JSON files somehow exist.
             */
            $signature = $method . '|' . md5(
                serialize($args)
            );


            if (isset($jobs[$signature])) {
                continue;
            }


            $jobs[$signature] = [
                'method' => $method,
                'args' => $args,
                'file' => basename($path),
            ];
        }


        /**
         * Prepare report.
         */
        $report = [
            'success' => true,
            'domain' => $domain,
            'started_at' => date('Y-m-d H:i:s'),
            'files_found' => count($files),
            'jobs_found' => count($jobs),
            'jobs' => [],
        ];


        /**
         * Force getters to bypass:
         *
         * STATIC
         * TRANSIENT
         * JSON
         *
         * and query DATABASE directly.
         */
        self::$database_cache_force_refresh = true;


        try {

            foreach ($jobs as $signature => $job) {

                $method = $job['method'];
                $args = $job['args'];
                $file = $job['file'];


                try {

                    $start_time = microtime(true);


                    /**
                     * Execute getter using exactly the same arguments
                     * that originally created the JSON cache file.
                     */
                    $result = call_user_func_array(
                        [self::class, $method],
                        $args
                    );


                    $time = round(
                        (microtime(true) - $start_time) * 1000,
                        2
                    );


                    if (is_array($result)) {

                        $records = count($result);

                    } elseif ($result === null) {

                        $records = 0;

                    } else {

                        $records = 1;
                    }


                    $report['jobs'][$signature] = [
                        'status' => 'ok',
                        'method' => $method,
                        'args' => $args,
                        'file' => $file,
                        'records' => $records,
                        'time_ms' => $time,
                    ];


                    self::debug_log(
                        __FUNCTION__ .
                        ': OK → ' .
                        $method .
                        ' → args=' .
                        wp_json_encode($args) .
                        ' → records=' .
                        $records .
                        ' → time=' .
                        $time .
                        'ms'
                    );


                } catch (\Throwable $e) {

                    $report['success'] = false;

                    $report['jobs'][$signature] = [
                        'status' => 'error',
                        'method' => $method,
                        'args' => $args,
                        'file' => $file,
                        'message' => $e->getMessage(),
                        'error_file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ];


                    self::debug_log(
                        __FUNCTION__ .
                        ': ERROR → ' .
                        $method .
                        ' → ' .
                        $e->getMessage() .
                        ' → ' .
                        $e->getFile() .
                        ':' .
                        $e->getLine(),
                        'error'
                    );
                }
            }


        } finally {

            /**
             * Restore normal frontend cache behaviour.
             */
            self::$database_cache_force_refresh = false;


            /**
             * Restore original HTTP_HOST.
             */
            if ($old_http_host !== null) {

                $_SERVER['HTTP_HOST'] = $old_http_host;

            } else {

                unset($_SERVER['HTTP_HOST']);
            }
        }


        $report['finished_at'] = date(
            'Y-m-d H:i:s'
        );


        self::debug_log(
            __FUNCTION__ .
            ': FINISHED → domain=' .
            $domain .
            ' → jobs=' .
            count($jobs) .
            ' → success=' .
            ($report['success'] ? 'YES' : 'NO')
        );


        return $report;
    }

    /**
     * Get fairs data from CAP databases
     *
     * Modes:
     * - 'warsawexpo.eu' OR 'all' → return ALL fairs (no filters)
     * - get_database_fairs_data($domain) → fairs only for that domain
     * - get_database_fairs_data() → fairs within ±17 days window
     */
    private static $fairs_cache = [];
    public static function get_database_fairs_data($fair_domain = null): array {

        // Detect current domain
        $current_domain = $_SERVER['HTTP_HOST'] ?? '';

        // Cache key
        if ($current_domain === 'warsawexpo.eu' || $fair_domain === 'all') {
            $cache_key = 'all_fairs';
        } elseif ($fair_domain !== null) {
            $cache_key = $fair_domain;
        } else {
            $cache_key = 'month';
        }

        // Static cache
        if (!self::$database_cache_force_refresh && isset(self::$fairs_cache[$cache_key])) {
            self::debug_log(__FUNCTION__ . ': data from STATIC → key=' . $cache_key);
            return self::$fairs_cache[$cache_key];
        }

        // Transient cache
        $transient_key = 'pwe_fairs_' . md5($cache_key);

        // Transient cache: preferred persistent cache before JSON
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$fairs_cache[$cache_key] = $cached;
            self::debug_log(__FUNCTION__ . ': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient
        if (!self::$database_cache_force_refresh) {
            $file_cached = self::read_database_json_cache(__FUNCTION__, $cache_key);
            if ($file_cached['hit']) {
                $results = $file_cached['data'];
                set_transient($transient_key, $results, 3600);
                self::$fairs_cache[$cache_key] = $results;
                self::debug_log(__FUNCTION__ . ': data from JSON FILE → key=' . $cache_key);
                return $results;
            }
        }


        // Log timeout if transient exists
        $timeout = get_option('_transient_timeout_' . $transient_key);
        if ($timeout !== false) {
            $time_left = $timeout - time();
            $time_left_str = gmdate('H:i:s', max($time_left, 0));
        } else {
            $time_left_str = 'unknown';
        }



        // Connect database
        $cap_db = self::connect_database();

        if (!$cap_db) {
            // DB not available → use last transient if exists, else empty
            if ($cached !== false) {

                // Extend transient by 65 minutes in emergency mode
                set_transient($transient_key, $cached, 3600);

                self::debug_log(__FUNCTION__ . ': NO DB connection → using last TRANSIENT and extending 65min → key=' . $cache_key, 'error');

                self::$fairs_cache[$cache_key] = $cached;
                return $cached;
            }

            // No transient available → return empty
            self::debug_log(__FUNCTION__ . ': NO DB connection and no TRANSIENT → returning empty → key=' . $cache_key, 'error');
            error_log('get_database_fairs_data: NO DB connection and no TRANSIENT → returning empty → key=' . $cache_key);

            // CRON-safe: no wp_die()
            if (defined('DOING_CRON') && DOING_CRON) {
                return [];
            }

            // Frontend fallback → user-friendly 503
            wp_die(
                '<h1>Przepraszamy</h1><p>Trwają prace techniczne. Spróbuj ponownie później.</p>',
                'Strona tymczasowo niedostępna',
                ['response' => 503]
            );

            return [];
        }

        // Base SQL
        $sql = "
            SELECT
                f.id,
                f.fair_name_pl,
                f.fair_name_en,
                f.fair_desc_pl,
                f.fair_desc_en,
                f.fair_short_desc_pl,
                f.fair_short_desc_en,
                f.fair_full_desc_pl,
                f.fair_full_desc_en,
                f.fair_date_start,
                f.fair_date_start_hour,
                f.fair_date_end,
                f.fair_date_end_hour,
                f.fair_edition,
                f.fair_visitors,
                f.fair_exhibitors,
                f.fair_countries,
                f.estimations,
                f.fair_facebook,
                f.fair_instagram,
                f.fair_linkedin,
                f.fair_youtube,
                f.fair_color_accent,
                f.fair_color_main2,
                f.fair_hall,
                f.fair_area,
                f.fair_kw,
                f.fair_badge,
                f.fair_domain,
                f.fair_shop,
                f.fair_group,

                MAX(CASE WHEN fa.slug = 'category_pl' THEN fa.data END) AS category_pl,
                MAX(CASE WHEN fa.slug = 'category_en' THEN fa.data END) AS category_en,
                MAX(CASE WHEN fa.slug = 'industry' THEN fa.data END) AS industry,
                MAX(CASE WHEN fa.slug = 'konf_name' THEN fa.data END) AS konf_name,
                MAX(CASE WHEN fa.slug = 'konf_title_pl' THEN fa.data END) AS konf_title_pl,
                MAX(CASE WHEN fa.slug = 'konf_title_en' THEN fa.data END) AS konf_title_en,
                MAX(CASE WHEN fa.slug = 'konf_desc_pl' THEN fa.data END) AS konf_desc_pl,
                MAX(CASE WHEN fa.slug = 'konf_desc_en' THEN fa.data END) AS konf_desc_en,
                MAX(CASE WHEN fa.slug = 'about_title_pl' THEN fa.data END) AS about_title_pl,
                MAX(CASE WHEN fa.slug = 'about_title_en' THEN fa.data END) AS about_title_en,
                MAX(CASE WHEN fa.slug = 'about_desc_pl' THEN fa.data END) AS about_desc_pl,
                MAX(CASE WHEN fa.slug = 'about_desc_en' THEN fa.data END) AS about_desc_en,
                MAX(CASE WHEN fa.slug = 'fair_kw_new' THEN fa.data END) AS fair_kw_new,
                MAX(CASE WHEN fa.slug = 'fair_kw_old_arch' THEN fa.data END) AS fair_kw_old_arch,
                MAX(CASE WHEN fa.slug = 'fair_kw_new_arch' THEN fa.data END) AS fair_kw_new_arch,
                MAX(CASE WHEN fa.slug = 'catalog_type' THEN fa.data END) AS catalog_type,
                MAX(CASE WHEN fa.slug = 'fair_entrance' THEN fa.data END) AS fair_entrance

            FROM fairs f
            LEFT JOIN fair_adds fa
                ON fa.fair_id = f.id
                AND fa.slug IN (
                    'category_pl',
                    'category_en',
                    'industry',
                    'konf_name',
                    'konf_title_pl',
                    'konf_title_en',
                    'fair_kw_new',
                    'fair_kw_old_arch',
                    'fair_kw_new_arch',
                    'catalog_type',
                    'fair_entrance',
                    'about_title_pl',
                    'about_title_en',
                    'about_desc_pl',
                    'about_desc_en',
                    'konf_title_pl',
                    'konf_title_en',
                    'konf_desc_pl',
                    'konf_desc_en'
                )
        ";

        // WHERE conditions
        $params = [];

        if ($current_domain === 'warsawexpo.eu' || $fair_domain === 'all') {
            // no WHERE
        } elseif ($fair_domain !== null) {
            $sql .= " WHERE f.fair_domain = %s ";
            $params[] = $fair_domain;
        } else {
            $current_fair = $cap_db->get_row(
                $cap_db->prepare(
                    "SELECT fair_date_start, fair_date_end
                    FROM fairs
                    WHERE fair_domain = %s
                    LIMIT 1",
                    $current_domain
                )
            );

            if ($current_fair && !empty($current_fair->fair_date_start) && !empty($current_fair->fair_date_end)) {
                $start = date('Y/m/d', strtotime($current_fair->fair_date_start . ' -17 days'));
                $end   = date('Y/m/d', strtotime($current_fair->fair_date_end . ' +17 days'));

                $sql .= "
                    WHERE f.fair_date_start >= %s
                    AND f.fair_date_end <= %s
                ";
                $params[] = $start;
                $params[] = $end;
            }
            // else → no WHERE
        }

        $sql .= " GROUP BY f.id ";

        $start_time = microtime(true);

        // Execute query
        if (!empty($params)) {
            $query = call_user_func_array([$cap_db, 'prepare'], array_merge([$sql], $params));
            $results = $cap_db->get_results($query);
        } else {
            $results = $cap_db->get_results($sql);
        }

        $time = round((microtime(true) - $start_time) * 1000, 2);

        // SQL error
        if ($cap_db->last_error) {
            self::debug_log(__FUNCTION__ . ': SQL error → ' . addslashes($cap_db->last_error), 'error');
            // Use last transient if available
            if ($cached !== false) {
                set_transient($transient_key, $cached, 3600);
                self::$fairs_cache[$cache_key] = $cached;
                return $cached;
            }
            self::$fairs_cache[$cache_key] = [];
            return [];
        }

        // Cache results for 65 minutes
        self::write_database_json_cache(__FUNCTION__, $cache_key, $results, [$fair_domain]);
        set_transient($transient_key, $results, 3600);

        self::$fairs_cache[$cache_key] = $results;
        self::debug_log(__FUNCTION__ . ': data from database DIRECTLY (SQL time ' . $time . 'ms) → key=' . $cache_key . ', host=' . $cap_db->dbhost . ' [' . gethostname() . '] and saved to TRANSIENT.');

        return $results;
    }

    /**
     * Get fairs additional data from CAP databases
     */
    private static $fairs_adds_cache = [];
    public static function get_database_fairs_data_adds($fair_domain = null): array {

        // Resolve current domain
        $fair_domain = $fair_domain ?? $_SERVER['HTTP_HOST'];
        $cache_key = $fair_domain;

        // STATIC cache
        if (!self::$database_cache_force_refresh && isset(self::$fairs_adds_cache[$cache_key])) {
            self::debug_log(__FUNCTION__ . ': data from STATIC → key=' . $cache_key);
            return self::$fairs_adds_cache[$cache_key];
        }

        // Transient
        $transient_key = 'pwe_fairs_adds_' . md5($cache_key);

        // Transient cache: preferred persistent cache before JSON
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$fairs_adds_cache[$cache_key] = $cached;
            self::debug_log(__FUNCTION__ . ': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient
        if (!self::$database_cache_force_refresh) {
            $file_cached = self::read_database_json_cache(__FUNCTION__, $cache_key);
            if ($file_cached['hit']) {
                $results = $file_cached['data'];
                set_transient($transient_key, $results, 3600);
                self::$fairs_adds_cache[$cache_key] = $results;
                self::debug_log(__FUNCTION__ . ': data from JSON FILE → key=' . $cache_key);
                return $results;
            }
        }


        // Log transient timeout
        $timeout = get_option('_transient_timeout_' . $transient_key);
        if ($timeout !== false) {
            $time_left = $timeout - time();
            $time_left_str = gmdate('H:i:s', max($time_left, 0));
        } else {
            $time_left_str = 'unknown';
        }



        // Connect DB
        $cap_db = self::connect_database();
        if (!$cap_db) {
            if ($cached !== false) {
                set_transient($transient_key, $cached, 3600);
                self::debug_log(__FUNCTION__ . ': NO DB connection → using last TRANSIENT and extending 65min → key=' . $cache_key, 'error');
                self::$fairs_adds_cache[$cache_key] = $cached;
                return $cached;
            }
            self::debug_log(__FUNCTION__ . ': NO DB connection and no TRANSIENT → returning empty → key=' . $cache_key, 'error');
            self::$fairs_adds_cache[$cache_key] = [];
            return [];
        }

        // SQL query
        $sql = "
            SELECT
                f.id,
                f.fair_domain,
                MAX(CASE WHEN fa.slug = 'konf_name' THEN fa.data END)       AS konf_name,
                MAX(CASE WHEN fa.slug = 'konf_title_pl' THEN fa.data END)   AS konf_title_pl,
                MAX(CASE WHEN fa.slug = 'konf_title_en' THEN fa.data END)   AS konf_title_en,
                MAX(CASE WHEN fa.slug = 'konf_desc_pl' THEN fa.data END)    AS konf_desc_pl,
                MAX(CASE WHEN fa.slug = 'konf_desc_en' THEN fa.data END)    AS konf_desc_en,
                MAX(CASE WHEN fa.slug = 'about_title_pl' THEN fa.data END)  AS about_title_pl,
                MAX(CASE WHEN fa.slug = 'about_title_en' THEN fa.data END)  AS about_title_en,
                MAX(CASE WHEN fa.slug = 'about_desc_pl' THEN fa.data END)   AS about_desc_pl,
                MAX(CASE WHEN fa.slug = 'about_desc_en' THEN fa.data END)   AS about_desc_en,
                MAX(CASE WHEN fa.slug = 'medal-ceremony' THEN fa.data END)  AS medal_ceremony,
                MAX(CASE WHEN fa.slug = 'videos' THEN fa.data END)   AS videos
            FROM fairs f
            LEFT JOIN fair_adds fa
                ON fa.fair_id = f.id
                AND fa.slug IN (
                    'konf_name','konf_title_pl','konf_title_en',
                    'konf_desc_pl','konf_desc_en','about_title_pl',
                    'about_title_en','about_desc_pl','about_desc_en', 'medal-ceremony', 'videos'
                )
            WHERE f.fair_domain = %s
            GROUP BY f.id
        ";

        $start_time = microtime(true);

        $results = $cap_db->get_results($cap_db->prepare($sql, $fair_domain));

        $time = round((microtime(true) - $start_time) * 1000, 2);

        // SQL error
        if ($cap_db->last_error) {
            self::debug_log(__FUNCTION__ . ': SQL error: ' . addslashes($cap_db->last_error), 'error');
            if ($cached !== false) {
                set_transient($transient_key, $cached, 3600);
                self::$fairs_adds_cache[$cache_key] = $cached;
                return $cached;
            }
            self::$fairs_adds_cache[$cache_key] = [];
            return [];
        }

        // Save transient + STATIC cache
        self::write_database_json_cache(__FUNCTION__, $cache_key, $results, [$fair_domain]);
        set_transient($transient_key, $results, 3600);
        self::$fairs_adds_cache[$cache_key] = $results;
        self::debug_log(__FUNCTION__ . ': data from database DIRECTLY (SQL time ' . $time . 'ms) → key=' . $cache_key . ', host=' . $cap_db->dbhost . ' [' . gethostname() . '] and saved to TRANSIENT.');

        return $results;
    }

    /**
     * Get translations data from CAP databases
     */
    private static $translations_cache = [];
    public static function get_database_translations_data($fair_domain = null): array {

        // Cache key
        $cache_key = $fair_domain ?? 'all';

        // STATIC cache
        if (!self::$database_cache_force_refresh && isset(self::$translations_cache[$cache_key])) {
            self::debug_log(__FUNCTION__ . ': STATIC → key=' . $cache_key);
            return self::$translations_cache[$cache_key];
        }

        // Transient
        $transient_key = 'pwe_translations_' . md5($cache_key);

        // Transient cache: preferred persistent cache before JSON
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$translations_cache[$cache_key] = $cached;
            self::debug_log(__FUNCTION__ . ': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient
        if (!self::$database_cache_force_refresh) {
            $file_cached = self::read_database_json_cache(__FUNCTION__, $cache_key);
            if ($file_cached['hit']) {
                $results = $file_cached['data'];
                set_transient($transient_key, $results, 3600);
                self::$translations_cache[$cache_key] = $results;
                self::debug_log(__FUNCTION__ . ': data from JSON FILE → key=' . $cache_key);
                return $results;
            }
        }


        // Log transient timeout
        $timeout = get_option('_transient_timeout_' . $transient_key);
        if ($timeout !== false) {
            $time_left = $timeout - time();
            $time_left_str = gmdate('H:i:s', max($time_left, 0));
        } else {
            $time_left_str = 'unknown';
        }



        // Connect DB
        $cap_db = self::connect_database();
        if (!$cap_db) {
            if ($cached !== false) {
                set_transient($transient_key, $cached, 3600);
                self::debug_log(__FUNCTION__ . ': NO DB connection → using last TRANSIENT and extending 65min → key=' . $cache_key, 'error');
                self::$translations_cache[$cache_key] = $cached;
                return $cached;
            }
            self::debug_log(__FUNCTION__ . ': NO DB connection and no TRANSIENT → returning empty → key=' . $cache_key, 'error');
            self::$translations_cache[$cache_key] = [];
            return [];
        }

        // SQL
        $sql = "
            SELECT
                f.id,
                f.fair_domain,

                f.fair_name_pl,
                f.fair_desc_pl,
                f.fair_short_desc_pl,
                f.fair_full_desc_pl,

                f.fair_name_en,
                f.fair_desc_en,
                f.fair_short_desc_en,
                f.fair_full_desc_en,

                fa.slug,
                fa.data,

                t.language,
                t.translation
            FROM fairs f
            LEFT JOIN fair_adds fa
                ON fa.fair_id = f.id
                AND fa.slug IN (
                    'category_pl',
                    'category_en',
                    'about_title_pl',
                    'about_title_en',
                    'about_desc_pl',
                    'about_desc_en',
                    'konf_title_pl',
                    'konf_title_en',
                    'konf_desc_pl',
                    'konf_desc_en'
                )
            LEFT JOIN translations t ON t.fair_id = f.id
        ";

        $start_time = microtime(true);

        if ($fair_domain !== null) {
            $sql .= " WHERE f.fair_domain = %s";
            $rows = $cap_db->get_results($cap_db->prepare($sql, $fair_domain));
        } else {
            $rows = $cap_db->get_results($sql);
        }

        $time = round((microtime(true) - $start_time) * 1000, 2);

        // SQL error
        if ($cap_db->last_error) {
            self::debug_log(__FUNCTION__ . ': SQL error: ' . addslashes($cap_db->last_error), 'error');
            if ($cached !== false) {
                set_transient($transient_key, $cached, 3600);
                self::$translations_cache[$cache_key] = $cached;
                return $cached;
            }
            self::$translations_cache[$cache_key] = [];
            return [];
        }

        // Map results
        $results = [];
        foreach ($rows as $row) {
            $fair_id = $row->id;
            if (!isset($results[$fair_id])) {
                $results[$fair_id] = [
                    'fair_domain' => $row->fair_domain,

                    'fair_name_pl' => $row->fair_name_pl,
                    'fair_desc_pl' => $row->fair_desc_pl,
                    'fair_short_desc_pl' => $row->fair_short_desc_pl,
                    'fair_full_desc_pl' => $row->fair_full_desc_pl,

                    'fair_short_desc_en' => $row->fair_short_desc_en,
                    'fair_name_en' => $row->fair_name_en,
                    'fair_desc_en' => $row->fair_desc_en,
                    'fair_full_desc_en' => $row->fair_full_desc_en,
                ];
            }

            $adds_fields = [
                'category_pl',
                'about_title_pl',
                'about_desc_pl',
                'konf_title_pl',
                'konf_desc_pl',
                'category_en',
                'about_title_en',
                'about_desc_en',
                'konf_title_en',
                'konf_desc_en'
            ];

            if (!empty($row->slug) && in_array($row->slug, $adds_fields, true)) {
                $results[$fair_id][$row->slug] = $row->data;
            }

            if (!empty($row->translation)) {
                $lang = strtolower($row->language);
                $data = json_decode($row->translation, true);
                if ($data) {
                    foreach ([
                        'fair_name',
                        'fair_desc',
                        'fair_short_desc',
                        'fair_full_desc',
                        'category',
                        'about_title',
                        'about_desc',
                        'konf_title',
                        'konf_desc'
                    ] as $field) {
                        if (isset($data[$field])) {
                            $results[$fair_id]["{$field}_$lang"] = $data[$field];
                        }
                    }
                }
            }
        }

        $results = array_values($results);

        // WPML languages
        $languages = apply_filters('wpml_active_languages', null);

        $langs = [];

        if (!empty($languages) && is_array($languages)) {

            foreach ($languages as $lang) {
                $code = $lang['language_code'];

                // ignore base languages
                if (in_array($code, ['pl', 'en'], true)) {
                    continue;
                }

                $langs[] = $code;
            }

        } else {
            // Fallback only when WPML is not available
            $langs = ['cs','de','it','lt','lv','ru','sk','uk'];
        }

        // Save transient + STATIC cache
        self::write_database_json_cache(__FUNCTION__, $cache_key, $results, [$fair_domain]);
        set_transient($transient_key, $results, 3600);
        self::$translations_cache[$cache_key] = $results;
        self::debug_log(__FUNCTION__ . ': data from database DIRECTLY (SQL time ' . $time . 'ms) → key=' . $cache_key . ', host=' . $cap_db->dbhost . ' [' . gethostname() . '] and saved to TRANSIENT.');

        return $results;
    }

    /**
     * Get associates data from CAP database.
     */
    private static $associates_cache = [];
    public static function get_database_associates_data (
        $fair_domain = null,
        bool $fair_block = false
    ): array {

        // Resolve domain
        $fair_domain = $fair_domain ?? $_SERVER['HTTP_HOST'];
        $fair_domain = strtolower(trim($fair_domain));

        // Include mode in cache key
        $cache_key = $fair_domain . '|fair_block:' . (int) $fair_block;

        // Static cache
        if (!self::$database_cache_force_refresh && isset(self::$associates_cache[$cache_key])) {
            self::debug_log(
                'get_database_associates_data: data from STATIC → key=' . $cache_key
            );

            return self::$associates_cache[$cache_key];
        }

        // Transient cache
        $transient_key = 'pwe_associates_' . md5($cache_key);

        // Transient cache: preferred persistent cache before JSON
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$associates_cache[$cache_key] = $cached;
            self::debug_log(__FUNCTION__ . ': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient
        if (!self::$database_cache_force_refresh) {
            $file_cached = self::read_database_json_cache(__FUNCTION__, $cache_key);
            if ($file_cached['hit']) {
                $results = $file_cached['data'];
                set_transient($transient_key, $results, 3600);
                self::$associates_cache[$cache_key] = $results;
                self::debug_log(__FUNCTION__ . ': data from JSON FILE → key=' . $cache_key);
                return $results;
            }
        }


        // Cache timeout
        $timeout = get_option('_transient_timeout_' . $transient_key);

        if ($timeout !== false) {
            $time_left = $timeout - time();
            $time_left_str = gmdate('H:i:s', max($time_left, 0));
        } else {
            $time_left_str = 'unknown';
        }



        // Connect database
        $cap_db = self::connect_database();

        if (!$cap_db) {
            self::debug_log(
                'get_database_associates_data: NO DB connection → returning empty → key=' .
                $cache_key,
                'error'
            );

            self::$associates_cache[$cache_key] = [];

            return [];
        }

        // Build query
        if ($fair_block) {
            $sql = "
                SELECT slug, fair_associates
                FROM associates
                WHERE FIND_IN_SET(
                    %s,
                    REPLACE(LOWER(fair_associates), ' ', '')
                ) > 0
            ";
        } else {
            $sql = "
                SELECT main_fair_domain, slug, fair_associates, desc_pl, desc_en
                FROM associates
                WHERE LOWER(main_fair_domain) = %s
            ";
        }

        // Run query
        $start_time = microtime(true);

        $prepared_sql = $cap_db->prepare($sql, $fair_domain);
        $results = $cap_db->get_results($prepared_sql);

        $time = round((microtime(true) - $start_time) * 1000, 2);

        // Handle SQL error
        if ($cap_db->last_error) {
            self::debug_log(
                'get_database_associates_data: SQL error: ' .
                addslashes($cap_db->last_error),
                'error'
            );

            self::$associates_cache[$cache_key] = [];

            return [];
        }

        // Save cache
        self::write_database_json_cache(__FUNCTION__, $cache_key, $results, [$fair_domain, $fair_block]);
        set_transient($transient_key, $results, 3600);
        self::$associates_cache[$cache_key] = $results;

        self::debug_log(
            'get_database_associates_data: data from database DIRECTLY ' .
            '(SQL time ' . $time . 'ms) → key=' . $cache_key .
            ', fair_block=' . (int) $fair_block .
            ', host=' . $cap_db->dbhost .
            ' [' . gethostname() . '] and saved to TRANSIENT.'
        );

        return $results;
    }

    /**
     * Get store data from CAP databases
     */
    private static $store_cache = null;
    public static function get_database_store_data(): array {

        $cache_key = 'store';

        // STATIC cache
        if (!self::$database_cache_force_refresh && self::$store_cache !== null) {
            self::debug_log(__FUNCTION__ . ': data from STATIC memory');
            return self::$store_cache;
        }

        // Transient
        $transient_key = 'pwe_store_data';

        // Transient cache: preferred persistent cache before JSON
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$store_cache = $cached;
            self::debug_log(__FUNCTION__ . ': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient
        if (!self::$database_cache_force_refresh) {
            $file_cached = self::read_database_json_cache(__FUNCTION__, $cache_key);
            if ($file_cached['hit']) {
                $results = $file_cached['data'];
                set_transient($transient_key, $results, 3600);
                self::$store_cache = $results;
                self::debug_log(__FUNCTION__ . ': data from JSON FILE → key=' . $cache_key);
                return $results;
            }
        }


        // Log transient timeout
        $timeout = get_option('_transient_timeout_' . $transient_key);
        if ($timeout !== false) {
            $time_left = $timeout - time();
            $time_left_str = gmdate('H:i:s', max($time_left, 0));
        } else {
            $time_left_str = 'unknown';
        }



        // Connect DB
        $cap_db = self::connect_database();
        if (!$cap_db) {
            if ($cached !== false) {
                set_transient($transient_key, $cached, 3600);
                self::debug_log(__FUNCTION__ . ': NO DB connection → using last TRANSIENT and extending 65min → key=' . $cache_key, 'error');
                self::$store_cache = $cached;
                return $cached;
            }
            self::debug_log(__FUNCTION__ . ': NO DB connection and no TRANSIENT → returning empty → key=' . $cache_key, 'error');
            self::$store_cache = [];
            return [];
        }

        // SQL query
        $sql = "SELECT * FROM shop";
        $start_time = microtime(true);
        $results = $cap_db->get_results($sql);
        $time = round((microtime(true) - $start_time) * 1000, 2);

        // SQL error
        if ($cap_db->last_error) {
            self::debug_log(__FUNCTION__ . ': SQL error: ' . addslashes($cap_db->last_error), 'error');
            if ($cached !== false) {
                set_transient($transient_key, $cached, 3600);
                self::$store_cache = $cached;
                return $cached;
            }
            self::$store_cache = [];
            return [];
        }

        // Save transient + STATIC cache
        self::write_database_json_cache(__FUNCTION__, $cache_key, $results, []);
        set_transient($transient_key, $results, 3600);
        self::$store_cache = $results;
        self::debug_log(__FUNCTION__ . ': data from database DIRECTLY (SQL time ' . $time . 'ms) → key=' . $cache_key . ', host=' . $cap_db->dbhost . ' [' . gethostname() . '] and saved to TRANSIENT.');

        return $results;
    }

    /**
     * Get store packages data from CAP databases
     */
    private static $store_packages_cache = null;
    public static function get_database_store_packages_data(): array {

        $cache_key = 'store_packages';

        // STATIC cache
        if (!self::$database_cache_force_refresh && self::$store_packages_cache !== null) {
            self::debug_log(__FUNCTION__ . ': data from STATIC memory');
            return self::$store_packages_cache;
        }

        // Transient
        $transient_key = 'pwe_store_packages';

        // Transient cache: preferred persistent cache before JSON
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$store_packages_cache = $cached;
            self::debug_log(__FUNCTION__ . ': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient
        if (!self::$database_cache_force_refresh) {
            $file_cached = self::read_database_json_cache(__FUNCTION__, $cache_key);
            if ($file_cached['hit']) {
                $results = $file_cached['data'];
                set_transient($transient_key, $results, 3600);
                self::$store_packages_cache = $results;
                self::debug_log(__FUNCTION__ . ': data from JSON FILE → key=' . $cache_key);
                return $results;
            }
        }


        // Log transient timeout
        $timeout = get_option('_transient_timeout_' . $transient_key);
        $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';



        // Connect DB
        $cap_db = self::connect_database();
        if (!$cap_db) {
            if ($cached !== false) {
                set_transient($transient_key, $cached, 3600);
                self::debug_log(__FUNCTION__ . ': NO DB connection → using last TRANSIENT and extending 65min → key=' . $cache_key, 'error');
                self::$store_packages_cache = $cached;
                return $cached;
            }
            self::debug_log(__FUNCTION__ . ': NO DB connection and no TRANSIENT → returning empty → key=' . $cache_key, 'error');
            self::$store_packages_cache = [];
            return [];
        }

        // SQL query
        $sql = "SELECT * FROM shop_packs";
        $start_time = microtime(true);
        $results = $cap_db->get_results($sql);
        $time = round((microtime(true) - $start_time) * 1000, 2);

        // SQL error
        if ($cap_db->last_error) {
            self::debug_log(__FUNCTION__ . ': SQL error: ' . addslashes($cap_db->last_error), 'error');
            if ($cached !== false) {
                set_transient($transient_key, $cached, 3600);
                self::$store_packages_cache = $cached;
                return $cached;
            }
            self::$store_packages_cache = [];
            return [];
        }

        // Save transient + STATIC cache
        self::write_database_json_cache(__FUNCTION__, $cache_key, $results, []);
        set_transient($transient_key, $results, 3600);
        self::$store_packages_cache = $results;
        self::debug_log(__FUNCTION__ . ': data from database DIRECTLY (SQL time ' . $time . 'ms) → key=' . $cache_key . ', host=' . $cap_db->dbhost . ' [' . gethostname() . '] and saved to TRANSIENT.');

        return $results;
    }

    /**
     * Get meta data from CAP databases
     */
    private static $meta_cache = [];
    public static function get_database_meta_data($data_id = null, $domain = null) {

        if ($domain !== null) {
            $domain = preg_replace('/:\d+$/', '', strtolower(trim($domain)));
        }

        // Different cache key for global and domain-specific queries.
        if ($domain !== null && $domain !== '') {
            $cache_key = $data_id . '|domain:' . $domain;
        } else {
            $cache_key = $data_id . '|global';
        }

        // STATIC cache
        if (!self::$database_cache_force_refresh && isset(self::$meta_cache[$cache_key])) {
            self::debug_log(__FUNCTION__ . ': data from STATIC → key=' . $cache_key);
            return self::$meta_cache[$cache_key];
        }

        // Transient
        $transient_key = 'pwe_meta_' . md5($cache_key);

        // Transient cache: preferred persistent cache before JSON
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$meta_cache[$cache_key] = $cached;
            self::debug_log(__FUNCTION__ . ': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient
        if (!self::$database_cache_force_refresh) {
            $file_cached = self::read_database_json_cache(__FUNCTION__, $cache_key);
            if ($file_cached['hit']) {
                $results = $file_cached['data'];
                set_transient($transient_key, $results, 3600);
                self::$meta_cache[$cache_key] = $results;
                self::debug_log(__FUNCTION__ . ': data from JSON FILE → key=' . $cache_key);
                return $results;
            }
        }


        // Log transient timeout
        $timeout = get_option('_transient_timeout_' . $transient_key);
        $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';

        // Connect DB
        $cap_db = self::connect_database();
        if (!$cap_db) {
            if ($cached !== false) {
                set_transient($transient_key, $cached, 3600);
                self::debug_log(__FUNCTION__ . ': NO DB connection → using last TRANSIENT and extending 65min → key=' . $cache_key, 'error');
                self::$meta_cache[$cache_key] = $cached;
                return $cached;
            }
            self::debug_log(__FUNCTION__ . ': NO DB connection and no TRANSIENT → returning empty → key=' . $cache_key, 'error');
            self::$meta_cache[$cache_key] = [];
            return [];
        }

        $start_time = microtime(true);

        // SQL query
        if ($data_id === null) {
            $results = $cap_db->get_results("SELECT * FROM meta_data");
        } elseif ($domain !== null) {
            $query = "
                SELECT m.meta_data
                FROM meta_data AS m
                INNER JOIN fairs AS f ON m.rights = f.id
                WHERE m.slug = %s
                AND f.fair_domain = %s
            ";
            $results = $cap_db->get_results(
                $cap_db->prepare($query, $data_id, $domain)
            );
        } else {
            $results = $cap_db->get_var(
                $cap_db->prepare("SELECT meta_data FROM meta_data WHERE slug = %s", $data_id)
            );
        }

        $time = round((microtime(true) - $start_time) * 1000, 2);

        // SQL error
        if ($cap_db->last_error) {
            self::debug_log(__FUNCTION__ . ': SQL error: ' . addslashes($cap_db->last_error), 'error');
            if ($cached !== false) {
                set_transient($transient_key, $cached, 3600);
                self::$meta_cache[$cache_key] = $cached;
                return $cached;
            }
            self::$meta_cache[$cache_key] = [];
            return [];
        }

        // Save transient + STATIC cache
        self::write_database_json_cache(__FUNCTION__, $cache_key, $results, [$data_id, $domain]);
        set_transient($transient_key, $results, 3600);
        self::$meta_cache[$cache_key] = $results;
        self::debug_log(__FUNCTION__ . ': data from database DIRECTLY (SQL time ' . $time . 'ms) → key=' . $cache_key . ', host=' . $cap_db->dbhost . ' [' . gethostname() . '] and saved to TRANSIENT.');

        return $results;
    }

    /**
     * Get group contacts data from CAP databases
     */
    private static $groups_contacts_cache = null;
    public static function get_database_groups_contacts_data(): array {

        $cache_key = 'groups_contacts';

        // STATIC cache
        if (!self::$database_cache_force_refresh && self::$groups_contacts_cache !== null) {
            self::debug_log(__FUNCTION__ . ': data from STATIC memory');
            return self::$groups_contacts_cache;
        }

        $transient_key = 'pwe_groups_contacts';

        // Transient cache: preferred persistent cache before JSON
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$groups_contacts_cache = $cached;
            self::debug_log(__FUNCTION__ . ': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient
        if (!self::$database_cache_force_refresh) {
            $file_cached = self::read_database_json_cache(__FUNCTION__, $cache_key);
            if ($file_cached['hit']) {
                $results = $file_cached['data'];
                set_transient($transient_key, $results, 3600);
                self::$groups_contacts_cache = $results;
                self::debug_log(__FUNCTION__ . ': data from JSON FILE → key=' . $cache_key);
                return $results;
            }
        }

        $timeout = get_option('_transient_timeout_' . $transient_key);
        $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';



        $cap_db = self::connect_database();
        if (!$cap_db) {
            if ($cached !== false) {
                set_transient($transient_key, $cached, 3600);
                self::debug_log(__FUNCTION__ . ': NO DB → using last TRANSIENT and extending 65min → key=' . $cache_key, 'error');
                self::$groups_contacts_cache = $cached;
                return $cached;
            }
            self::debug_log(__FUNCTION__ . ': NO DB and no TRANSIENT → returning empty → key=' . $cache_key, 'error');
            self::$groups_contacts_cache = [];
            return [];
        }

        $start_time = microtime(true);
        $results = $cap_db->get_results("SELECT * FROM groups");
        $time = round((microtime(true) - $start_time) * 1000, 2);

        if ($cap_db->last_error) {
            self::debug_log(__FUNCTION__ . ': SQL error: ' . addslashes($cap_db->last_error), 'error');
            if ($cached !== false) {
                set_transient($transient_key, $cached, 3600);
                self::$groups_contacts_cache = $cached;
                return $cached;
            }
            self::$groups_contacts_cache = [];
            return [];
        }

        self::write_database_json_cache(__FUNCTION__, $cache_key, $results, []);
        set_transient($transient_key, $results, 3600);
        self::$groups_contacts_cache = $results;
        self::debug_log(__FUNCTION__ . ': data from database DIRECTLY (SQL time ' . $time . 'ms) → key=' . $cache_key . ', host=' . $cap_db->dbhost . ' [' . gethostname() . '] and saved to TRANSIENT.');

        return $results;
    }

    /**
     * Get group callcenter data from CAP databases
     */
    private static $groups_callcenter_cache = null;
    public static function get_database_groups_callcenter_data(): array {

        $cache_key = 'groups_callcenter';

        if (!self::$database_cache_force_refresh && self::$groups_callcenter_cache !== null) {
            self::debug_log(__FUNCTION__ . ': data from STATIC memory');
            return self::$groups_callcenter_cache;
        }

        $transient_key = 'pwe_groups_callcenter';

        // Transient cache: preferred persistent cache before JSON
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$groups_callcenter_cache = $cached;
            self::debug_log(__FUNCTION__ . ': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient
        if (!self::$database_cache_force_refresh) {
            $file_cached = self::read_database_json_cache(__FUNCTION__, $cache_key);
            if ($file_cached['hit']) {
                $results = $file_cached['data'];
                set_transient($transient_key, $results, 3600);
                self::$groups_callcenter_cache = $results;
                self::debug_log(__FUNCTION__ . ': data from JSON FILE → key=' . $cache_key);
                return $results;
            }
        }

        $timeout = get_option('_transient_timeout_' . $transient_key);
        $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';



        $cap_db = self::connect_database();
        if (!$cap_db) {
            if ($cached !== false) {
                set_transient($transient_key, $cached, 3600);
                self::debug_log(__FUNCTION__ . ': NO DB → using last TRANSIENT and extending 65min → key=' . $cache_key, 'error');
                self::$groups_callcenter_cache = $cached;
                return $cached;
            }
            self::debug_log(__FUNCTION__ . ': NO DB and no TRANSIENT → returning empty → key=' . $cache_key, 'error');
            self::$groups_callcenter_cache = [];
            return [];
        }

        $start_time = microtime(true);
        $results = $cap_db->get_results("SELECT * FROM form_senders");
        $time = round((microtime(true) - $start_time) * 1000, 2);

        if ($cap_db->last_error) {
            self::debug_log(__FUNCTION__ . ': SQL error: ' . addslashes($cap_db->last_error), 'error');
            if ($cached !== false) {
                set_transient($transient_key, $cached, 3600);
                self::$groups_callcenter_cache = $cached;
                return $cached;
            }
            self::$groups_callcenter_cache = [];
            return [];
        }

        self::write_database_json_cache(__FUNCTION__, $cache_key, $results, []);
        set_transient($transient_key, $results, 3600);
        self::$groups_callcenter_cache = $results;
        self::debug_log(__FUNCTION__ . ': data from database DIRECTLY (SQL time ' . $time . 'ms) → key=' . $cache_key . ', host=' . $cap_db->dbhost . ' [' . gethostname() . '] and saved to TRANSIENT.');

        return $results;
    }

    /**
     * Get groups data from CAP databases
     */
    private static $groups_cache = null;
    public static function get_database_groups_data(): array {

        $cache_key = 'groups';

        if (!self::$database_cache_force_refresh && self::$groups_cache !== null) {
            self::debug_log(__FUNCTION__ . ': data from STATIC memory');
            return self::$groups_cache;
        }

        $transient_key = 'pwe_groups';

        // Transient cache: preferred persistent cache before JSON
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$groups_cache = $cached;
            self::debug_log(__FUNCTION__ . ': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient
        if (!self::$database_cache_force_refresh) {
            $file_cached = self::read_database_json_cache(__FUNCTION__, $cache_key);
            if ($file_cached['hit']) {
                $results = $file_cached['data'];
                set_transient($transient_key, $results, 3600);
                self::$groups_cache = $results;
                self::debug_log(__FUNCTION__ . ': data from JSON FILE → key=' . $cache_key);
                return $results;
            }
        }

        $timeout = get_option('_transient_timeout_' . $transient_key);
        $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';



        $cap_db = self::connect_database();
        if (!$cap_db) {
            if ($cached !== false) {
                set_transient($transient_key, $cached, 3600);
                self::debug_log(__FUNCTION__ . ': NO DB → using last TRANSIENT and extending 65min → key=' . $cache_key, 'error');
                self::$groups_cache = $cached;
                return $cached;
            }
            self::debug_log(__FUNCTION__ . ': NO DB and no TRANSIENT → returning empty → key=' . $cache_key, 'error');
            self::$groups_cache = [];
            return [];
        }

        $start_time = microtime(true);
        $results = $cap_db->get_results("SELECT fair_domain, fair_group FROM fairs");
        $time = round((microtime(true) - $start_time) * 1000, 2);

        if ($cap_db->last_error) {
            self::debug_log(__FUNCTION__ . ': SQL error: ' . addslashes($cap_db->last_error), 'error');
            if ($cached !== false) {
                set_transient($transient_key, $cached, 3600);
                self::$groups_cache = $cached;
                return $cached;
            }
            self::$groups_cache = [];
            return [];
        }

        self::write_database_json_cache(__FUNCTION__, $cache_key, $results, []);
        set_transient($transient_key, $results, 3600);
        self::$groups_cache = $results;
        self::debug_log(__FUNCTION__ . ': data from database DIRECTLY (SQL time ' . $time . 'ms) → key=' . $cache_key . ', host=' . $cap_db->dbhost . ' [' . gethostname() . '] and saved to TRANSIENT.');

        return $results;
    }

    /**
     * Get week data from CAP databases
     */
    private static $week_data_cache = [];
    public static function get_database_week_data($fair_domain = null): array {

        $current_domain = $fair_domain ?? $_SERVER['HTTP_HOST'];
        $cache_key = $current_domain;

        if (!self::$database_cache_force_refresh && isset(self::$week_data_cache[$cache_key])) {
            self::debug_log(__FUNCTION__ .': data from STATIC → key=' . $cache_key);
            return self::$week_data_cache[$cache_key];
        }

        $transient_key = 'pwe_week_data_' . md5($cache_key);

        // Transient cache: preferred persistent cache before JSON
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$week_data_cache[$cache_key] = $cached;
            self::debug_log(__FUNCTION__ . ': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient
        if (!self::$database_cache_force_refresh) {
            $file_cached = self::read_database_json_cache(__FUNCTION__, $cache_key);
            if ($file_cached['hit']) {
                $results = $file_cached['data'];
                set_transient($transient_key, $results, 3600);
                self::$week_data_cache[$cache_key] = $results;
                self::debug_log(__FUNCTION__ . ': data from JSON FILE → key=' . $cache_key);
                return $results;
            }
        }

        $timeout = get_option('_transient_timeout_' . $transient_key);
        $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';



        $cap_db = self::connect_database();
        if (!$cap_db) {
            if ($cached !== false) {
                set_transient($transient_key, $cached, 3600);
                self::debug_log(__FUNCTION__ .': NO DB → using last TRANSIENT and extending 65min → key=' . $cache_key, 'error');
                self::$week_data_cache[$cache_key] = $cached;
                return $cached;
            }
            self::debug_log(__FUNCTION__ .': NO DB and no TRANSIENT → returning empty → key=' . $cache_key, 'error');
            self::$week_data_cache[$cache_key] = [];
            return [];
        }

        $week = $cap_db->get_row(
            $cap_db->prepare("SELECT fairs_domains FROM fair_weeks WHERE week_domain = %s LIMIT 1", $current_domain)
        );

        if ($cap_db->last_error) {
            self::debug_log(__FUNCTION__ .': SQL error: ' . addslashes($cap_db->last_error), 'error');
            if ($cached !== false) {
                set_transient($transient_key, $cached, 3600);
                self::$week_data_cache[$cache_key] = $cached;
                return $cached;
            }
            self::$week_data_cache[$cache_key] = [];
            return [];
        }

        $start_time = microtime(true);
        $results = [];
        if ($week && !empty($week->fairs_domains)) {
            $results = array_map('trim', explode(',', $week->fairs_domains));
        }
        $time = round((microtime(true) - $start_time) * 1000, 2);

        self::write_database_json_cache(__FUNCTION__, $cache_key, $results, [$fair_domain]);
        set_transient($transient_key, $results, 3600);
        self::$week_data_cache[$cache_key] = $results;
        self::debug_log(__FUNCTION__ .': data from database DIRECTLY (SQL time ' . $time . 'ms) → key=' . $cache_key . ', host=' . $cap_db->dbhost . ' [' . gethostname() . '] and saved to TRANSIENT.');

        return $results;
    }

    /**
     * Get full week data from CAP databases
     */
    private static $week_all_cache = [];
    public static function get_database_week_all($fair_domain = null) {

        $current_domain = $fair_domain ?? $_SERVER['HTTP_HOST'];
        $cache_key = $current_domain;

        if (!self::$database_cache_force_refresh && isset(self::$week_all_cache[$cache_key])) {
            self::debug_log(__FUNCTION__ .': data from STATIC → key=' . $cache_key);
            return self::$week_all_cache[$cache_key];
        }

        $transient_key = 'pwe_week_all_' . md5($cache_key);

        // Transient cache: preferred persistent cache before JSON
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$week_all_cache[$cache_key] = $cached;
            self::debug_log(__FUNCTION__ .': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient
        if (!self::$database_cache_force_refresh) {
            $file_cached = self::read_database_json_cache(__FUNCTION__, $cache_key);
            if ($file_cached['hit']) {
                $results = $file_cached['data'];
                set_transient($transient_key, $results, 3600);
                self::$week_all_cache[$cache_key] = $results;
                self::debug_log(__FUNCTION__ . ': data from JSON FILE → key=' . $cache_key);
                return $results;
            }
        }

        $timeout = get_option('_transient_timeout_' . $transient_key);
        $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';



        $cap_db = self::connect_database();
        if (!$cap_db) {
            if ($cached !== false) {
                set_transient($transient_key, $cached, 3600);
                self::debug_log(__FUNCTION__ .': NO DB → using last TRANSIENT and extending 65min → key=' . $cache_key, 'error');
                self::$week_all_cache[$cache_key] = $cached;
                return $cached;
            }
            self::debug_log(__FUNCTION__ .': NO DB and no TRANSIENT → returning null → key=' . $cache_key, 'error');
            self::$week_all_cache[$cache_key] = null;
            return null;
        }

        $week = $cap_db->get_row(
            $cap_db->prepare("SELECT week_data FROM fair_weeks WHERE week_domain = %s LIMIT 1", $current_domain)
        );

        if ($cap_db->last_error) {
            self::debug_log(__FUNCTION__ .': SQL error: ' . addslashes($cap_db->last_error), 'error');
            if ($cached !== false) {
                set_transient($transient_key, $cached, 3600);
                self::$week_all_cache[$cache_key] = $cached;
                return $cached;
            }
            self::$week_all_cache[$cache_key] = null;
            return null;
        }

        $start_time = microtime(true);
        $results = null;
        if ($week && !empty($week->week_data)) {
            $decoded = json_decode($week->week_data, true);
            $results = (json_last_error() === JSON_ERROR_NONE) ? $decoded : $week->week_data;
        }
        $time = round((microtime(true) - $start_time) * 1000, 2);

        self::write_database_json_cache(__FUNCTION__, $cache_key, $results, [$fair_domain]);
        set_transient($transient_key, $results, 3600);
        self::$week_all_cache[$cache_key] = $results;
        self::debug_log(__FUNCTION__ .': data from database DIRECTLY (SQL time ' . $time . 'ms) → key=' . $cache_key . ', host=' . $cap_db->dbhost . ' [' . gethostname() . '] and saved to TRANSIENT.');

        return $results;
    }

    /**
     * Get all week domains from CAP databases
     */
    private static $all_week_domains_cache = null;
    public static function get_all_week_domains(): array {

        $cache_key = 'all_week_domains';

        if (!self::$database_cache_force_refresh && self::$all_week_domains_cache !== null) {
            self::debug_log(__FUNCTION__ .': data from STATIC memory');
            return self::$all_week_domains_cache;
        }

        $transient_key = 'pwe_all_week_domains';

        // Transient cache: preferred persistent cache before JSON
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$all_week_domains_cache = $cached;
            self::debug_log(__FUNCTION__ . ': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient
        if (!self::$database_cache_force_refresh) {
            $file_cached = self::read_database_json_cache(__FUNCTION__, $cache_key);
            if ($file_cached['hit']) {
                $results = $file_cached['data'];
                set_transient($transient_key, $results, 3600);
                self::$all_week_domains_cache = $results;
                self::debug_log(__FUNCTION__ . ': data from JSON FILE → key=' . $cache_key);
                return $results;
            }
        }

        $timeout = get_option('_transient_timeout_' . $transient_key);
        $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';



        $cap_db = self::connect_database();
        if (!$cap_db) {
            if ($cached !== false) {
                set_transient($transient_key, $cached, 3600);
                self::debug_log(__FUNCTION__ .': NO DB → using last TRANSIENT and extending 65min → key=' . $cache_key, 'error');
                self::$all_week_domains_cache = $cached;
                return $cached;
            }
            self::debug_log(__FUNCTION__ .': NO DB and no TRANSIENT → returning empty → key=' . $cache_key, 'error');
            self::$all_week_domains_cache = [];
            return [];
        }

        $rows = $cap_db->get_results("SELECT week_domain FROM fair_weeks");

        if ($cap_db->last_error) {
            self::debug_log(__FUNCTION__ .': SQL error: ' . addslashes($cap_db->last_error), 'error');
            if ($cached !== false) {
                set_transient($transient_key, $cached, 3600);
                self::$all_week_domains_cache = $cached;
                return $cached;
            }
            self::$all_week_domains_cache = [];
            return [];
        }

        $start_time = microtime(true);
        $domains = [];
        foreach ($rows as $row) {
            if (!empty($row->week_domain)) {
                $domains[] = trim($row->week_domain);
            }
        }
        $results = array_values(array_unique($domains));
        $time = round((microtime(true) - $start_time) * 1000, 2);

        self::write_database_json_cache(__FUNCTION__, $cache_key, $results, []);
        set_transient($transient_key, $results, 3600);
        self::$all_week_domains_cache = $results;
        self::debug_log(__FUNCTION__ .': data from database DIRECTLY (SQL time ' . $time . 'ms) → key=' . $cache_key . ', host=' . $cap_db->dbhost . ' [' . gethostname() . '] and saved to TRANSIENT.');

        return $results;
    }

    /**
     * Get logotypes data from CAP databases
     */
    private static $logotypes_cache = [];
    public static function get_database_logotypes_data($fair_domain = null): array {

        $current_domain = $fair_domain ?? $_SERVER['HTTP_HOST'];
        $cache_key = $current_domain;

        if (!self::$database_cache_force_refresh && isset(self::$logotypes_cache[$cache_key])) {
            self::debug_log(__FUNCTION__ .': data from STATIC → key=' . $cache_key);
            return self::$logotypes_cache[$cache_key];
        }

        $transient_key = 'pwe_logotypes_' . md5($cache_key);

        // Transient cache: preferred persistent cache before JSON
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$logotypes_cache[$cache_key] = $cached;
            self::debug_log(__FUNCTION__ . ': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient
        if (!self::$database_cache_force_refresh) {
            $file_cached = self::read_database_json_cache(__FUNCTION__, $cache_key);
            if ($file_cached['hit']) {
                $results = $file_cached['data'];
                set_transient($transient_key, $results, 3600);
                self::$logotypes_cache[$cache_key] = $results;
                self::debug_log(__FUNCTION__ . ': data from JSON FILE → key=' . $cache_key);
                return $results;
            }
        }

        $timeout = get_option('_transient_timeout_' . $transient_key);
        $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';



        $cap_db = self::connect_database();
        if (!$cap_db) {
            if ($cached !== false) {
                set_transient($transient_key, $cached, 3600);
                self::debug_log(__FUNCTION__ .': NO DB → using last TRANSIENT → key=' . $cache_key, 'error');
                self::$logotypes_cache[$cache_key] = $cached;
                return $cached;
            }
            self::debug_log(__FUNCTION__ .': NO DB and no TRANSIENT → returning empty → key=' . $cache_key, 'error');
            self::$logotypes_cache[$cache_key] = [];
            return [];
        }

        $start_time = microtime(true);
        $results = [];

        $week = $cap_db->get_row($cap_db->prepare(
            "SELECT fairs_domains FROM fair_weeks WHERE week_domain = %s LIMIT 1",
            $current_domain
        ));

        if ($week) {
            $domains = !empty($week->fairs_domains) ? json_decode($week->fairs_domains, true) : [];
            if (!is_array($domains)) {
                $domains = [];
            }

            $domains[] = $current_domain;

            $domains = array_values(array_unique(array_filter(array_map('trim', $domains))));

            $placeholders = implode(',', array_fill(0, count($domains), '%s'));
            $query = "
                SELECT DISTINCT logos.*, meta_data.meta_data AS meta_data
                FROM logos
                INNER JOIN fairs ON logos.fair_id = fairs.id
                LEFT JOIN meta_data ON meta_data.slug = 'patrons'
                    AND JSON_UNQUOTE(JSON_EXTRACT(meta_data.meta_data, '$.slug')) = logos.logos_type
                WHERE fairs.fair_domain IN ($placeholders)
            ";
            $results = $cap_db->get_results($cap_db->prepare($query, $domains));
        } else {
            $query = "
                SELECT logos.*, meta_data.meta_data AS meta_data
                FROM logos
                INNER JOIN fairs ON logos.fair_id = fairs.id
                LEFT JOIN meta_data ON meta_data.slug = 'patrons'
                    AND JSON_UNQUOTE(JSON_EXTRACT(meta_data.meta_data, '$.slug')) = logos.logos_type
                WHERE fairs.fair_domain = %s
            ";
            $results = $cap_db->get_results($cap_db->prepare($query, $current_domain));
        }

        if ($cap_db->last_error) {
            self::debug_log(__FUNCTION__ .': SQL error: ' . addslashes($cap_db->last_error), 'error');
            if ($cached !== false) {
                set_transient($transient_key, $cached, 3600);
                self::$logotypes_cache[$cache_key] = $cached;
                return $cached;
            }
            self::$logotypes_cache[$cache_key] = [];
            return [];
        }

        $results = self::remove_logo_duplicates($results);
        $time = round((microtime(true) - $start_time) * 1000, 2);

        self::write_database_json_cache(__FUNCTION__, $cache_key, $results, [$fair_domain]);
        set_transient($transient_key, $results, 3600);
        self::$logotypes_cache[$cache_key] = $results;
        self::debug_log(__FUNCTION__ .': data from database DIRECTLY (SQL time ' . $time . 'ms) → key=' . $cache_key . ', host=' . $cap_db->dbhost . ' [' . gethostname() . '] and saved to TRANSIENT.');

        return $results;
    }

    /**
     * Get conferences data from CAP databases
     */
    private static $conferences_cache = [];
    public static function get_database_conferences_data($domain = null): array {

        $domain = $domain ?? $_SERVER['HTTP_HOST'];
        $cache_key = $domain;

        if (!self::$database_cache_force_refresh && isset(self::$conferences_cache[$cache_key])) {
            self::debug_log(__FUNCTION__ . ': data from STATIC → key=' . $cache_key);
            return self::$conferences_cache[$cache_key];
        }

        $transient_key = 'pwe_conferences_' . md5($cache_key);

        // Transient cache: preferred persistent cache before JSON
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$conferences_cache[$cache_key] = $cached;
            self::debug_log(__FUNCTION__ .': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient
        if (!self::$database_cache_force_refresh) {
            $file_cached = self::read_database_json_cache(__FUNCTION__, $cache_key);
            if ($file_cached['hit']) {
                $results = $file_cached['data'];
                set_transient($transient_key, $results, 3600);
                self::$conferences_cache[$cache_key] = $results;
                self::debug_log(__FUNCTION__ . ': data from JSON FILE → key=' . $cache_key);
                return $results;
            }
        }

        $timeout = get_option('_transient_timeout_' . $transient_key);
        $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';



        $cap_db = self::connect_database();
        if (!$cap_db) {
            if ($cached !== false) {
                set_transient($transient_key, $cached, 3600);
                self::debug_log(__FUNCTION__ .': NO DB → using last TRANSIENT → key=' . $cache_key, 'error');
                self::$conferences_cache[$cache_key] = $cached;
                return $cached;
            }
            self::debug_log(__FUNCTION__ .': NO DB and no TRANSIENT → returning empty → key=' . $cache_key, 'error');
            self::$conferences_cache[$cache_key] = [];
            return [];
        }

        $start_time = microtime(true);
        $results = $cap_db->get_results(
            $cap_db->prepare(
                "SELECT * FROM conferences WHERE conf_site_link LIKE %s AND deleted_at IS NULL",
                '%' . $domain . '%'
            )
        );

        if ($cap_db->last_error) {
            self::debug_log(__FUNCTION__ .': SQL error: ' . addslashes($cap_db->last_error), 'error');
            if ($cached !== false) {
                set_transient($transient_key, $cached, 3600);
                self::$conferences_cache[$cache_key] = $cached;
                return $cached;
            }
            self::$conferences_cache[$cache_key] = [];
            return [];
        }

        foreach ($results as &$row) {
            if (!empty($row->conf_data)) {
                $decoded = html_entity_decode($row->conf_data);
                $decoded = preg_replace_callback('/style="([^"]+)"/is', function ($match) {
                    $style = $match[1];
                    $style = preg_replace('/font-family\s*:\s*[^;"]+("[^"]+"[, ]*)*[^;"]*;?/i', '', $style);
                    $style = trim(preg_replace('/\s*;\s*/', '; ', $style), '; ');
                    return $style ? 'style="' . $style . '"' : '';
                }, $decoded);
                if (json_decode($decoded, true) !== null) {
                    $row->conf_data = $decoded;
                } else {
                    error_log("Error JSON in conf_data: " . json_last_error_msg());
                }
            }
        }

        $time = round((microtime(true) - $start_time) * 1000, 2);
        self::write_database_json_cache(__FUNCTION__, $cache_key, $results, [$domain]);
        set_transient($transient_key, $results, 3600);
        self::$conferences_cache[$cache_key] = $results;
        self::debug_log(__FUNCTION__ .': data from database DIRECTLY (SQL time ' . $time . 'ms) → key=' . $cache_key . ', host=' . $cap_db->dbhost . ' [' . gethostname() . '] and saved to TRANSIENT.');

        return $results;
    }

    /**
     * Get conference additional data from CAP database.
     */
    private static $conference_adds_cache = [];
    public static function get_database_conference_adds_data($conf_id): array {

        $conf_id = (int) $conf_id;

        if ($conf_id <= 0) {
            return [];
        }

        $cache_key = (string) $conf_id;

        // STATIC
        if (!self::$database_cache_force_refresh && isset(self::$conference_adds_cache[$cache_key])
        ) {
            self::debug_log(__FUNCTION__ . ': data from STATIC → key=' . $cache_key);
            return self::$conference_adds_cache[$cache_key];
        }

        // TRANSIENT
        $transient_key = 'pwe_conference_adds_' . md5($cache_key);

        $cached = self::$database_cache_force_refresh
            ? false
            : get_transient($transient_key);

        if ($cached !== false) {

            $timeout = get_option('_transient_timeout_' . $transient_key);

            $time_left_str = ($timeout !== false)
                ? gmdate('H:i:s', max($timeout - time(), 0))
                : 'unknown';

            self::$conference_adds_cache[$cache_key] = $cached;
            self::debug_log(__FUNCTION__ .': data from TRANSIENT → key=' . $cache_key .', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON FILE
        if (!self::$database_cache_force_refresh) {

            $file_cached = self::read_database_json_cache(__FUNCTION__, $cache_key);

            if ($file_cached['hit']) {
                $results = $file_cached['data'];
                set_transient($transient_key, $results, 3600);
                self::$conference_adds_cache[$cache_key] = $results;

                self::debug_log(__FUNCTION__ .': data from JSON FILE → key=' . $cache_key);
                return $results;
            }
        }

        // DATABASE
        $cap_db = self::connect_database();

        if (!$cap_db) {
            self::debug_log(__FUNCTION__ .': NO DB connection → key=' . $cache_key, 'error');
            self::$conference_adds_cache[$cache_key] = [];
            return [];
        }

        $start_time = microtime(true);

        $results = $cap_db->get_results(
            $cap_db->prepare("SELECT slug, data FROM conf_adds WHERE conf_id = %d", $conf_id), ARRAY_A
        );

        $time = round(
            (microtime(true) - $start_time) * 1000,
            2
        );

        if ($cap_db->last_error) {
            self::debug_log(__FUNCTION__ .': SQL error: '. addslashes($cap_db->last_error), 'error');
            self::$conference_adds_cache[$cache_key] = [];
            return [];
        }

        // JSON
        self::write_database_json_cache(__FUNCTION__, $cache_key, $results, [$conf_id]);

        // TRANSIENT
        set_transient($transient_key, $results, 3600);

        // STATIC
        self::$conference_adds_cache[$cache_key] = $results;

        self::debug_log(__FUNCTION__ .': data from database DIRECTLY ' .'(SQL time ' . $time . 'ms) → key='. $cache_key .', host=' .$cap_db->dbhost .' [' . gethostname() .'] and saved to JSON + TRANSIENT.');

        return $results;
    }

    /**
     * Get fairs profiles data from CAP databases
     */
    private static $fairs_profiles_cache = [];
    public static function get_database_fairs_data_profiles($fair_domain = null): array {

        $fair_domain = $fair_domain ?? $_SERVER['HTTP_HOST'] ?? '';
        $cache_key = $fair_domain;

        if (!self::$database_cache_force_refresh && isset(self::$fairs_profiles_cache[$cache_key])) {
            self::debug_log(__FUNCTION__ . ': data from STATIC → key=' . $cache_key);
            return self::$fairs_profiles_cache[$cache_key];
        }

        $transient_key = 'pwe_fairs_profiles_' . md5($cache_key);

        // Transient cache: preferred persistent cache before JSON
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$fairs_profiles_cache[$cache_key] = $cached;
            self::debug_log(__FUNCTION__ . ': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient
        if (!self::$database_cache_force_refresh) {
            $file_cached = self::read_database_json_cache(__FUNCTION__, $cache_key);
            if ($file_cached['hit']) {
                $results = $file_cached['data'];
                set_transient($transient_key, $results, 3600);
                self::$fairs_profiles_cache[$cache_key] = $results;
                self::debug_log(__FUNCTION__ . ': data from JSON FILE → key=' . $cache_key);
                return $results;
            }
        }

        $timeout = get_option('_transient_timeout_' . $transient_key);
        $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';



        $cap_db = self::connect_database();
        if (!$cap_db) {
            self::debug_log(__FUNCTION__ . ': no database connection.', 'error');
            self::$fairs_profiles_cache[$cache_key] = [];
            return [];
        }

        $sql = "
            SELECT f.id, f.fair_domain, fp.data
            FROM fairs f
            LEFT JOIN fair_profiles fp ON fp.fair_id = f.id AND fp.slug = f.fair_domain
            WHERE f.fair_domain = %s
        ";

        $start_time = microtime(true);
        $results = $cap_db->get_results($cap_db->prepare($sql, $fair_domain));
        $time = round((microtime(true) - $start_time) * 1000, 2);

        if ($cap_db->last_error) {
            self::debug_log(__FUNCTION__ . ': SQL error: ' . addslashes($cap_db->last_error), 'error');
            $results = [];
        }

        self::write_database_json_cache(__FUNCTION__, $cache_key, $results, [$fair_domain]);
        set_transient($transient_key, $results, 3600);
        self::$fairs_profiles_cache[$cache_key] = $results;
        self::debug_log(__FUNCTION__ . ': data from database DIRECTLY (SQL time ' . $time . 'ms) → key=' . $cache_key . ' [' . gethostname() . '] and saved to TRANSIENT.');

        return $results;
    }

    /**
     * Get premieres data from CAP databases
     */
    private static $premieres_cache = [];
    public static function get_database_premieres_data($fair_domain = null): array {

        $cache_key = $fair_domain ?? 'all';

        if (!self::$database_cache_force_refresh && isset(self::$premieres_cache[$cache_key])) {
            self::debug_log(__FUNCTION__ . ': data from STATIC → key=' . $cache_key);
            return self::$premieres_cache[$cache_key];
        }

        $transient_key = 'pwe_premieres_' . md5($cache_key);

        // Transient cache: preferred persistent cache before JSON
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$premieres_cache[$cache_key] = $cached;
            self::debug_log(__FUNCTION__ . ': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient
        if (!self::$database_cache_force_refresh) {
            $file_cached = self::read_database_json_cache(__FUNCTION__, $cache_key);
            if ($file_cached['hit']) {
                $results = $file_cached['data'];
                set_transient($transient_key, $results, 3600);
                self::$premieres_cache[$cache_key] = $results;
                self::debug_log(__FUNCTION__ . ': data from JSON FILE → key=' . $cache_key);
                return $results;
            }
        }

        $timeout = get_option('_transient_timeout_' . $transient_key);
        $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';



        $cap_db = self::connect_database();
        if (!$cap_db) {
            self::debug_log(__FUNCTION__ . ': no database connection.', 'error');
            self::$premieres_cache[$cache_key] = [];
            return [];
        }

        $sql = "
            SELECT f.id, f.fair_domain, p.slug, p.data
            FROM fairs f
            LEFT JOIN fair_premieres p ON p.fair_id = f.id
        ";

        $params = [];
        if ($fair_domain !== null) {
            $sql .= " WHERE f.fair_domain = %s";
            $params[] = $fair_domain;
        }

        $start_time = microtime(true);
        $results = !empty($params)
            ? $cap_db->get_results($cap_db->prepare($sql, $params))
            : $cap_db->get_results($sql);

        $time = round((microtime(true) - $start_time) * 1000, 2);

        if ($cap_db->last_error) {
            self::debug_log(__FUNCTION__ . ': SQL error: ' . addslashes($cap_db->last_error), 'error');
            self::$premieres_cache[$cache_key] = [];
            return [];
        }

        self::write_database_json_cache(__FUNCTION__, $cache_key, $results, [$fair_domain]);
        set_transient($transient_key, $results, 3600);
        self::$premieres_cache[$cache_key] = $results;
        self::debug_log(__FUNCTION__ . ': data from database DIRECTLY (SQL time ' . $time . 'ms) → key=' . $cache_key . ' [' . gethostname() . '] and saved to TRANSIENT.');

        return $results;
    }

    /**
     * Get fairs opinions data from CAP databases
     */
    private static $fairs_opinions_cache = [];
    public static function get_database_fairs_data_opinions($fair_domain = null): array {

        $fair_domain = $fair_domain ?? $_SERVER['HTTP_HOST'] ?? '';
        $cache_key = $fair_domain;

        // Check runtime cache first
        if (!self::$database_cache_force_refresh && isset(self::$fairs_opinions_cache[$cache_key])) {
            self::debug_log(__FUNCTION__ . ': data from STATIC → key=' . $cache_key);
            return self::$fairs_opinions_cache[$cache_key];
        }

        // Transient key
        $transient_key = 'pwe_fairs_opinions_' . md5($cache_key);

        // Transient cache: preferred persistent cache before JSON
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$fairs_opinions_cache[$cache_key] = $cached;
            self::debug_log(__FUNCTION__ . ': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient
        if (!self::$database_cache_force_refresh) {
            $file_cached = self::read_database_json_cache(__FUNCTION__, $cache_key);
            if ($file_cached['hit']) {
                $results = $file_cached['data'];
                set_transient($transient_key, $results, 3600);
                self::$fairs_opinions_cache[$cache_key] = $results;
                self::debug_log(__FUNCTION__ . ': data from JSON FILE → key=' . $cache_key);
                return $results;
            }
        }

        // Connect to database
        $cap_db = self::connect_database();
        if (!$cap_db) {
            self::debug_log(__FUNCTION__ . ': no database connection.', 'error');
            self::$fairs_opinions_cache[$cache_key] = [];
            return [];
        }

        // SQL query
        $sql = "
            SELECT f.id, f.fair_domain, fp.data, fp.slug, fp.order
            FROM fairs f
            LEFT JOIN fair_opinions fp ON fp.fair_id = f.id
        ";
        $params = [];
        if ($fair_domain !== null) {
            $sql .= " WHERE f.fair_domain = %s";
            $params[] = $fair_domain;
        }
        $sql .= " ORDER BY fp.order ASC";

        $start_time = microtime(true);

        // Execute query
        $results = !empty($params) ? $cap_db->get_results($cap_db->prepare($sql, $params)) : $cap_db->get_results($sql);
        $time = round((microtime(true) - $start_time) * 1000, 2);

        // Handle SQL errors
        if ($cap_db->last_error) {
            self::debug_log(__FUNCTION__ . ': SQL error: ' . addslashes($cap_db->last_error), 'error');
            $results = [];
        }

        // Save to transient for 65 minutes
        self::write_database_json_cache(__FUNCTION__, $cache_key, $results, [$fair_domain]);
        set_transient($transient_key, $results, 3600);

        // Save to runtime cache
        self::$fairs_opinions_cache[$cache_key] = $results;
        self::debug_log(__FUNCTION__ . ': data from database DIRECTLY (SQL time ' . $time . 'ms) → key=' . $cache_key . ', host=' . $cap_db->dbhost . ' [' . gethostname() . '] and saved to TRANSIENT.');

        return $results;
    }

    /**
     * Get fairs sectors data from CAP databases
     */
    private static $fairs_sectors_cache = [];
    public static function get_database_fairs_data_sectors($fair_domain = null): array {

        $fair_domain = $fair_domain ?? $_SERVER['HTTP_HOST'] ?? '';
        $cache_key = $fair_domain;

        // Check runtime cache first
        if (!self::$database_cache_force_refresh && isset(self::$fairs_sectors_cache[$cache_key])) {
            self::debug_log(__FUNCTION__ . ': data from STATIC → key=' . $cache_key);
            return self::$fairs_sectors_cache[$cache_key];
        }

        // Transient key
        $transient_key = 'pwe_fairs_sectors_' . md5($cache_key);

        // Transient cache: preferred persistent cache before JSON
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$fairs_sectors_cache[$cache_key] = $cached;
            self::debug_log(__FUNCTION__ . ': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient
        if (!self::$database_cache_force_refresh) {
            $file_cached = self::read_database_json_cache(__FUNCTION__, $cache_key);
            if ($file_cached['hit']) {
                $results = $file_cached['data'];
                set_transient($transient_key, $results, 3600);
                self::$fairs_sectors_cache[$cache_key] = $results;
                self::debug_log(__FUNCTION__ . ': data from JSON FILE → key=' . $cache_key);
                return $results;
            }
        }

        // Connect to database
        $cap_db = self::connect_database();
        if (!$cap_db) {
            self::debug_log(__FUNCTION__ . ': no database connection.', 'error');
            self::$fairs_sectors_cache[$cache_key] = [];
            return [];
        }

        // SQL query
        $sql = "
            SELECT f.id, f.fair_domain, fp.slug, fp.data
            FROM fairs f
            LEFT JOIN fair_sectors fp ON fp.fair_id = f.id
        ";
        $params = [];
        if ($fair_domain !== null) {
            $sql .= " WHERE f.fair_domain = %s";
            $params[] = $fair_domain;
        }

        $start_time = microtime(true);

        // Execute query
        $results = !empty($params) ? $cap_db->get_results($cap_db->prepare($sql, $params)) : $cap_db->get_results($sql);
        $time = round((microtime(true) - $start_time) * 1000, 2);

        // Handle SQL errors
        if ($cap_db->last_error) {
            self::debug_log(__FUNCTION__ . ': SQL error: ' . addslashes($cap_db->last_error), 'error');
            $results = [];
        }

        // Save to transient for 65 minutes
        self::write_database_json_cache(__FUNCTION__, $cache_key, $results, [$fair_domain]);
        set_transient($transient_key, $results, 3600);

        // Save to runtime cache
        self::$fairs_sectors_cache[$cache_key] = $results;
        self::debug_log(__FUNCTION__ . ': data from database DIRECTLY (SQL time ' . $time . 'ms) → key=' . $cache_key . ', host=' . $cap_db->dbhost . ' [' . gethostname() . '] and saved to TRANSIENT.');

        return $results;
    }

    /**
     * Get fairs tickets data from CAP databases
     */
    private static $fairs_tickets_cache = [];
    public static function get_database_fairs_data_tickets($fair_domain = null): array {

        $fair_domain = $fair_domain ?? $_SERVER['HTTP_HOST'] ?? '';
        $cache_key = $fair_domain;

        // Check runtime cache first
        if (!self::$database_cache_force_refresh && isset(self::$fairs_tickets_cache[$cache_key])) {
            self::debug_log(__FUNCTION__ . ': data from STATIC → key=' . $cache_key);
            return self::$fairs_tickets_cache[$cache_key];
        }

        // Transient key
        $transient_key = 'pwe_fairs_tickets_' . md5($cache_key);

        // Transient cache: preferred persistent cache before JSON
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$fairs_tickets_cache[$cache_key] = $cached;
            self::debug_log(__FUNCTION__ . ': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient
        if (!self::$database_cache_force_refresh) {
            $file_cached = self::read_database_json_cache(__FUNCTION__, $cache_key);
            if ($file_cached['hit']) {
                $results = $file_cached['data'];
                set_transient($transient_key, $results, 3600);
                self::$fairs_tickets_cache[$cache_key] = $results;
                self::debug_log(__FUNCTION__ . ': data from JSON FILE → key=' . $cache_key);
                return $results;
            }
        }

        // Connect to database
        $cap_db = self::connect_database();
        if (!$cap_db) {
            self::debug_log(__FUNCTION__ . ': no database connection.', 'error');
            self::$fairs_tickets_cache[$cache_key] = [];
            return [];
        }

        // SQL query
        $sql = "
            SELECT f.id, f.fair_domain, fp.slug, fp.data
            FROM fairs f
            LEFT JOIN fair_tickets fp ON fp.fair_id = f.id
        ";
        $params = [];
        if ($fair_domain !== null) {
            $sql .= " WHERE f.fair_domain = %s";
            $params[] = $fair_domain;
        }

        $start_time = microtime(true);

        // Execute query
        $results = !empty($params) ? $cap_db->get_results($cap_db->prepare($sql, $params)) : $cap_db->get_results($sql);
        $time = round((microtime(true) - $start_time) * 1000, 2);

        // Handle SQL errors
        if ($cap_db->last_error) {
            self::debug_log(__FUNCTION__ . ': SQL error: ' . addslashes($cap_db->last_error), 'error');
            $results = [];
        }

        // Save to transient for 65 minutes
        self::write_database_json_cache(__FUNCTION__, $cache_key, $results, [$fair_domain]);
        set_transient($transient_key, $results, 3600);

        // Save to runtime cache
        self::$fairs_tickets_cache[$cache_key] = $results;
        self::debug_log(__FUNCTION__ . ': data from database DIRECTLY (SQL time ' . $time . 'ms) → key=' . $cache_key . ', host=' . $cap_db->dbhost . ' [' . gethostname() . '] and saved to TRANSIENT.');

        return $results;
    }

    /**
     * Get fairs speakers data from CAP databases
     */
    private static $fairs_speakers_cache = [];
    public static function get_database_fairs_data_speakers($fair_domain = null): array {

        $fair_domain = $fair_domain ?? $_SERVER['HTTP_HOST'] ?? '';
        $cache_key = $fair_domain;

        // Check runtime cache first
        if (!self::$database_cache_force_refresh && isset(self::$fairs_speakers_cache[$cache_key])) {
            self::debug_log(__FUNCTION__ . ': data from STATIC → key=' . $cache_key);
            return self::$fairs_speakers_cache[$cache_key];
        }

        // Transient key
        $transient_key = 'pwe_fairs_speakers_' . md5($cache_key);

        // Transient cache: preferred persistent cache before JSON
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$fairs_speakers_cache[$cache_key] = $cached;
            self::debug_log(__FUNCTION__ . ': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient
        if (!self::$database_cache_force_refresh) {
            $file_cached = self::read_database_json_cache(__FUNCTION__, $cache_key);
            if ($file_cached['hit']) {
                $results = $file_cached['data'];
                set_transient($transient_key, $results, 3600);
                self::$fairs_speakers_cache[$cache_key] = $results;
                self::debug_log(__FUNCTION__ . ': data from JSON FILE → key=' . $cache_key);
                return $results;
            }
        }

        // Connect to database
        $cap_db = self::connect_database();
        if (!$cap_db) {
            self::debug_log(__FUNCTION__ . ': no database connection.', 'error');
            self::$fairs_speakers_cache[$cache_key] = [];
            return [];
        }

        // SQL query
        $sql = "
            SELECT f.id, f.fair_domain, fp.slug, fp.name, fp.company, fp.position, fp.bio, fp.image, fp.logo, fp.order
            FROM fairs f
            LEFT JOIN fair_lectures fp ON fp.fair_id = f.id
        ";

        $params = [];

        if ($fair_domain !== null) {
            $sql .= " WHERE f.fair_domain = %s";
            $params[] = $fair_domain;
        }

        $sql .= " ORDER BY fp.order ASC";

        $start_time = microtime(true);

        // Execute query
        $results = !empty($params) ? $cap_db->get_results($cap_db->prepare($sql, $params)) : $cap_db->get_results($sql);

        $time = round((microtime(true) - $start_time) * 1000, 2);

        // Handle SQL errors
        if ($cap_db->last_error) {
            self::debug_log(__FUNCTION__ . ': SQL error: ' . addslashes($cap_db->last_error), 'error');
            $results = [];
        }

        // Save to transient for 65 minutes
        self::write_database_json_cache(__FUNCTION__, $cache_key, $results, [$fair_domain]);
        set_transient($transient_key, $results, 3600);

        // Save to runtime cache
        self::$fairs_speakers_cache[$cache_key] = $results;
        self::debug_log(__FUNCTION__ . ': data from database DIRECTLY (SQL time ' . $time . 'ms) → key=' . $cache_key . ', host=' . $cap_db->dbhost . ' [' . gethostname() . '] and saved to TRANSIENT.');

        return $results;
    }

    /**
     * Get fairs guests data from CAP databases
     */
    private static $fairs_guests_cache = [];
    public static function get_database_fairs_data_guests($fair_domain = null): array {

        $fair_domain = $fair_domain ?? $_SERVER['HTTP_HOST'] ?? '';
        $cache_key = $fair_domain;

        // Check runtime cache first
        if (!self::$database_cache_force_refresh && isset(self::$fairs_guests_cache[$cache_key])) {
            self::debug_log(__FUNCTION__ . ': data from STATIC → key=' . $cache_key);
            return self::$fairs_guests_cache[$cache_key];
        }

        // Transient key
        $transient_key = 'pwe_fairs_guests_' . md5($cache_key);

        // Transient cache: preferred persistent cache before JSON
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$fairs_guests_cache[$cache_key] = $cached;
            self::debug_log(__FUNCTION__ . ': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient
        if (!self::$database_cache_force_refresh) {
            $file_cached = self::read_database_json_cache(__FUNCTION__, $cache_key);
            if ($file_cached['hit']) {
                $results = $file_cached['data'];
                set_transient($transient_key, $results, 3600);
                self::$fairs_guests_cache[$cache_key] = $results;
                self::debug_log(__FUNCTION__ . ': data from JSON FILE → key=' . $cache_key);
                return $results;
            }
        }

        // Connect to database
        $cap_db = self::connect_database();
        if (!$cap_db) {
            self::debug_log(__FUNCTION__ . ': no database connection.', 'error');
            self::$fairs_guests_cache[$cache_key] = [];
            return [];
        }

        // SQL query
        $sql = "
            SELECT f.id, f.fair_domain, fp.type, fp.data
            FROM fairs f
            LEFT JOIN fair_guests fp ON fp.fair_id = f.id
        ";

        $params = [];

        if ($fair_domain !== null) {
            $sql .= " WHERE f.fair_domain = %s";
            $params[] = $fair_domain;
        }

        $start_time = microtime(true);

        // Execute query
        $results = !empty($params) ? $cap_db->get_results($cap_db->prepare($sql, $params)) : $cap_db->get_results($sql);

        $time = round((microtime(true) - $start_time) * 1000, 2);

        // Handle SQL errors
        if ($cap_db->last_error) {
            self::debug_log(__FUNCTION__ . ': SQL error: ' . addslashes($cap_db->last_error), 'error');
            $results = [];
        }

        // Save to transient for 65 minutes
        self::write_database_json_cache(__FUNCTION__, $cache_key, $results, [$fair_domain]);
        set_transient($transient_key, $results, 3600);

        // Save to runtime cache
        self::$fairs_guests_cache[$cache_key] = $results;
        self::debug_log(__FUNCTION__ . ': data from database DIRECTLY (SQL time ' . $time . 'ms) → key=' . $cache_key . ', host=' . $cap_db->dbhost . ' [' . gethostname() . '] and saved to TRANSIENT.');

        return $results;
    }

    /**
     * Get fairs attractions data from CAP databases
     */
    private static $fairs_attractions_cache = [];
    public static function get_database_fairs_data_attractions($fair_domain = null): array {

        $fair_domain = $fair_domain ?? $_SERVER['HTTP_HOST'] ?? '';
        $cache_key = $fair_domain;

        // Check runtime cache first
        if (!self::$database_cache_force_refresh && isset(self::$fairs_attractions_cache[$cache_key])) {
            self::debug_log(__FUNCTION__ . ': data from STATIC → key=' . $cache_key);
            return self::$fairs_attractions_cache[$cache_key];
        }

        // Transient key
        $transient_key = 'pwe_fairs_attractions_' . md5($cache_key);

        // Transient cache: preferred persistent cache before JSON
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$fairs_attractions_cache[$cache_key] = $cached;
            self::debug_log(__FUNCTION__ . ': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient
        if (!self::$database_cache_force_refresh) {
            $file_cached = self::read_database_json_cache(__FUNCTION__, $cache_key);
            if ($file_cached['hit']) {
                $results = $file_cached['data'];
                set_transient($transient_key, $results, 3600);
                self::$fairs_attractions_cache[$cache_key] = $results;
                self::debug_log(__FUNCTION__ . ': data from JSON FILE → key=' . $cache_key);
                return $results;
            }
        }

        // Connect to database
        $cap_db = self::connect_database();
        if (!$cap_db) {
            self::debug_log(__FUNCTION__ . ': no database connection.', 'error');
            self::$fairs_attractions_cache[$cache_key] = [];
            return [];
        }

        // SQL query
        $sql = "
            SELECT f.id, f.fair_domain, fp.type, fp.data
            FROM fairs f
            LEFT JOIN fair_attractions fp ON fp.fair_id = f.id
        ";

        $params = [];

        if ($fair_domain !== null) {
            $sql .= " WHERE f.fair_domain = %s";
            $params[] = $fair_domain;
        }

        $start_time = microtime(true);

        // Execute query
        $results = !empty($params) ? $cap_db->get_results($cap_db->prepare($sql, $params)) : $cap_db->get_results($sql);

        $time = round((microtime(true) - $start_time) * 1000, 2);

        // Handle SQL errors
        if ($cap_db->last_error) {
            self::debug_log(__FUNCTION__ . ': SQL error: ' . addslashes($cap_db->last_error), 'error');
            $results = [];
        }

        // Save to transient for 65 minutes
        self::write_database_json_cache(__FUNCTION__, $cache_key, $results, [$fair_domain]);
        set_transient($transient_key, $results, 3600);

        // Save to runtime cache
        self::$fairs_attractions_cache[$cache_key] = $results;
        self::debug_log(__FUNCTION__ . ': data from database DIRECTLY (SQL time ' . $time . 'ms) → key=' . $cache_key . ', host=' . $cap_db->dbhost . ' [' . gethostname() . '] and saved to TRANSIENT.');

        return $results;
    }

        /**
     * Get fairs files data from CAP databases
     */
    private static $fairs_files_cache = [];
    public static function get_database_fairs_data_files($fair_domain = null): array {

        $fair_domain = $fair_domain ?? $_SERVER['HTTP_HOST'] ?? '';
        $cache_key = $fair_domain;

        // Check runtime cache first
        if (!self::$database_cache_force_refresh && isset(self::$fairs_files_cache[$cache_key])) {
            self::debug_log(__FUNCTION__ . ': data from STATIC → key=' . $cache_key);
            return self::$fairs_files_cache[$cache_key];
        }

        // Transient key
        $transient_key = 'pwe_fairs_files_' . md5($cache_key);

        // Transient cache: preferred persistent cache before JSON
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$fairs_files_cache[$cache_key] = $cached;
            self::debug_log(__FUNCTION__ . ': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient
        if (!self::$database_cache_force_refresh) {
            $file_cached = self::read_database_json_cache(__FUNCTION__, $cache_key);
            if ($file_cached['hit']) {
                $results = $file_cached['data'];
                set_transient($transient_key, $results, 3600);
                self::$fairs_files_cache[$cache_key] = $results;
                self::debug_log(__FUNCTION__ . ': data from JSON FILE → key=' . $cache_key);
                return $results;
            }
        }

        // Connect to database
        $cap_db = self::connect_database();
        if (!$cap_db) {
            self::debug_log(__FUNCTION__ . ': no database connection.', 'error');
            self::$fairs_files_cache[$cache_key] = [];
            return [];
        }

        // SQL query
        $sql = "
            SELECT f.id, f.fair_domain, ff.fair_id, ff.category_slug, ff.category_name, ff.year, ff.language, ff.file_name, ff.file_path, ff.file_type, ff.gallery_files, ff.redirect_slug, ff.is_active
            FROM fairs f
            LEFT JOIN fair_files ff ON ff.fair_id = f.id
        ";

        $params = [];

        if ($fair_domain !== null) {
            $sql .= " WHERE f.fair_domain = %s";
            $params[] = $fair_domain;
        }

        $start_time = microtime(true);

        // Execute query
        $results = !empty($params) ? $cap_db->get_results($cap_db->prepare($sql, $params)) : $cap_db->get_results($sql);

        $time = round((microtime(true) - $start_time) * 1000, 2);

        // Handle SQL errors
        if ($cap_db->last_error) {
            self::debug_log(__FUNCTION__ . ': SQL error: ' . addslashes($cap_db->last_error), 'error');
            $results = [];
        }

        // Save to transient for 65 minutes
        self::write_database_json_cache(__FUNCTION__, $cache_key, $results, [$fair_domain]);
        set_transient($transient_key, $results, 3600);

        // Save to runtime cache
        self::$fairs_files_cache[$cache_key] = $results;
        self::debug_log(__FUNCTION__ . ': data from database DIRECTLY (SQL time ' . $time . 'ms) → key=' . $cache_key . ', host=' . $cap_db->dbhost . ' [' . gethostname() . '] and saved to TRANSIENT.');

        return $results;
    }

    /**
     * Get elements data from CAP databases
     */
    private static $elements_cache = null;
    public static function get_database_elements_data(): array {

        $cache_key = 'elements';

        // STATIC cache
        if (!self::$database_cache_force_refresh && self::$elements_cache !== null) {
            self::debug_log(__FUNCTION__ . ': data from STATIC memory');
            return self::$elements_cache;
        }

        // Transient
        $transient_key = 'pwe_elements_data';

        // Transient cache: preferred persistent cache before JSON
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$elements_cache = $cached;
            self::debug_log(__FUNCTION__ . ': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient
        if (!self::$database_cache_force_refresh) {
            $file_cached = self::read_database_json_cache(__FUNCTION__, $cache_key);
            if ($file_cached['hit']) {
                $results = $file_cached['data'];
                set_transient($transient_key, $results, 3600);
                self::$elements_cache = $results;
                self::debug_log(__FUNCTION__ . ': data from JSON FILE → key=' . $cache_key);
                return $results;
            }
        }


        // Log transient timeout
        $timeout = get_option('_transient_timeout_' . $transient_key);
        if ($timeout !== false) {
            $time_left = $timeout - time();
            $time_left_str = gmdate('H:i:s', max($time_left, 0));
        } else {
            $time_left_str = 'unknown';
        }



        // Connect DB
        $cap_db = self::connect_database();
        if (!$cap_db) {
            if ($cached !== false) {
                set_transient($transient_key, $cached, 3600);
                self::debug_log(__FUNCTION__ . ': NO DB connection → using last TRANSIENT and extending 65min → key=' . $cache_key, 'error');
                self::$elements_cache = $cached;
                return $cached;
            }
            self::debug_log(__FUNCTION__ . ': NO DB connection and no TRANSIENT → returning empty → key=' . $cache_key, 'error');
            self::$elements_cache = [];
            return [];
        }

        // SQL query
        $sql = "SELECT * FROM pwelements";
        $start_time = microtime(true);
        $results = $cap_db->get_results($sql);
        $time = round((microtime(true) - $start_time) * 1000, 2);

        // SQL error
        if ($cap_db->last_error) {
            self::debug_log(__FUNCTION__ . ': SQL error: ' . addslashes($cap_db->last_error), 'error');
            if ($cached !== false) {
                set_transient($transient_key, $cached, 3600);
                self::$elements_cache = $cached;
                return $cached;
            }
            self::$elements_cache = [];
            return [];
        }

        // Save transient + STATIC cache
        self::write_database_json_cache(__FUNCTION__, $cache_key, $results, []);
        set_transient($transient_key, $results, 3600);
        self::$elements_cache = $results;
        self::debug_log(__FUNCTION__ . ': data from database DIRECTLY (SQL time ' . $time . 'ms) → key=' . $cache_key . ', host=' . $cap_db->dbhost . ' [' . gethostname() . '] and saved to TRANSIENT.');

        return $results;
    }

    /**
     * Get elements order data from CAP databases
     */
    private static $elements_order_cache = null;
    public static function get_database_elements_order_data(): array {

        $cache_key = 'elements_order';

        // STATIC cache
        if (!self::$database_cache_force_refresh && self::$elements_order_cache !== null) {
            self::debug_log(__FUNCTION__ . ': data from STATIC memory');
            return self::$elements_order_cache;
        }

        // Transient
        $transient_key = 'pwe_elements_order_data';

        // Transient cache: preferred persistent cache before JSON
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$elements_order_cache = $cached;
            self::debug_log(__FUNCTION__ . ': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient
        if (!self::$database_cache_force_refresh) {
            $file_cached = self::read_database_json_cache(__FUNCTION__, $cache_key);
            if ($file_cached['hit']) {
                $results = $file_cached['data'];
                set_transient($transient_key, $results, 3600);
                self::$elements_order_cache = $results;
                self::debug_log(__FUNCTION__ . ': data from JSON FILE → key=' . $cache_key);
                return $results;
            }
        }


        // Log transient timeout
        $timeout = get_option('_transient_timeout_' . $transient_key);
        if ($timeout !== false) {
            $time_left = $timeout - time();
            $time_left_str = gmdate('H:i:s', max($time_left, 0));
        } else {
            $time_left_str = 'unknown';
        }



        // Connect DB
        $cap_db = self::connect_database();
        if (!$cap_db) {
            if ($cached !== false) {
                set_transient($transient_key, $cached, 3600);
                self::debug_log(__FUNCTION__ . ': NO DB connection → using last TRANSIENT and extending 65min → key=' . $cache_key, 'error');
                self::$elements_order_cache = $cached;
                return $cached;
            }
            self::debug_log(__FUNCTION__ . ': NO DB connection and no TRANSIENT → returning empty → key=' . $cache_key, 'error');
            self::$elements_order_cache = [];
            return [];
        }

        // SQL query
        $sql = "SELECT * FROM pwe_order";
        $start_time = microtime(true);
        $results = $cap_db->get_results($sql);
        $time = round((microtime(true) - $start_time) * 1000, 2);

        // SQL error
        if ($cap_db->last_error) {
            self::debug_log(__FUNCTION__ . ': SQL error: ' . addslashes($cap_db->last_error), 'error');
            if ($cached !== false) {
                set_transient($transient_key, $cached, 3600);
                self::$elements_order_cache = $cached;
                return $cached;
            }
            self::$elements_order_cache = [];
            return [];
        }

        // Save transient + STATIC cache
        self::write_database_json_cache(__FUNCTION__, $cache_key, $results, []);
        set_transient($transient_key, $results, 3600);
        self::$elements_order_cache = $results;
        self::debug_log(__FUNCTION__ . ': data from database DIRECTLY (SQL time ' . $time . 'ms) → key=' . $cache_key . ', host=' . $cap_db->dbhost . ' [' . gethostname() . '] and saved to TRANSIENT.');

        return $results;
    }

    // DATABASE CONNECTIONS END <==================================================================================>





    private static function remove_logo_duplicates(array $logos): array {
        $unique = [];
        $seen = [];

        foreach ($logos as $logo) {
            if (empty($logo->logos_url)) {
                $unique[] = $logo;
                continue;
            }

            if (preg_match('#/partners/([^/]+)/#', $logo->logos_url, $m)) {
                $partner_type = $m[1];
            } else {
                $partner_type = 'unknown';
            }

            $filename = basename($logo->logos_url);
            $key = $partner_type . '|' . $filename;

            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $unique[] = $logo;
            }
        }

        return $unique;
    }

    /**
     * Colors (accent or main2)
     */
    public static function pwe_color($color) {
        $fair_colors = self::findPalletColorsStatic();
        $result_color = null;

        // Handling color 'accent'
        if (strtolower($color) === 'accent' && isset($fair_colors['Accent'])) {
            $result_color = $fair_colors['Accent'];
        }

        // Handling color 'main2'
        if (strtolower($color) === 'main2') {
            foreach ($fair_colors as $color_key => $color_value) {
                if (strpos(strtolower($color_key), 'main2') !== false) {
                    $result_color = $color_value;
                    break;
                }
            }
        }

        return $result_color;
    }

    /**
     * Generate data for the fair
     */
    public static function generate_fair_data($fair) {
        // Decode JSON estimations
        $estimations = !empty($fair->estimations) ? json_decode($fair->estimations, true) : [];

        $data = [
            "domain" => $fair->fair_domain,
            "date_start" => $fair->fair_date_start ?? "",
            "date_start_hour" => $fair->fair_date_start_hour ?? "",
            "date_end" => $fair->fair_date_end ?? "",
            "date_end_hour" => $fair->fair_date_end_hour ?? "",
            "edition" => $fair->fair_edition ?? "",
            "name_pl" => $fair->fair_name_pl ?? "",
            "name_en" => $fair->fair_name_en ?? "",
            "desc_pl" => $fair->fair_desc_pl ?? "",
            "desc_en" => $fair->fair_desc_en ?? "",
            "id" => $fair->id ?? "",
            "short_desc_pl" => $fair->fair_short_desc_pl ?? "",
            "short_desc_en" => $fair->fair_short_desc_en ?? "",
            "full_desc_pl" => $fair->fair_full_desc_pl ?? "",
            "full_desc_en" => $fair->fair_full_desc_en ?? "",
            "visitors" => $fair->fair_visitors ?? "",
            "exhibitors" => $fair->fair_exhibitors ?? "",
            "countries" => $fair->fair_countries ?? "",
            "area" => $fair->fair_area ?? "",
            "color_accent" => $fair->fair_color_accent ?? "",
            "color_main2" => $fair->fair_color_main2 ?? "",
            "hall" => $fair->fair_hall ?? "",
            "hall_entrance" => $fair->fair_entrance ?? "",
            "facebook" => $fair->fair_facebook ?? "",
            "instagram" => $fair->fair_instagram ?? "",
            "linkedin" => $fair->fair_linkedin ?? "",
            "youtube" => $fair->fair_youtube ?? "",
            "badge" => $fair->fair_badge ?? "",
            "catalog" => $fair->fair_kw ?? "",
            "catalog_id" => $fair->fair_kw_new ?? "",
            "catalog_archive" => $fair->fair_kw_old_arch ?? "",
            "catalog_id_archive" => $fair->fair_kw_new_arch ?? "",
            "shop" => $fair->fair_shop ?? "",
            "group" => $fair->fair_group ?? "",
            "category_pl" => $fair->category_pl ?? "",
            "category_en" => $fair->category_en ?? "",
            "industry" => $fair->industry ?? "",
            "conference_name" => $fair->konf_name ?? "",
            "conference_title_pl" => $fair->konf_title_pl ?? "",
            "conference_title_en" => $fair->konf_title_en ?? "",
            'conference_desc_pl' => $fair->konf_desc_pl ?? '',
            'conference_desc_en' => $fair->konf_desc_en ?? '',
            'about_title_pl' => $fair->about_title_pl ?? '',
            'about_title_en' => $fair->about_title_en ?? '',
            'about_desc_pl' => $fair->about_desc_pl ?? '',
            'about_desc_en' => $fair->about_desc_en ?? ''
        ];

        // Add estimations to data
        if (!empty($estimations)) {
            foreach ($estimations as $key => $val) {
                $data[$key] = $val;
            }
        }

        return $data;
    }

    /**
     * Generate translations data for the fair
     */
    public static function generate_fair_translation_data($fair) {
        $languages = apply_filters('wpml_active_languages', null);

        // fallback if WPML doesn't work or doesn't exist
        if (empty($languages) || !is_array($languages)) {
            $fallback_codes = ['cs','de','it','lt','lv','ru','sk','uk'];

            $languages = [];

            foreach ($fallback_codes as $code) {
                $languages[] = ['language_code' => $code];
            }
        }

        $data = [
            "domain" => $fair["fair_domain"] ?? "",
        ];

        foreach ($languages as $lang) {
            $code = $lang['language_code'];

            $data["name_{$code}"] = $fair["fair_name_{$code}"] ?? "";
            $data["desc_{$code}"] = $fair["fair_desc_{$code}"] ?? "";
            $data["short_desc_{$code}"] = $fair["fair_short_desc_{$code}"] ?? "";
            $data["full_desc_{$code}"] = $fair["fair_full_desc_{$code}"] ?? "";
            $data["about_title_{$code}"] = $fair["about_title_{$code}"] ?? "";
            $data["about_desc_{$code}"] = $fair["about_desc_{$code}"] ?? "";
            $data["conference_title_{$code}"] = $fair["konf_title_{$code}"] ?? "";
            $data["conference_desc_{$code}"] = $fair["konf_desc_{$code}"] ?? "";
            $data["category_{$code}"] = $fair["category_{$code}"] ?? "";
        }

        return $data;
    }

    /**
     * JSON all trade fairs
     */
    public static function json_fairs() {
        static $runtime_cache = null;

        if ($runtime_cache !== null) {
            return $runtime_cache;
        }

        $pwe_fairs = self::get_database_fairs_data();
        $pwe_fairs_desc_translations = self::get_database_translations_data();

        static $console_logged = false;

        // Check if data is already in global variable
        if (!empty($pwe_fairs) && is_array($pwe_fairs)) {
            global $fairs_data;
            $fairs_data = ["fairs" => []];

            // Add data about the fair
            foreach ($pwe_fairs as $fair) {
                if (!isset($fair->fair_domain) || empty($fair->fair_domain)) {
                    continue;
                }

                $domain = $fair->fair_domain;

                // Save data about the fair in the table
                $fairs_data["fairs"][$domain] = self::generate_fair_data($fair);
            }

            // Add translations to the fair data
            foreach ($pwe_fairs_desc_translations as $translation) {
                if (!isset($translation['fair_domain']) || empty($translation['fair_domain'])) {
                    continue;
                }

                $domain = $translation['fair_domain'];

                $translation_data = self::generate_fair_translation_data($translation);

                // Merge data
                if (isset($fairs_data["fairs"][$domain])) {
                    $fairs_data["fairs"][$domain] = array_merge(
                        $fairs_data["fairs"][$domain],
                        $translation_data
                    );
                }
            }

            // $current_user = wp_get_current_user();
            // if ($current_user && $current_user->user_login === 'Anton') {
            //     var_dump($fairs_data);
            // }
        } else {
            // URL to JSON file
            $json_file = 'https://mr.glasstec.pl/doc/pwe-data.json';

            // Getting data from JSON file
            $json_data = @file_get_contents($json_file); // Use @ to ignore PHP warnings on failure

            // Checking if data has been downloaded
            if ($json_data === false) {
                if (current_user_can("administrator") && !is_admin()) {
                    echo '<script>console.error("Failed to fetch data from JSON file: ' . $json_file . '")</script>';
                }
                return null;
            }

            global $fairs_data;
            // Decoding JSON data
            $fairs_data = json_decode($json_data, true);

            // Checking JSON decoding correctness
            if (json_last_error() !== JSON_ERROR_NONE) {
                if (current_user_can("administrator") && !is_admin()) {
                    echo '<script>console.error("Error decoding JSON: ' . json_last_error_msg() . '")</script>';
                }
                return null;
            }

            // Checking if the data has the correct structure
            if (!isset($fairs_data['fairs']) || !is_array($fairs_data['fairs'])) {
                if (current_user_can("administrator") && !is_admin()) {
                    echo '<script>console.error("Invalid fairs data format in JSON file.")</script>';
                }
                return null;
            }

            if (!$console_logged) {
                if (current_user_can("administrator") && !is_admin()) {
                    echo '<script>console.error("Brak danych o targach w globalnej zmiennej (dane CAP DB), dane są pobrane z pwe-data.json")</script>';
                }
                $console_logged = true;
            }
        }

        $runtime_cache = $fairs_data['fairs'];
        return $runtime_cache;
    }

    /**
     * Function to transform the date
     */
    public static function transform_dates($start_date, $end_date, $include_hours = true) {
        $format = $include_hours ? "Y/m/d H:i" : "Y/m/d";

        // Convert date strings to DateTime objects
        $start_date_obj = DateTime::createFromFormat($format, $start_date);
        $end_date_obj = DateTime::createFromFormat($format, $end_date);

        // Check if the conversion was correct
        if ($start_date_obj && $end_date_obj) {
            // Get the day, month and year from DateTime objects
            $start_day = $start_date_obj->format("d");
            $end_day = $end_date_obj->format("d");
            $start_month = $start_date_obj->format("m");
            $end_month = $end_date_obj->format("m");
            $year = $start_date_obj->format("Y");

            // Check if months are the same
            if ($start_month === $end_month) {
                $formatted_date = "{$start_day}-{$end_day}|{$start_month}|{$year}";
            } else {
                $formatted_date = "{$start_day}|{$start_month}-{$end_day}|{$end_month}|{$year}";
            }

            return $formatted_date;
        } else {
            return "Invalid dates";
        }
    }


    /**
     * Decoding Base64
     * Decoding URL
     * Remowe wpautop
     */
    public static function decode_clean_content($encoded_content) {
        $decoded_content = wpb_js_remove_wpautop(urldecode(base64_decode($encoded_content)), true);
        return $decoded_content;
    }

    /**
     * Decodes URL-encoded string
     * Decodes a JSON string
     */
    public static function json_decode($encoded_variable) {
        $encoded_variable_urldecode = urldecode($encoded_variable);
        $encoded_variable_json = json_decode($encoded_variable_urldecode, true);
        return $encoded_variable_json;
    }

    /**
     * Adding colors
     */
    public static function findColor($primary, $secondary, $default = '') {
        if ($primary != '') {
            return $primary;
        } elseif ($secondary != '') {
            return $secondary;
        } else {
            return $default;
        }
    }

    /**
     * Finding preset colors pallet.
     *
     * @return array
     */
    public static function findPalletColorsStatic() {
        $uncode_options = get_option('uncode');
        $accent_uncode_color = $uncode_options["_uncode_accent_color"];
        $custom_element_colors = array();

        if (isset($uncode_options["_uncode_custom_colors_list"]) && is_array($uncode_options["_uncode_custom_colors_list"])) {
            $custom_colors_list = $uncode_options["_uncode_custom_colors_list"];

            foreach ($custom_colors_list as $color) {
                $title = $color['title'];
                $color_value = $color["_uncode_custom_color"];
                $color_id = $color["_uncode_custom_color_unique_id"];

                if ($accent_uncode_color != $color_id) {
                    $custom_element_colors[$title] = $color_value;
                } else {
                    $accent_color_value = $color_value;
                    $custom_element_colors = array_merge(array('Accent' => $accent_color_value), $custom_element_colors);
                }
            }
            $custom_element_colors = array_merge(array('Default' => ''), $custom_element_colors);
        }
        return $custom_element_colors;
    }

    /**
     * Finding preset colors pallet.
     *
     * @return array
     */
    public function findPalletColors() {
        $uncode_options = get_option('uncode');
        $accent_uncode_color = $uncode_options["_uncode_accent_color"];
        $custom_element_colors = array();

        if (isset($uncode_options["_uncode_custom_colors_list"]) && is_array($uncode_options["_uncode_custom_colors_list"])) {
            $custom_colors_list = $uncode_options["_uncode_custom_colors_list"];

            foreach ($custom_colors_list as $color) {
                $title = $color['title'];
                $color_value = $color["_uncode_custom_color"];
                $color_id = $color["_uncode_custom_color_unique_id"];

                if ($accent_uncode_color != $color_id) {
                    $custom_element_colors[$title] = $color_value;
                } else {
                    $accent_color_value = $color_value;
                    $custom_element_colors = array_merge(array('Accent' => $accent_color_value), $custom_element_colors);
                }
            }
            $custom_element_colors = array_merge(array('Default' => ''), $custom_element_colors);
        }
        return $custom_element_colors;
    }

    /**
     * Checking if the location is PL
     *
     * @return bool
     */
    public static function lang_pl() {
        return get_locale() == 'pl_PL';
    }

    /**
     * Laguage check for text
     *
     * @param string $pl text in Polish.
     * @param string $pl text in English.
     * @return string
     */
    public static function languageChecker($pl, $en = '', $de = '') {
        if (get_locale() == 'pl_PL') {
            return $pl;
        } else if (get_locale() == 'de_DR') {
            return $de;
        } else {
            return $en;
        }
    }

    /**
     * Function to change color brightness (taking color in hex format)
     *
     * @return array
     */
    public static function adjustBrightness($hex, $steps) {
        // Convert hex to RGB
        $hex = str_replace('#', '', $hex);
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        // Shift RGB values
        $r = max(0, min(255, $r + $steps));
        $g = max(0, min(255, $g + $steps));
        $b = max(0, min(255, $b + $steps));

        // Convert RGB back to hex
        return '#' . str_pad(dechex($r), 2, '0', STR_PAD_LEFT)
            . str_pad(dechex($g), 2, '0', STR_PAD_LEFT)
            . str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
    }

    /**
     * Finding all GF forms
     *
     * @return array
     */
    public function findFormsGF($mode = '') {
        $pwe_forms_array = array();
        if (is_admin()) {
            if (method_exists('GFAPI', 'get_forms')) {
                $pwe_forms = GFAPI::get_forms();
                if ($mode == 'id') {
                    foreach ($pwe_forms as $form) {
                        $pwe_forms_array[$form['title']] = $form['id'];
                    }
                } else {
                    foreach ($pwe_forms as $form) {
                        $pwe_forms_array[$form['id']] = $form['title'];
                    }
                }
            }
        }
        return $pwe_forms_array;
    }

    /**
     * Finding all target form id
     *
     * @param string $form_name
     * @return string
     */
    public static function findFormsID($form_name) {
        $pwe_form_id = '';
        if (method_exists('GFAPI', 'get_forms')) {
            $pwe_forms = GFAPI::get_forms();
            foreach ($pwe_forms as $form) {
                if ($form['title'] === $form_name) {
                    $pwe_form_id = $form['id'];
                    break;
                }
            }
        }
        return $pwe_form_id;
    }

    /**
     * Mobile displayer check
     *
     * @return bool
     */
    public static function checkForMobile() {
        return (preg_match('/Mobile|Android|iPhone/i', $_SERVER['HTTP_USER_AGENT']));
    }

    /**
     * Laguage check for text
     *
     * @param bool $logo_color schould logo be in color.
     * @return string
     */
    public static function findBestLogo($logo_color = false) {
        $filePaths = array(
            '/doc/logo-color-en.webp',
            '/doc/logo-color-en.png',
            '/doc/logo-color.webp',
            '/doc/logo-color.png',
            '/doc/logo-en.webp',
            '/doc/logo-en.png',
            '/doc/logo.webp',
            '/doc/logo.png'
        );

        switch (true) {
            case (get_locale() == 'pl_PL'):
                if ($logo_color) {
                    foreach ($filePaths as $path) {
                        if (strpos($path, '-en.') === false && file_exists(ABSPATH . $path)) {
                            return '<img src="' . $path . '"/>';
                        }
                    }
                } else {
                    foreach ($filePaths as $path) {
                        if (strpos($path, 'color') === false && strpos($path, '-en.') === false && file_exists(ABSPATH . $path)) {
                            return '<img src="' . $path . '"/>';
                        }
                    }
                }
                break;

            case (get_locale() == 'en_US'):
                if ($logo_color) {
                    foreach ($filePaths as $path) {
                        if (file_exists(ABSPATH . $path)) {
                            return '<img src="' . $path . '"/>';
                        }
                    }
                } else {
                    foreach ($filePaths as $path) {
                        if (strpos($path, 'color') === false && file_exists(ABSPATH . $path)) {
                            return '<img src="' . $path . '"/>';
                        }
                    }
                }
                break;
        }
    }

    /**
     * Finding URL of all images based on katalog
     */
    public static function findAllImages($firstPath, $image_count = false, $secondPath = '/doc/galeria') {
        $firstPath = $_SERVER['DOCUMENT_ROOT'] . $firstPath;

        if (is_dir($firstPath) && !empty(glob($firstPath . '/*.{jpeg,jpg,png,webp,svg,JPEG,JPG,PNG,WEBP}', GLOB_BRACE))) {
            $exhibitorsImages = glob($firstPath . '/*.{jpeg,jpg,png,webp,svg,JPEG,JPG,PNG,WEBP}', GLOB_BRACE);
        } else {
            $secondPath = $_SERVER['DOCUMENT_ROOT'] . $secondPath;
            $exhibitorsImages = glob($secondPath . '/*.{jpeg,jpg,png,webp,svg,JPEG,JPG,PNG,WEBP}', GLOB_BRACE);
        }
        $count = 0;
        foreach ($exhibitorsImages as $image) {
            if ($image_count != false && $count >= $image_count) {
                break;
            } else {
                $exhibitors_path[] = substr($image, strpos($image, '/doc/'));
                $count++;
            }
        }

        return $exhibitors_path;
    }

    /**
     * Laguage check for text
     *
     * @param bool $logo_color schould logo be in color.
     * @return string
     */
    public static function findBestFile($file_path) {
        $filePaths = array(
            '.webp',
            '.jpg',
            '.png'
        );

        foreach ($filePaths as $com) {
            if (file_exists(ABSPATH . $file_path . $com)) {
                return $file_path . $com;
            }
        }
    }

    /**
     * Trade fair date existance check
     *
     * @return bool
     */
    public static function isTradeDateExist() {

        $seasons = ["nowa data", "wiosna", "lato", "jesień", "zima", "new date", "spring", "summer", "autumn", "winter"];
        $trade_date_lower = strtolower(do_shortcode('[trade_fair_date]'));

        // Przeszukiwanie tablicy w poszukiwaniu pasującego sezonu
        foreach ($seasons as $season) {
            if (strpos($trade_date_lower, strtolower($season)) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Adding element input[type="range"]
     */
    public static function inputRange() {
        if (function_exists('vc_add_shortcode_param')) {
            vc_add_shortcode_param('input_range', array('PWEHeader', 'input_range_field_html'));
        }
    }
    public static function input_range_field_html($settings, $value) {
        $id = uniqid('range_');
        return '<div class="pwe-input-range">'
            . '<input type="range" '
            . 'id="' . esc_attr($id) . '" '
            . 'name="' . esc_attr($settings['param_name']) . '" '
            . 'class="wpb_vc_param_value ' . esc_attr($settings['param_name']) . ' ' . esc_attr($settings['type']) . '_field" '
            . 'value="' . esc_attr($value) . '" '
            . 'min="' . esc_attr($settings['min']) . '" '
            . 'max="' . esc_attr($settings['max']) . '" '
            . 'step="' . esc_attr($settings['step']) . '" '
            . 'oninput="document.getElementById(\'value_' . esc_attr($id) . '\').innerHTML = this.value" '
            . '/>'
            . '<span id="value_' . esc_attr($id) . '">' . esc_attr($value) . '</span>'
            . '</div>';
    }
    /**
     * Gravity Forms SMTP failure monitor.
     *
     */
    public static function gravity_forms_smtp_monitor(
        $is_success = null,
        $to = '',
        $subject = '',
        $message = '',
        $headers = [],
        $attachments = [],
        $message_format = '',
        $from = '',
        $from_name = '',
        $bcc = '',
        $reply_to = '',
        $entry = false
    ) {
        $alert_recipients = [
            'jakub.chola@warsawexpo.eu',
            's.skrypnychenko@warsawexpo.eu',
            'jakub.koscielak@warsawexpo.eu',
            'jakub.goral@warsawexpo.eu',
        ];

        $smtp_host       = 'dedyk180.cyber-folks.pl';
        $smtp_port       = 465;
        $smtp_encryption = 'ssl';
        $smtp_username   = 'smtp@mr.glasstec.pl';
        $smtp_password   = defined('PWE_API_KEY_2') ? PWE_API_KEY_2 : '';

        $max_alerts_per_hour = 100;

        // --- OBSŁUGA SUKCESU (RECOVERY) ---
        if ($is_success === true) {
            $incident_active = get_transient('pwe_gf_smtp_incident_active');

            if ($incident_active) {
                $failure_count = (int) get_transient('pwe_gf_smtp_failure_count');
                $recovery_sent = get_transient('pwe_gf_smtp_recovery_sent');

                if (!$recovery_sent) {
                    $entry_id = (is_array($entry) && !empty($entry['id'])) ? $entry['id'] : '';
                    $form_id  = (is_array($entry) && !empty($entry['form_id'])) ? $entry['form_id'] : '';
                    $time     = current_time('Y-m-d H:i:s');

                    $recovery_subject = '✅ Gravity Forms SMTP RECOVERY - ' . $smtp_host;

                    $recovery_body  = "Gravity Forms SMTP ponownie działa.\n\n";
                    $recovery_body .= "STATUS: RECOVERY\nData: {$time}\n\n";
                    $recovery_body .= "Serwer backup SMTP:\n{$smtp_host}:{$smtp_port}\nEncryption: {$smtp_encryption}\n\n";
                    $recovery_body .= "Kolejny poprawnie wysłany email:\nDo: {$to}\nTemat: {$subject}\nForm ID: {$form_id}\nEntry ID: {$entry_id}\n\n";
                    $recovery_body .= "Liczba wykrytych błędów przed recovery: {$failure_count}\n\n";
                    $recovery_body .= "Gravity Forms ponownie poprawnie przetworzył wysyłkę wiadomości.";

                    $phpmailer_instance = null;
                    $phpmailer_config   = static function ($phpmailer) use (
                        &$phpmailer_instance,
                        $smtp_host,
                        $smtp_port,
                        $smtp_username,
                        $smtp_password,
                        $smtp_encryption
                    ) {
                        $phpmailer_instance = $phpmailer;
                        $phpmailer->isSMTP();

                        $phpmailer->Host     = $smtp_host;
                        $phpmailer->Port     = $smtp_port;
                        $phpmailer->SMTPAuth = true;
                        $phpmailer->Username = $smtp_username;
                        $phpmailer->Password = $smtp_password;

                        if ($smtp_encryption === 'ssl') {
                            $phpmailer->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
                        } elseif ($smtp_encryption === 'tls') {
                            $phpmailer->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                        }

                        $phpmailer->From     = $smtp_username;
                        $phpmailer->FromName = 'WordPress SMTP Monitor';
                        $phpmailer->Timeout  = 15;
                    };

                    add_action('phpmailer_init', $phpmailer_config, 999);
                    $result = wp_mail($alert_recipients, $recovery_subject, $recovery_body);
                    remove_action('phpmailer_init', $phpmailer_config, 999);

                    if ($result) {
                        set_transient('pwe_gf_smtp_recovery_sent', 1, DAY_IN_SECONDS);
                    }
                }

                delete_transient('pwe_gf_smtp_incident_active');
                delete_transient('pwe_gf_smtp_failure_count');
                delete_transient('pwe_gf_smtp_recovery_sent');

                return;
            }

            return;
        }

        // --- OBSŁUGA BŁĘDU (FAILURE) ---
        if ($is_success !== false) {
            return;
        }

        $entry_id = (is_array($entry) && !empty($entry['id'])) ? $entry['id'] : '';
        $form_id  = (is_array($entry) && !empty($entry['form_id'])) ? $entry['form_id'] : '';

        $form_title = '';
        if ($form_id && class_exists('GFAPI')) {
            $form = \GFAPI::get_form($form_id);
            if (is_array($form) && !empty($form['title'])) {
                $form_title = $form['title'];
            }
        }

        $time = current_time('Y-m-d H:i:s');

        $failure_count = (int) get_transient('pwe_gf_smtp_failure_count');
        $failure_count++;

        set_transient('pwe_gf_smtp_failure_count', $failure_count, DAY_IN_SECONDS);
        set_transient('pwe_gf_smtp_incident_active', 1, DAY_IN_SECONDS);

        $alert_count = (int) get_transient('pwe_gf_smtp_alert_count');
        if ($alert_count >= $max_alerts_per_hour) {
            return;
        }

        $alert_subject = '🚨 Gravity Forms SMTP FAILURE - ' . $smtp_host;

        $alert_body  = "Gravity Forms wykrył problem z wysyłką emaila.\n\n";
        $alert_body .= "STATUS: SMTP FAILURE\nData: {$time}\n\n";
        $alert_body .= "FORMULARZ:\nNazwa: {$form_title}\nForm ID: {$form_id}\nEntry ID: {$entry_id}\n\n";
        $alert_body .= "EMAIL:\nDo: {$to}\nTemat: {$subject}\n\n";
        $alert_body .= "LICZNIKI:\nBłąd nr: {$failure_count}\nAlert nr w tej godzinie: " . ($alert_count + 1) . " / {$max_alerts_per_hour}\n\n";
        $alert_body .= "BACKUP SMTP:\nHost: {$smtp_host}\nPort: {$smtp_port}\nEncryption: {$smtp_encryption}\nUsername: {$smtp_username}\n\n";
        $alert_body .= "Gravity Forms zwrócił is_success = FALSE.\nMonitor wysłał alert przez backup SMTP.";

        if (empty($smtp_password)) {
            return;
        }

        $phpmailer_instance = null;
        $phpmailer_config   = static function ($phpmailer) use (
            &$phpmailer_instance,
            $smtp_host,
            $smtp_port,
            $smtp_username,
            $smtp_password,
            $smtp_encryption
        ) {
            $phpmailer_instance = $phpmailer;
            $phpmailer->isSMTP();

            $phpmailer->Host     = $smtp_host;
            $phpmailer->Port     = $smtp_port;
            $phpmailer->SMTPAuth = true;
            $phpmailer->Username = $smtp_username;
            $phpmailer->Password = $smtp_password;

            if ($smtp_encryption === 'ssl') {
                $phpmailer->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($smtp_encryption === 'tls') {
                $phpmailer->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            }

            $phpmailer->From     = $smtp_username;
            $phpmailer->FromName = 'WordPress SMTP Monitor';
            $phpmailer->Timeout  = 15;
        };

        add_action('phpmailer_init', $phpmailer_config, 999);
        $result = wp_mail($alert_recipients, $alert_subject, $alert_body);
        remove_action('phpmailer_init', $phpmailer_config, 999);

        if ($result) {
            $alert_count++;
            set_transient('pwe_gf_smtp_alert_count', $alert_count, HOUR_IN_SECONDS);
        }
    }
}

add_action('wp_footer', ['PWE_System_Functions', 'output_db_connection_logs']);


// Backward compatibility for existing PWE Elements code.
// The implementation is PWE_System_Functions; PWE_Functions is only an alias.
if (!class_exists('PWE_Functions', false)) {
    class_alias('PWE_System_Functions', 'PWE_Functions');
}
