<?php

/**
 * All CAP database getters and their runtime caches.
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Compose all CAP database getters and their runtime caches in PWE_System_Functions.
 *
 * @internal Composed by core/class-pwe-system-functions.php.
 */
trait PWE_System_Functions_Database_Data {
    /**
     * Runtime cache for CAP fairs records.
     */
    private static $fairs_cache = [];

    /**
     * Fetch fair records from CAP using the requested domain or date window.
     *
     * Use "warsawexpo.eu" or "all" to return all fairs without a domain filter.
     * An explicit domain selects that domain; no argument uses the existing +/-17-day window.
     */
    public static function get_database_fairs_data($fair_domain = null): array {

        // Detect current domain.
        $current_domain = $_SERVER['HTTP_HOST'] ?? '';

        // Cache key.
        if ($current_domain === 'warsawexpo.eu' || $fair_domain === 'all') {
            $cache_key = 'all_fairs';
        } elseif ($fair_domain !== null) {
            $cache_key = $fair_domain;
        } else {
            $cache_key = 'month';
        }

        // Static cache.
        if (!self::$database_cache_force_refresh && isset(self::$fairs_cache[$cache_key])) {
            self::debug_log(__FUNCTION__ . ': data from STATIC → key=' . $cache_key);
            return self::$fairs_cache[$cache_key];
        }

        // Transient cache.
        $transient_key = 'pwe_fairs_' . md5($cache_key);

        // Transient cache: preferred persistent cache before JSON.
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$fairs_cache[$cache_key] = $cached;
            self::debug_log(__FUNCTION__ . ': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient.
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


        // Log timeout if transient exists.
        $timeout = get_option('_transient_timeout_' . $transient_key);
        if ($timeout !== false) {
            $time_left = $timeout - time();
            $time_left_str = gmdate('H:i:s', max($time_left, 0));
        } else {
            $time_left_str = 'unknown';
        }



        // Connect database.
        $cap_db = self::connect_database();

        if (!$cap_db) {
            // DB not available -> use last transient if exists, else empty.
            if ($cached !== false) {

                // Extend transient by 65 minutes in emergency mode.
                set_transient($transient_key, $cached, 3600);

                self::debug_log(__FUNCTION__ . ': NO DB connection → using last TRANSIENT and extending 65min → key=' . $cache_key, 'error');

                self::$fairs_cache[$cache_key] = $cached;
                return $cached;
            }

            // No transient available -> return empty.
            self::debug_log(__FUNCTION__ . ': NO DB connection and no TRANSIENT → returning empty → key=' . $cache_key, 'error');
            error_log('get_database_fairs_data: NO DB connection and no TRANSIENT → returning empty → key=' . $cache_key);

            // Return without terminating the process when running under cron.
            if (defined('DOING_CRON') && DOING_CRON) {
                return [];
            }

            // Frontend fallback -> user-friendly 503.
            wp_die(
                '<h1>Przepraszamy</h1><p>Trwają prace techniczne. Spróbuj ponownie później.</p>',
                'Strona tymczasowo niedostępna',
                ['response' => 503]
            );

            return [];
        }

        // Base SQL.
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
                MAX(CASE WHEN fa.slug = 'fair_entrance' THEN fa.data END) AS fair_entrance,
                MAX(CASE WHEN fa.slug = 'available_languages' THEN fa.data END) AS available_languages

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
                    'konf_desc_en',
                    'available_languages'
                )
        ";

        // WHERE conditions.
        $params = [];

        if ($current_domain === 'warsawexpo.eu' || $fair_domain === 'all') {
            // Leave the query unfiltered.
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
            // Otherwise, leave the query unfiltered.
        }

        $sql .= " GROUP BY f.id ";

        $start_time = microtime(true);

        // Execute query.
        if (!empty($params)) {
            $query = call_user_func_array([$cap_db, 'prepare'], array_merge([$sql], $params));
            $results = $cap_db->get_results($query);
        } else {
            $results = $cap_db->get_results($sql);
        }

        $time = round((microtime(true) - $start_time) * 1000, 2);

        // SQL error.
        if ($cap_db->last_error) {
            self::debug_log(__FUNCTION__ . ': SQL error → ' . addslashes($cap_db->last_error), 'error');
            // Use last transient if available.
            if ($cached !== false) {
                set_transient($transient_key, $cached, 3600);
                self::$fairs_cache[$cache_key] = $cached;
                return $cached;
            }
            self::$fairs_cache[$cache_key] = [];
            return [];
        }

        // Cache results for 65 minutes.
        self::write_database_json_cache(__FUNCTION__, $cache_key, $results, [$fair_domain]);
        set_transient($transient_key, $results, 3600);

        self::$fairs_cache[$cache_key] = $results;
        self::debug_log(__FUNCTION__ . ': data from database DIRECTLY (SQL time ' . $time . 'ms) → key=' . $cache_key . ', host=' . $cap_db->dbhost . ' [' . gethostname() . '] and saved to TRANSIENT.');

        return $results;
    }

    /**
     * Runtime cache for CAP fairs adds records.
     */
    private static $fairs_adds_cache = [];

