<?php

/**
 * Persistent JSON cache, serialization and cron refresh.
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Compose persistent JSON cache, serialization and cron refresh in PWE_System_Functions.
 *
 * @internal Composed by core/class-pwe-system-functions.php.
 */
trait PWE_System_Functions_Database_Cache {
    /**
     * Whether database getters must bypass the normal cache read order.
     */
    private static $database_cache_force_refresh = false;

    /**
     * Cache write outcomes collected for the current forced-refresh job.
     */
    private static $database_cache_write_results = [];

    /**
     * Resolve and protect the persistent CAP JSON cache directory.
     */
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

    /**
     * Build the JSON cache path for a source and cache key.
     */
    private static function get_database_json_cache_path($source, $cache_key) {
        $dir = self::get_database_json_cache_dir();

        if (!$dir) {
            return false;
        }

        $safe_source = sanitize_file_name($source);
        return trailingslashit($dir) . $safe_source . '--' . md5((string) $cache_key) . '.json';
    }

    /**
     * Serialize cache values while preserving arrays and object types.
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

    /**
     * Restore arrays and objects from serialized JSON cache values.
     */
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

    /**
     * Read a valid persistent cache document and restore its data.
     */
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

    /**
     * Atomically write data and getter arguments to the persistent JSON cache.
     */
    private static function write_database_json_cache($source, $cache_key, $data, array $args = []): bool {
        $write_key = $source . '|' . (string) $cache_key;
        if (self::$database_cache_force_refresh) {
            self::$database_cache_write_results[$write_key] = false;
        }

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

        if (self::$database_cache_force_refresh) {
            self::$database_cache_write_results[$write_key] = true;
        }

        return true;
    }

    /**
     * Refresh known CAP cache variants from the database for cron jobs.
     *
     * Normal reads: runtime cache -> transient -> JSON file -> database.
     * Forced refresh: database -> JSON file -> transient -> runtime cache.
     * When the database is unavailable, retain the existing getter fallback rules.
     * Discover getter variants from existing cache files and execute only allowed methods.
     *
     * @param string|null $domain Domain used while refreshing cache variants.
     * @return array Refresh report.
     */
    public static function refresh_database_json_cache($domain = null): array {

        if (empty($domain)) {
            $domain = $_SERVER['HTTP_HOST'] ?? '';

            // CLI cron has no browser Host header; resolve the WordPress site instead.
            if (empty($domain) && function_exists('home_url')) {
                $domain = wp_parse_url(home_url('/'), PHP_URL_HOST) ?: '';
            }
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


        // Save current HTTP_HOST.
        //
        // Some older database getters still rely on $_SERVER['HTTP_HOST'], so during cron refresh we
        // temporarily set it to the requested domain.
        $old_http_host = $_SERVER['HTTP_HOST'] ?? null;

        $_SERVER['HTTP_HOST'] = $domain;


        // Whitelist of methods that are allowed to be executed automatically based on JSON cache
        // files.
        //
        // This prevents an arbitrary method name stored in a modified JSON file from being executed by
        // the cron.
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


        // Build jobs automatically from existing JSON files.
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


        // Scan all JSON cache files.
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


            // Validate minimum JSON structure.
            if (
                empty($document['source']) ||
                !isset($document['args']) ||
                !is_array($document['args'])
            ) {
                continue;
            }


            $method = (string) $document['source'];
            $args = $document['args'];


            // Security whitelist.
            if (!isset($allowed_methods[$method])) {

                self::debug_log(
                    __FUNCTION__ .
                    ': method not allowed → ' .
                    $method,
                    'error'
                );

                continue;
            }


            // Method must still exist in the class.
            if (!method_exists(self::class, $method)) {

                self::debug_log(
                    __FUNCTION__ .
                    ': METHOD NOT FOUND → ' .
                    $method,
                    'error'
                );

                continue;
            }


            // Generate a unique signature from method + args.
            //
            // This prevents running the same cache variant multiple times if duplicate JSON files somehow
            // exist.
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


        // Prepare report.
        $report = [
            'success' => !empty($jobs),
            'domain' => $domain,
            'started_at' => date('Y-m-d H:i:s'),
            'files_found' => count($files),
            'jobs_found' => count($jobs),
            'jobs' => [],
        ];


        // Force getters to bypass:
        //
        // STATIC TRANSIENT JSON.
        //
        // And query DATABASE directly.
        if (empty($jobs)) {
            $report['error'] = 'no_cache_jobs';
        }

        self::$database_cache_force_refresh = true;


        try {

            foreach ($jobs as $signature => $job) {

                $method = $job['method'];
                $args = $job['args'];
                $file = $job['file'];


                try {

                    self::$database_cache_write_results = [];
                    $start_time = microtime(true);

                    // // Diagnostic marker is written before the getter, so a memory fatal identifies the failing job.
                    // if (function_exists('pwe_mass_vip_cron_log')) {
                    //     pwe_mass_vip_cron_log(
                    //         'CACHE JOB START | method=' . $method .
                    //         ' | file=' . $file .
                    //         ' | usage=' . round(memory_get_usage(true) / 1048576, 2) . 'MB' .
                    //         ' | peak=' . round(memory_get_peak_usage(true) / 1048576, 2) . 'MB'
                    //     );
                    // }

                    // Execute getter using exactly the same arguments that originally created the JSON cache file.
                    $result = call_user_func_array(
                        [self::class, $method],
                        $args
                    );


                    $time = round(
                        (microtime(true) - $start_time) * 1000,
                        2
                    );

                    // if (function_exists('pwe_mass_vip_cron_log')) {
                    //     pwe_mass_vip_cron_log(
                    //         'CACHE JOB END | method=' . $method .
                    //         ' | file=' . $file .
                    //         ' | usage=' . round(memory_get_usage(true) / 1048576, 2) . 'MB' .
                    //         ' | peak=' . round(memory_get_peak_usage(true) / 1048576, 2) . 'MB' .
                    //         ' | time_ms=' . $time
                    //     );
                    // }


                    if (is_array($result)) {

                        $records = count($result);

                    } elseif ($result === null) {

                        $records = 0;

                    } else {

                        $records = 1;
                    }


                    // A getter may return without throwing after an SQL or file-write failure.
                    // Only report success when a complete JSON cache file was actually replaced.
                    $cache_written = !empty(self::$database_cache_write_results)
                        && !in_array(false, self::$database_cache_write_results, true);

                    if (!$cache_written) {
                        $report['success'] = false;
                    }

                    $report['jobs'][$signature] = [
                        'status' => $cache_written ? 'ok' : 'error',
                        'cache_written' => $cache_written,
                        'error' => $cache_written ? null : 'cache_not_written',
                        'method' => $method,
                        'args' => $args,
                        'file' => $file,
                        'records' => $records,
                        'time_ms' => $time,
                    ];


                    self::debug_log(
                        __FUNCTION__ .
                        ($cache_written ? ': OK → ' : ': CACHE NOT WRITTEN → ') .
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

            // Restore normal frontend cache behaviour.
            self::$database_cache_force_refresh = false;
            self::$database_cache_write_results = [];


            // Restore original HTTP_HOST.
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
}