    /**
     * Fetch additional fair records from CAP.
     */
    public static function get_database_fairs_data_adds($fair_domain = null): array {

        // Resolve current domain.
        $fair_domain = $fair_domain ?? $_SERVER['HTTP_HOST'];
        $cache_key = $fair_domain;

        // STATIC cache.
        if (!self::$database_cache_force_refresh && isset(self::$fairs_adds_cache[$cache_key])) {
            self::debug_log(__FUNCTION__ . ': data from STATIC → key=' . $cache_key);
            return self::$fairs_adds_cache[$cache_key];
        }

        // Transient.
        $transient_key = 'pwe_fairs_adds_' . md5($cache_key);

        // Transient cache: preferred persistent cache before JSON.
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$fairs_adds_cache[$cache_key] = $cached;
            self::debug_log(__FUNCTION__ . ': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient.
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


        // Log transient timeout.
        $timeout = get_option('_transient_timeout_' . $transient_key);
        if ($timeout !== false) {
            $time_left = $timeout - time();
            $time_left_str = gmdate('H:i:s', max($time_left, 0));
        } else {
            $time_left_str = 'unknown';
        }



        // Connect DB.
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

        // SQL query.
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

        // SQL error.
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

        // Save transient + STATIC cache.
        self::write_database_json_cache(__FUNCTION__, $cache_key, $results, [$fair_domain]);
        set_transient($transient_key, $results, 3600);
        self::$fairs_adds_cache[$cache_key] = $results;
        self::debug_log(__FUNCTION__ . ': data from database DIRECTLY (SQL time ' . $time . 'ms) → key=' . $cache_key . ', host=' . $cap_db->dbhost . ' [' . gethostname() . '] and saved to TRANSIENT.');

        return $results;
    }

    /**
     * Runtime cache for CAP translations records.
     */
    private static $translations_cache = [];

    /**
     * Fetch translated fair records from CAP.
     */
    public static function get_database_translations_data($fair_domain = null): array {

        // Cache key.
        $cache_key = $fair_domain ?? 'all';

        // STATIC cache.
        if (!self::$database_cache_force_refresh && isset(self::$translations_cache[$cache_key])) {
            self::debug_log(__FUNCTION__ . ': STATIC → key=' . $cache_key);
            return self::$translations_cache[$cache_key];
        }

        // Transient.
        $transient_key = 'pwe_translations_' . md5($cache_key);

        // Transient cache: preferred persistent cache before JSON.
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$translations_cache[$cache_key] = $cached;
            self::debug_log(__FUNCTION__ . ': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient.
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


        // Log transient timeout.
        $timeout = get_option('_transient_timeout_' . $transient_key);
        if ($timeout !== false) {
            $time_left = $timeout - time();
            $time_left_str = gmdate('H:i:s', max($time_left, 0));
        } else {
            $time_left_str = 'unknown';
        }



        // Connect DB.
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

        // Fetch fairs, additional fields and translations independently.
        // Joining both one-to-many tables multiplies result rows and memory use.
        $start_time = microtime(true);
        $where = $fair_domain !== null ? ' WHERE f.fair_domain = %s' : '';

        $fairs_sql = "
            SELECT
                f.id, f.fair_domain,
                f.fair_name_pl, f.fair_desc_pl, f.fair_short_desc_pl, f.fair_full_desc_pl,
                f.fair_name_en, f.fair_desc_en, f.fair_short_desc_en, f.fair_full_desc_en
            FROM fairs f" . $where;
        $fairs_rows = $fair_domain !== null
            ? $cap_db->get_results($cap_db->prepare($fairs_sql, $fair_domain))
            : $cap_db->get_results($fairs_sql);

        if ($cap_db->last_error || !is_array($fairs_rows)) {
            self::debug_log(__FUNCTION__ . ': fairs SQL error: ' . addslashes($cap_db->last_error), 'error');
            if ($cached !== false) {
                set_transient($transient_key, $cached, 3600);
                self::$translations_cache[$cache_key] = $cached;
                return $cached;
            }
            self::$translations_cache[$cache_key] = [];
            return [];
        }

        $results = [];
        foreach ($fairs_rows as $row) {
            $results[$row->id] = [
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
        unset($fairs_rows, $row);
        $cap_db->flush();

        $adds_fields = [
            'category_pl', 'category_en',
            'about_title_pl', 'about_title_en',
            'about_desc_pl', 'about_desc_en',
            'konf_title_pl', 'konf_title_en',
            'konf_desc_pl', 'konf_desc_en',
        ];
        $placeholders = implode(', ', array_fill(0, count($adds_fields), '%s'));
        $adds_sql = "
            SELECT fa.fair_id, fa.slug, fa.data
            FROM fair_adds fa
            INNER JOIN fairs f ON f.id = fa.fair_id
            WHERE fa.slug IN ($placeholders)";
        $adds_params = $adds_fields;
        if ($fair_domain !== null) {
            $adds_sql .= ' AND f.fair_domain = %s';
            $adds_params[] = $fair_domain;
        }
        $adds_rows = $cap_db->get_results($cap_db->prepare($adds_sql, ...$adds_params));

        if ($cap_db->last_error || !is_array($adds_rows)) {
            self::debug_log(__FUNCTION__ . ': adds SQL error: ' . addslashes($cap_db->last_error), 'error');
            self::$translations_cache[$cache_key] = [];
            return [];
        }
        foreach ($adds_rows as $row) {
            if (isset($results[$row->fair_id])) {
                $results[$row->fair_id][$row->slug] = $row->data;
            }
        }
        unset($adds_rows, $row);
        $cap_db->flush();

        $translations_sql = "
            SELECT t.fair_id, t.language, t.translation
            FROM translations t
            INNER JOIN fairs f ON f.id = t.fair_id" . $where;
        $translations_rows = $fair_domain !== null
            ? $cap_db->get_results($cap_db->prepare($translations_sql, $fair_domain))
            : $cap_db->get_results($translations_sql);

        if ($cap_db->last_error || !is_array($translations_rows)) {
            self::debug_log(__FUNCTION__ . ': translations SQL error: ' . addslashes($cap_db->last_error), 'error');
            self::$translations_cache[$cache_key] = [];
            return [];
        }

        $translation_fields = [
            'fair_name', 'fair_desc', 'fair_short_desc', 'fair_full_desc',
            'category', 'about_title', 'about_desc', 'konf_title', 'konf_desc',
        ];
        foreach ($translations_rows as $row) {
            if (!isset($results[$row->fair_id]) || empty($row->translation)) {
                continue;
            }
            $lang = strtolower((string) $row->language);
            $data = json_decode($row->translation, true);
            if (!is_array($data)) {
                continue;
            }
            foreach ($translation_fields as $field) {
                if (isset($data[$field])) {
                    $results[$row->fair_id]["{$field}_$lang"] = $data[$field];
                }
            }
        }
        unset($translations_rows, $row, $data);
        $cap_db->flush();

        $time = round((microtime(true) - $start_time) * 1000, 2);
        $results = array_values($results);

        // WPML languages.
        $languages = apply_filters('wpml_active_languages', null);

        $langs = [];

        if (!empty($languages) && is_array($languages)) {

            foreach ($languages as $lang) {
                $code = $lang['language_code'];

                // Ignore base languages.
                if (in_array($code, ['pl', 'en'], true)) {
                    continue;
                }

                $langs[] = $code;
            }

        } else {
            // Fallback only when WPML is not available.
            $langs = ['cs','de','it','lt','lv','ru','sk','uk'];
        }

        // Save transient + STATIC cache.
        self::write_database_json_cache(__FUNCTION__, $cache_key, $results, [$fair_domain]);
        set_transient($transient_key, $results, 3600);
        self::$translations_cache[$cache_key] = $results;
        self::debug_log(__FUNCTION__ . ': data from database DIRECTLY (SQL time ' . $time . 'ms) → key=' . $cache_key . ', host=' . $cap_db->dbhost . ' [' . gethostname() . '] and saved to TRANSIENT.');

        return $results;
    }

    /**
     * Runtime cache for CAP associates records.
     */
    private static $associates_cache = [];

    /**
     * Fetch associate records from CAP by domain and optional block.
     */
    public static function get_database_associates_data (
        $fair_domain = null,
        bool $fair_block = false
    ): array {

        // Resolve domain.
        $fair_domain = $fair_domain ?? $_SERVER['HTTP_HOST'];
        $fair_domain = strtolower(trim($fair_domain));

        // Include mode in cache key.
        $cache_key = $fair_domain . '|fair_block:' . (int) $fair_block;

        // Static cache.
        if (!self::$database_cache_force_refresh && isset(self::$associates_cache[$cache_key])) {
            self::debug_log(
                'get_database_associates_data: data from STATIC → key=' . $cache_key
            );

            return self::$associates_cache[$cache_key];
        }

        // Transient cache.
        $transient_key = 'pwe_associates_' . md5($cache_key);

        // Transient cache: preferred persistent cache before JSON.
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$associates_cache[$cache_key] = $cached;
            self::debug_log(__FUNCTION__ . ': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient.
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


        // Cache timeout.
        $timeout = get_option('_transient_timeout_' . $transient_key);

        if ($timeout !== false) {
            $time_left = $timeout - time();
            $time_left_str = gmdate('H:i:s', max($time_left, 0));
        } else {
            $time_left_str = 'unknown';
        }



        // Connect database.
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

        // Build query.
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

        // Run query.
        $start_time = microtime(true);

        $prepared_sql = $cap_db->prepare($sql, $fair_domain);
        $results = $cap_db->get_results($prepared_sql);

        $time = round((microtime(true) - $start_time) * 1000, 2);

        // Handle SQL error.
        if ($cap_db->last_error) {
            self::debug_log(
                'get_database_associates_data: SQL error: ' .
                addslashes($cap_db->last_error),
                'error'
            );

            self::$associates_cache[$cache_key] = [];

            return [];
        }

        // Save cache.
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
     * Runtime cache for CAP store records.
     */
    private static $store_cache = null;

    /**
     * Fetch store product records from CAP.
     */
    public static function get_database_store_data(): array {

        $cache_key = 'store';

        // STATIC cache.
        if (!self::$database_cache_force_refresh && self::$store_cache !== null) {
            self::debug_log(__FUNCTION__ . ': data from STATIC memory');
            return self::$store_cache;
        }

        // Transient.
        $transient_key = 'pwe_store_data';

        // Transient cache: preferred persistent cache before JSON.
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$store_cache = $cached;
            self::debug_log(__FUNCTION__ . ': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient.
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


        // Log transient timeout.
        $timeout = get_option('_transient_timeout_' . $transient_key);
        if ($timeout !== false) {
            $time_left = $timeout - time();
            $time_left_str = gmdate('H:i:s', max($time_left, 0));
        } else {
            $time_left_str = 'unknown';
        }



        // Connect DB.
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

        // SQL query.
        $sql = "SELECT * FROM shop";
        $start_time = microtime(true);
        $results = $cap_db->get_results($sql);
        $time = round((microtime(true) - $start_time) * 1000, 2);

        // SQL error.
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

        // Save transient + STATIC cache.
        self::write_database_json_cache(__FUNCTION__, $cache_key, $results, []);
        set_transient($transient_key, $results, 3600);
        self::$store_cache = $results;
        self::debug_log(__FUNCTION__ . ': data from database DIRECTLY (SQL time ' . $time . 'ms) → key=' . $cache_key . ', host=' . $cap_db->dbhost . ' [' . gethostname() . '] and saved to TRANSIENT.');

        return $results;
    }

    /**
     * Runtime cache for CAP store packages records.
     */
    private static $store_packages_cache = null;

    /**
     * Fetch store package records from CAP.
     */
    public static function get_database_store_packages_data(): array {

        $cache_key = 'store_packages';

        // STATIC cache.
        if (!self::$database_cache_force_refresh && self::$store_packages_cache !== null) {
            self::debug_log(__FUNCTION__ . ': data from STATIC memory');
            return self::$store_packages_cache;
        }

        // Transient.
        $transient_key = 'pwe_store_packages';

        // Transient cache: preferred persistent cache before JSON.
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$store_packages_cache = $cached;
            self::debug_log(__FUNCTION__ . ': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient.
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


        // Log transient timeout.
        $timeout = get_option('_transient_timeout_' . $transient_key);
        $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';



        // Connect DB.
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

        // SQL query.
        $sql = "SELECT * FROM shop_packs";
        $start_time = microtime(true);
        $results = $cap_db->get_results($sql);
        $time = round((microtime(true) - $start_time) * 1000, 2);

        // SQL error.
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

        // Save transient + STATIC cache.
        self::write_database_json_cache(__FUNCTION__, $cache_key, $results, []);
        set_transient($transient_key, $results, 3600);
        self::$store_packages_cache = $results;
        self::debug_log(__FUNCTION__ . ': data from database DIRECTLY (SQL time ' . $time . 'ms) → key=' . $cache_key . ', host=' . $cap_db->dbhost . ' [' . gethostname() . '] and saved to TRANSIENT.');

        return $results;
    }

    /**
     * Runtime cache for CAP meta records.
     */
    private static $meta_cache = [];

    /**
     * Fetch CAP metadata by optional record ID and domain.
     */
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

        // STATIC cache.
        if (!self::$database_cache_force_refresh && isset(self::$meta_cache[$cache_key])) {
            self::debug_log(__FUNCTION__ . ': data from STATIC → key=' . $cache_key);
            return self::$meta_cache[$cache_key];
        }

        // Transient.
        $transient_key = 'pwe_meta_' . md5($cache_key);

        // Transient cache: preferred persistent cache before JSON.
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$meta_cache[$cache_key] = $cached;
            self::debug_log(__FUNCTION__ . ': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient.
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


        // Log transient timeout.
        $timeout = get_option('_transient_timeout_' . $transient_key);
        $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';

        // Connect DB.
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

        // SQL query.
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

        // SQL error.
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

        // Save transient + STATIC cache.
        self::write_database_json_cache(__FUNCTION__, $cache_key, $results, [$data_id, $domain]);
        set_transient($transient_key, $results, 3600);
        self::$meta_cache[$cache_key] = $results;
        self::debug_log(__FUNCTION__ . ': data from database DIRECTLY (SQL time ' . $time . 'ms) → key=' . $cache_key . ', host=' . $cap_db->dbhost . ' [' . gethostname() . '] and saved to TRANSIENT.');

        return $results;
    }

    /**
     * Runtime cache for CAP groups contacts records.
     */
    private static $groups_contacts_cache = null;

    /**
     * Fetch group contact records from CAP.
     */
    public static function get_database_groups_contacts_data(): array {

        $cache_key = 'groups_contacts';

        // STATIC cache.
        if (!self::$database_cache_force_refresh && self::$groups_contacts_cache !== null) {
            self::debug_log(__FUNCTION__ . ': data from STATIC memory');
            return self::$groups_contacts_cache;
        }

        $transient_key = 'pwe_groups_contacts';

        // Transient cache: preferred persistent cache before JSON.
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$groups_contacts_cache = $cached;
            self::debug_log(__FUNCTION__ . ': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient.
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
     * Runtime cache for CAP groups callcenter records.
     */
    private static $groups_callcenter_cache = null;

    /**
     * Fetch group call center records from CAP.
     */
    public static function get_database_groups_callcenter_data(): array {

        $cache_key = 'groups_callcenter';

        if (!self::$database_cache_force_refresh && self::$groups_callcenter_cache !== null) {
            self::debug_log(__FUNCTION__ . ': data from STATIC memory');
            return self::$groups_callcenter_cache;
        }

        $transient_key = 'pwe_groups_callcenter';

        // Transient cache: preferred persistent cache before JSON.
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$groups_callcenter_cache = $cached;
            self::debug_log(__FUNCTION__ . ': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient.
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
     * Runtime cache for CAP groups records.
     */
    private static $groups_cache = null;

    /**
     * Fetch fair group records from CAP.
     */
    public static function get_database_groups_data(): array {

        $cache_key = 'groups';

        if (!self::$database_cache_force_refresh && self::$groups_cache !== null) {
            self::debug_log(__FUNCTION__ . ': data from STATIC memory');
            return self::$groups_cache;
        }

        $transient_key = 'pwe_groups';

        // Transient cache: preferred persistent cache before JSON.
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$groups_cache = $cached;
            self::debug_log(__FUNCTION__ . ': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient.
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
     * Runtime cache for CAP week data records.
     */
    private static $week_data_cache = [];

    /**
     * Fetch week records from CAP for a domain.
     */
    public static function get_database_week_data($fair_domain = null): array {

        $current_domain = $fair_domain ?? $_SERVER['HTTP_HOST'];
        $cache_key = $current_domain;

        if (!self::$database_cache_force_refresh && isset(self::$week_data_cache[$cache_key])) {
            self::debug_log(__FUNCTION__ .': data from STATIC → key=' . $cache_key);
            return self::$week_data_cache[$cache_key];
        }

        $transient_key = 'pwe_week_data_' . md5($cache_key);

        // Transient cache: preferred persistent cache before JSON.
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$week_data_cache[$cache_key] = $cached;
            self::debug_log(__FUNCTION__ . ': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient.
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
     * Runtime cache for CAP week all records.
     */
    private static $week_all_cache = [];

    /**
     * Fetch the complete week configuration from CAP for a domain.
     */
    public static function get_database_week_all($fair_domain = null) {

        $current_domain = $fair_domain ?? $_SERVER['HTTP_HOST'];
        $cache_key = $current_domain;

        if (!self::$database_cache_force_refresh && isset(self::$week_all_cache[$cache_key])) {
            self::debug_log(__FUNCTION__ .': data from STATIC → key=' . $cache_key);
            return self::$week_all_cache[$cache_key];
        }

        $transient_key = 'pwe_week_all_' . md5($cache_key);

        // Transient cache: preferred persistent cache before JSON.
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$week_all_cache[$cache_key] = $cached;
            self::debug_log(__FUNCTION__ .': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient.
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
     * Runtime cache for CAP all week domains records.
     */
    private static $all_week_domains_cache = null;

    /**
     * Fetch all week domains from CAP.
     */
    public static function get_all_week_domains(): array {

        $cache_key = 'all_week_domains';

        if (!self::$database_cache_force_refresh && self::$all_week_domains_cache !== null) {
            self::debug_log(__FUNCTION__ .': data from STATIC memory');
            return self::$all_week_domains_cache;
        }

        $transient_key = 'pwe_all_week_domains';

        // Transient cache: preferred persistent cache before JSON.
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$all_week_domains_cache = $cached;
            self::debug_log(__FUNCTION__ . ': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient.
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
     * Runtime cache for CAP logotypes records.
     */
    private static $logotypes_cache = [];

    /**
     * Fetch logotypes from CAP and apply the existing week deduplication rules.
     */
    public static function get_database_logotypes_data($fair_domain = null): array {

        $current_domain = $fair_domain ?? $_SERVER['HTTP_HOST'];
        $cache_key = $current_domain;

        if (!self::$database_cache_force_refresh && isset(self::$logotypes_cache[$cache_key])) {
            self::debug_log(__FUNCTION__ .': data from STATIC → key=' . $cache_key);
            return self::$logotypes_cache[$cache_key];
        }

        $transient_key = 'pwe_logotypes_' . md5($cache_key);

        // Transient cache: preferred persistent cache before JSON.
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$logotypes_cache[$cache_key] = $cached;
            self::debug_log(__FUNCTION__ . ': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient.
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

        // Remove duplicates if the current week is the same as the current domain.
        $associates = self::get_database_associates_data($current_domain);
        $current_week = !empty($associates) ? $associates[0]->main_fair_domain : '';

        if ($current_week === $current_domain) {
            $results = self::remove_logo_duplicates($results);
        }
        
        $time = round((microtime(true) - $start_time) * 1000, 2);

        self::write_database_json_cache(__FUNCTION__, $cache_key, $results, [$fair_domain]);
        set_transient($transient_key, $results, 3600);
        self::$logotypes_cache[$cache_key] = $results;
        self::debug_log(__FUNCTION__ .': data from database DIRECTLY (SQL time ' . $time . 'ms) → key=' . $cache_key . ', host=' . $cap_db->dbhost . ' [' . gethostname() . '] and saved to TRANSIENT.');

        return $results;
    }

    /**
     * Runtime cache for CAP conferences records.
     */
    private static $conferences_cache = [];

    /**
     * Fetch conference records from CAP for a domain.
     */
    public static function get_database_conferences_data($domain = null): array {

        $domain = $domain ?? $_SERVER['HTTP_HOST'];
        $cache_key = $domain;

        if (!self::$database_cache_force_refresh && isset(self::$conferences_cache[$cache_key])) {
            self::debug_log(__FUNCTION__ . ': data from STATIC → key=' . $cache_key);
            return self::$conferences_cache[$cache_key];
        }

        $transient_key = 'pwe_conferences_' . md5($cache_key);

        // Transient cache: preferred persistent cache before JSON.
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$conferences_cache[$cache_key] = $cached;
            self::debug_log(__FUNCTION__ .': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient.
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
     * Runtime cache for CAP conference adds records.
     */
    private static $conference_adds_cache = [];

    /**
     * Fetch additional conference records from CAP by conference ID.
     */
    public static function get_database_conference_adds_data($conf_id): array {

        $conf_id = (int) $conf_id;

        if ($conf_id <= 0) {
            return [];
        }

        $cache_key = (string) $conf_id;

        // STATIC.
        if (!self::$database_cache_force_refresh && isset(self::$conference_adds_cache[$cache_key])
        ) {
            self::debug_log(__FUNCTION__ . ': data from STATIC → key=' . $cache_key);
            return self::$conference_adds_cache[$cache_key];
        }

        // TRANSIENT.
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

        // JSON FILE.
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

        // DATABASE.
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

        // JSON.
        self::write_database_json_cache(__FUNCTION__, $cache_key, $results, [$conf_id]);

        // TRANSIENT.
        set_transient($transient_key, $results, 3600);

        // STATIC.
        self::$conference_adds_cache[$cache_key] = $results;

        self::debug_log(__FUNCTION__ .': data from database DIRECTLY ' .'(SQL time ' . $time . 'ms) → key='. $cache_key .', host=' .$cap_db->dbhost .' [' . gethostname() .'] and saved to JSON + TRANSIENT.');

        return $results;
    }

    /**
     * Runtime cache for CAP fairs profiles records.
     */
    private static $fairs_profiles_cache = [];

    /**
     * Fetch fair profile records from CAP.
     */
    public static function get_database_fairs_data_profiles($fair_domain = null): array {

        $fair_domain = $fair_domain ?? $_SERVER['HTTP_HOST'] ?? '';
        $cache_key = $fair_domain;

        if (!self::$database_cache_force_refresh && isset(self::$fairs_profiles_cache[$cache_key])) {
            self::debug_log(__FUNCTION__ . ': data from STATIC → key=' . $cache_key);
            return self::$fairs_profiles_cache[$cache_key];
        }

        $transient_key = 'pwe_fairs_profiles_' . md5($cache_key);

        // Transient cache: preferred persistent cache before JSON.
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$fairs_profiles_cache[$cache_key] = $cached;
            self::debug_log(__FUNCTION__ . ': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient.
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
     * Runtime cache for CAP premieres records.
     */
    private static $premieres_cache = [];

    /**
     * Fetch fair premiere records from CAP.
     */
    public static function get_database_premieres_data($fair_domain = null): array {

        $cache_key = $fair_domain ?? 'all';

        if (!self::$database_cache_force_refresh && isset(self::$premieres_cache[$cache_key])) {
            self::debug_log(__FUNCTION__ . ': data from STATIC → key=' . $cache_key);
            return self::$premieres_cache[$cache_key];
        }

        $transient_key = 'pwe_premieres_' . md5($cache_key);

        // Transient cache: preferred persistent cache before JSON.
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$premieres_cache[$cache_key] = $cached;
            self::debug_log(__FUNCTION__ . ': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient.
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
     * Runtime cache for CAP fairs opinions records.
     */
    private static $fairs_opinions_cache = [];

    /**
     * Fetch fair opinion records from CAP.
     */
    public static function get_database_fairs_data_opinions($fair_domain = null): array {

        $fair_domain = $fair_domain ?? $_SERVER['HTTP_HOST'] ?? '';
        $cache_key = $fair_domain;

        // Check runtime cache first.
        if (!self::$database_cache_force_refresh && isset(self::$fairs_opinions_cache[$cache_key])) {
            self::debug_log(__FUNCTION__ . ': data from STATIC → key=' . $cache_key);
            return self::$fairs_opinions_cache[$cache_key];
        }

        // Transient key.
        $transient_key = 'pwe_fairs_opinions_' . md5($cache_key);

        // Transient cache: preferred persistent cache before JSON.
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$fairs_opinions_cache[$cache_key] = $cached;
            self::debug_log(__FUNCTION__ . ': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient.
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

        // Connect to database.
        $cap_db = self::connect_database();
        if (!$cap_db) {
            self::debug_log(__FUNCTION__ . ': no database connection.', 'error');
            self::$fairs_opinions_cache[$cache_key] = [];
            return [];
        }

        // SQL query.
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

        // Execute query.
        $results = !empty($params) ? $cap_db->get_results($cap_db->prepare($sql, $params)) : $cap_db->get_results($sql);
        $time = round((microtime(true) - $start_time) * 1000, 2);

        // Handle SQL errors.
        if ($cap_db->last_error) {
            self::debug_log(__FUNCTION__ . ': SQL error: ' . addslashes($cap_db->last_error), 'error');
            $results = [];
        }

        // Save to transient for 65 minutes.
        self::write_database_json_cache(__FUNCTION__, $cache_key, $results, [$fair_domain]);
        set_transient($transient_key, $results, 3600);

        // Save to runtime cache.
        self::$fairs_opinions_cache[$cache_key] = $results;
        self::debug_log(__FUNCTION__ . ': data from database DIRECTLY (SQL time ' . $time . 'ms) → key=' . $cache_key . ', host=' . $cap_db->dbhost . ' [' . gethostname() . '] and saved to TRANSIENT.');

        return $results;
    }

    /**
     * Runtime cache for CAP fairs sectors records.
     */
    private static $fairs_sectors_cache = [];

    /**
     * Fetch fair sector records from CAP.
     */
    public static function get_database_fairs_data_sectors($fair_domain = null): array {

        $fair_domain = $fair_domain ?? $_SERVER['HTTP_HOST'] ?? '';
        $cache_key = $fair_domain;

        // Check runtime cache first.
        if (!self::$database_cache_force_refresh && isset(self::$fairs_sectors_cache[$cache_key])) {
            self::debug_log(__FUNCTION__ . ': data from STATIC → key=' . $cache_key);
            return self::$fairs_sectors_cache[$cache_key];
        }

        // Transient key.
        $transient_key = 'pwe_fairs_sectors_' . md5($cache_key);

        // Transient cache: preferred persistent cache before JSON.
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$fairs_sectors_cache[$cache_key] = $cached;
            self::debug_log(__FUNCTION__ . ': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient.
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

        // Connect to database.
        $cap_db = self::connect_database();
        if (!$cap_db) {
            self::debug_log(__FUNCTION__ . ': no database connection.', 'error');
            self::$fairs_sectors_cache[$cache_key] = [];
            return [];
        }

        // SQL query.
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

        // Execute query.
        $results = !empty($params) ? $cap_db->get_results($cap_db->prepare($sql, $params)) : $cap_db->get_results($sql);
        $time = round((microtime(true) - $start_time) * 1000, 2);

        // Handle SQL errors.
        if ($cap_db->last_error) {
            self::debug_log(__FUNCTION__ . ': SQL error: ' . addslashes($cap_db->last_error), 'error');
            $results = [];
        }

        // Save to transient for 65 minutes.
        self::write_database_json_cache(__FUNCTION__, $cache_key, $results, [$fair_domain]);
        set_transient($transient_key, $results, 3600);

        // Save to runtime cache.
        self::$fairs_sectors_cache[$cache_key] = $results;
        self::debug_log(__FUNCTION__ . ': data from database DIRECTLY (SQL time ' . $time . 'ms) → key=' . $cache_key . ', host=' . $cap_db->dbhost . ' [' . gethostname() . '] and saved to TRANSIENT.');

        return $results;
    }

    /**
     * Runtime cache for CAP fairs tickets records.
     */
    private static $fairs_tickets_cache = [];

    /**
     * Fetch fair ticket records from CAP.
     */
    public static function get_database_fairs_data_tickets($fair_domain = null): array {

        $fair_domain = $fair_domain ?? $_SERVER['HTTP_HOST'] ?? '';
        $cache_key = $fair_domain;

        // Check runtime cache first.
        if (!self::$database_cache_force_refresh && isset(self::$fairs_tickets_cache[$cache_key])) {
            self::debug_log(__FUNCTION__ . ': data from STATIC → key=' . $cache_key);
            return self::$fairs_tickets_cache[$cache_key];
        }

        // Transient key.
        $transient_key = 'pwe_fairs_tickets_' . md5($cache_key);

        // Transient cache: preferred persistent cache before JSON.
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$fairs_tickets_cache[$cache_key] = $cached;
            self::debug_log(__FUNCTION__ . ': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient.
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

        // Connect to database.
        $cap_db = self::connect_database();
        if (!$cap_db) {
            self::debug_log(__FUNCTION__ . ': no database connection.', 'error');
            self::$fairs_tickets_cache[$cache_key] = [];
            return [];
        }

        // SQL query.
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

        // Execute query.
        $results = !empty($params) ? $cap_db->get_results($cap_db->prepare($sql, $params)) : $cap_db->get_results($sql);
        $time = round((microtime(true) - $start_time) * 1000, 2);

        // Handle SQL errors.
        if ($cap_db->last_error) {
            self::debug_log(__FUNCTION__ . ': SQL error: ' . addslashes($cap_db->last_error), 'error');
            $results = [];
        }

        // Save to transient for 65 minutes.
        self::write_database_json_cache(__FUNCTION__, $cache_key, $results, [$fair_domain]);
        set_transient($transient_key, $results, 3600);

        // Save to runtime cache.
        self::$fairs_tickets_cache[$cache_key] = $results;
        self::debug_log(__FUNCTION__ . ': data from database DIRECTLY (SQL time ' . $time . 'ms) → key=' . $cache_key . ', host=' . $cap_db->dbhost . ' [' . gethostname() . '] and saved to TRANSIENT.');

        return $results;
    }

    /**
     * Runtime cache for CAP fairs speakers records.
     */
    private static $fairs_speakers_cache = [];

    /**
     * Fetch fair speaker records from CAP.
     */
    public static function get_database_fairs_data_speakers($fair_domain = null): array {

        $fair_domain = $fair_domain ?? $_SERVER['HTTP_HOST'] ?? '';
        $cache_key = $fair_domain;

        // Check runtime cache first.
        if (!self::$database_cache_force_refresh && isset(self::$fairs_speakers_cache[$cache_key])) {
            self::debug_log(__FUNCTION__ . ': data from STATIC → key=' . $cache_key);
            return self::$fairs_speakers_cache[$cache_key];
        }

        // Transient key.
        $transient_key = 'pwe_fairs_speakers_' . md5($cache_key);

        // Transient cache: preferred persistent cache before JSON.
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$fairs_speakers_cache[$cache_key] = $cached;
            self::debug_log(__FUNCTION__ . ': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient.
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

        // Connect to database.
        $cap_db = self::connect_database();
        if (!$cap_db) {
            self::debug_log(__FUNCTION__ . ': no database connection.', 'error');
            self::$fairs_speakers_cache[$cache_key] = [];
            return [];
        }

        // SQL query.
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

        // Execute query.
        $results = !empty($params) ? $cap_db->get_results($cap_db->prepare($sql, $params)) : $cap_db->get_results($sql);

        $time = round((microtime(true) - $start_time) * 1000, 2);

        // Handle SQL errors.
        if ($cap_db->last_error) {
            self::debug_log(__FUNCTION__ . ': SQL error: ' . addslashes($cap_db->last_error), 'error');
            $results = [];
        }

        // Save to transient for 65 minutes.
        self::write_database_json_cache(__FUNCTION__, $cache_key, $results, [$fair_domain]);
        set_transient($transient_key, $results, 3600);

        // Save to runtime cache.
        self::$fairs_speakers_cache[$cache_key] = $results;
        self::debug_log(__FUNCTION__ . ': data from database DIRECTLY (SQL time ' . $time . 'ms) → key=' . $cache_key . ', host=' . $cap_db->dbhost . ' [' . gethostname() . '] and saved to TRANSIENT.');

        return $results;
    }

    /**
     * Runtime cache for CAP fairs guests records.
     */
    private static $fairs_guests_cache = [];

    /**
     * Fetch fair guest records from CAP.
     */
    public static function get_database_fairs_data_guests($fair_domain = null): array {

        $fair_domain = $fair_domain ?? $_SERVER['HTTP_HOST'] ?? '';
        $cache_key = $fair_domain;

        // Check runtime cache first.
        if (!self::$database_cache_force_refresh && isset(self::$fairs_guests_cache[$cache_key])) {
            self::debug_log(__FUNCTION__ . ': data from STATIC → key=' . $cache_key);
            return self::$fairs_guests_cache[$cache_key];
        }

        // Transient key.
        $transient_key = 'pwe_fairs_guests_' . md5($cache_key);

        // Transient cache: preferred persistent cache before JSON.
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$fairs_guests_cache[$cache_key] = $cached;
            self::debug_log(__FUNCTION__ . ': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient.
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

        // Connect to database.
        $cap_db = self::connect_database();
        if (!$cap_db) {
            self::debug_log(__FUNCTION__ . ': no database connection.', 'error');
            self::$fairs_guests_cache[$cache_key] = [];
            return [];
        }

        // SQL query.
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

        // Execute query.
        $results = !empty($params) ? $cap_db->get_results($cap_db->prepare($sql, $params)) : $cap_db->get_results($sql);

        $time = round((microtime(true) - $start_time) * 1000, 2);

        // Handle SQL errors.
        if ($cap_db->last_error) {
            self::debug_log(__FUNCTION__ . ': SQL error: ' . addslashes($cap_db->last_error), 'error');
            $results = [];
        }

        // Save to transient for 65 minutes.
        self::write_database_json_cache(__FUNCTION__, $cache_key, $results, [$fair_domain]);
        set_transient($transient_key, $results, 3600);

        // Save to runtime cache.
        self::$fairs_guests_cache[$cache_key] = $results;
        self::debug_log(__FUNCTION__ . ': data from database DIRECTLY (SQL time ' . $time . 'ms) → key=' . $cache_key . ', host=' . $cap_db->dbhost . ' [' . gethostname() . '] and saved to TRANSIENT.');

        return $results;
    }

    /**
     * Runtime cache for CAP fairs attractions records.
     */
    private static $fairs_attractions_cache = [];

    /**
     * Fetch fair attraction records from CAP.
     */
    public static function get_database_fairs_data_attractions($fair_domain = null): array {

        $fair_domain = $fair_domain ?? $_SERVER['HTTP_HOST'] ?? '';
        $cache_key = $fair_domain;

        // Check runtime cache first.
        if (!self::$database_cache_force_refresh && isset(self::$fairs_attractions_cache[$cache_key])) {
            self::debug_log(__FUNCTION__ . ': data from STATIC → key=' . $cache_key);
            return self::$fairs_attractions_cache[$cache_key];
        }

        // Transient key.
        $transient_key = 'pwe_fairs_attractions_' . md5($cache_key);

        // Transient cache: preferred persistent cache before JSON.
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$fairs_attractions_cache[$cache_key] = $cached;
            self::debug_log(__FUNCTION__ . ': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient.
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

        // Connect to database.
        $cap_db = self::connect_database();
        if (!$cap_db) {
            self::debug_log(__FUNCTION__ . ': no database connection.', 'error');
            self::$fairs_attractions_cache[$cache_key] = [];
            return [];
        }

        // SQL query.
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

        // Execute query.
        $results = !empty($params) ? $cap_db->get_results($cap_db->prepare($sql, $params)) : $cap_db->get_results($sql);

        $time = round((microtime(true) - $start_time) * 1000, 2);

        // Handle SQL errors.
        if ($cap_db->last_error) {
            self::debug_log(__FUNCTION__ . ': SQL error: ' . addslashes($cap_db->last_error), 'error');
            $results = [];
        }

        // Save to transient for 65 minutes.
        self::write_database_json_cache(__FUNCTION__, $cache_key, $results, [$fair_domain]);
        set_transient($transient_key, $results, 3600);

        // Save to runtime cache.
        self::$fairs_attractions_cache[$cache_key] = $results;
        self::debug_log(__FUNCTION__ . ': data from database DIRECTLY (SQL time ' . $time . 'ms) → key=' . $cache_key . ', host=' . $cap_db->dbhost . ' [' . gethostname() . '] and saved to TRANSIENT.');

        return $results;
    }

    /**
     * Runtime cache for CAP fairs files records.
     */
    private static $fairs_files_cache = [];

    /**
     * Fetch fair file records from CAP.
     */
    public static function get_database_fairs_data_files($fair_domain = null): array {

        $fair_domain = $fair_domain ?? $_SERVER['HTTP_HOST'] ?? '';
        $cache_key = $fair_domain;

        // Check runtime cache first.
        if (!self::$database_cache_force_refresh && isset(self::$fairs_files_cache[$cache_key])) {
            self::debug_log(__FUNCTION__ . ': data from STATIC → key=' . $cache_key);
            return self::$fairs_files_cache[$cache_key];
        }

        // Transient key.
        $transient_key = 'pwe_fairs_files_' . md5($cache_key);

        // Transient cache: preferred persistent cache before JSON.
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$fairs_files_cache[$cache_key] = $cached;
            self::debug_log(__FUNCTION__ . ': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient.
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

        // Connect to database.
        $cap_db = self::connect_database();
        if (!$cap_db) {
            self::debug_log(__FUNCTION__ . ': no database connection.', 'error');
            self::$fairs_files_cache[$cache_key] = [];
            return [];
        }

        // SQL query.
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

        // Execute query.
        $results = !empty($params) ? $cap_db->get_results($cap_db->prepare($sql, $params)) : $cap_db->get_results($sql);

        $time = round((microtime(true) - $start_time) * 1000, 2);

        // Handle SQL errors.
        if ($cap_db->last_error) {
            self::debug_log(__FUNCTION__ . ': SQL error: ' . addslashes($cap_db->last_error), 'error');
            $results = [];
        }

        // Save to transient for 65 minutes.
        self::write_database_json_cache(__FUNCTION__, $cache_key, $results, [$fair_domain]);
        set_transient($transient_key, $results, 3600);

        // Save to runtime cache.
        self::$fairs_files_cache[$cache_key] = $results;
        self::debug_log(__FUNCTION__ . ': data from database DIRECTLY (SQL time ' . $time . 'ms) → key=' . $cache_key . ', host=' . $cap_db->dbhost . ' [' . gethostname() . '] and saved to TRANSIENT.');

        return $results;
    }

    /**
     * Runtime cache for CAP elements records.
     */
    private static $elements_cache = null;

    /**
     * Fetch element configuration records from CAP.
     */
    public static function get_database_elements_data(): array {

        $cache_key = 'elements';

        // STATIC cache.
        if (!self::$database_cache_force_refresh && self::$elements_cache !== null) {
            self::debug_log(__FUNCTION__ . ': data from STATIC memory');
            return self::$elements_cache;
        }

        // Transient.
        $transient_key = 'pwe_elements_data';

        // Transient cache: preferred persistent cache before JSON.
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$elements_cache = $cached;
            self::debug_log(__FUNCTION__ . ': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient.
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


        // Log transient timeout.
        $timeout = get_option('_transient_timeout_' . $transient_key);
        if ($timeout !== false) {
            $time_left = $timeout - time();
            $time_left_str = gmdate('H:i:s', max($time_left, 0));
        } else {
            $time_left_str = 'unknown';
        }



        // Connect DB.
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

        // SQL query.
        $sql = "SELECT * FROM pwelements";
        $start_time = microtime(true);
        $results = $cap_db->get_results($sql);
        $time = round((microtime(true) - $start_time) * 1000, 2);

        // SQL error.
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

        // Save transient + STATIC cache.
        self::write_database_json_cache(__FUNCTION__, $cache_key, $results, []);
        set_transient($transient_key, $results, 3600);
        self::$elements_cache = $results;
        self::debug_log(__FUNCTION__ . ': data from database DIRECTLY (SQL time ' . $time . 'ms) → key=' . $cache_key . ', host=' . $cap_db->dbhost . ' [' . gethostname() . '] and saved to TRANSIENT.');

        return $results;
    }

    /**
     * Runtime cache for CAP elements order records.
     */
    private static $elements_order_cache = null;

    /**
     * Fetch element ordering records from CAP.
     */
    public static function get_database_elements_order_data(): array {

        $cache_key = 'elements_order';

        // STATIC cache.
        if (!self::$database_cache_force_refresh && self::$elements_order_cache !== null) {
            self::debug_log(__FUNCTION__ . ': data from STATIC memory');
            return self::$elements_order_cache;
        }

        // Transient.
        $transient_key = 'pwe_elements_order_data';

        // Transient cache: preferred persistent cache before JSON.
        $cached = self::$database_cache_force_refresh ? false : get_transient($transient_key);

        if ($cached !== false) {
            $timeout = get_option('_transient_timeout_' . $transient_key);
            $time_left_str = ($timeout !== false) ? gmdate('H:i:s', max($timeout - time(), 0)) : 'unknown';
            self::$elements_order_cache = $cached;
            self::debug_log(__FUNCTION__ . ': data from TRANSIENT → key=' . $cache_key . ', expires in ' . $time_left_str);
            return $cached;
        }

        // JSON file cache: fallback after transient.
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


        // Log transient timeout.
        $timeout = get_option('_transient_timeout_' . $transient_key);
        if ($timeout !== false) {
            $time_left = $timeout - time();
            $time_left_str = gmdate('H:i:s', max($time_left, 0));
        } else {
            $time_left_str = 'unknown';
        }



        // Connect DB.
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

        // SQL query.
        $sql = "SELECT * FROM pwe_order";
        $start_time = microtime(true);
        $results = $cap_db->get_results($sql);
        $time = round((microtime(true) - $start_time) * 1000, 2);

        // SQL error.
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

        // Save transient + STATIC cache.
        self::write_database_json_cache(__FUNCTION__, $cache_key, $results, []);
        set_transient($transient_key, $results, 3600);
        self::$elements_order_cache = $results;
        self::debug_log(__FUNCTION__ . ': data from database DIRECTLY (SQL time ' . $time . 'ms) → key=' . $cache_key . ', host=' . $cap_db->dbhost . ' [' . gethostname() . '] and saved to TRANSIENT.');

        return $results;
    }
}
