<?php

/**
 * Exhibitor catalogs, logo deduplication and image file discovery.
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Compose exhibitor catalogs, logo deduplication and image file discovery in PWE_System_Functions.
 *
 * @internal Composed by core/class-pwe-system-functions.php.
 */
trait PWE_System_Functions_Media {
    /**
     * Read exhibitor logos from the current or legacy local catalog.
     *
     * Prefer pwe-exhibitors.json over the legacy catalog and deduplicate company names.
     *
     * @param int $count Maximum number of logos; a falsey value disables the limit.
     * @param bool $shuffle Whether to randomize the logo order.
     * @return array Exhibitor logo records.
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

            // Read the current exhibitor catalog first (pwe-exhibitors.json).
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

            // Use the legacy exhibitor catalog when the current catalog is not configured.
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

            // Apply the requested order and limit to the logos.

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
     * Deduplicate logos by partner type and image filename.
     */
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
     * Return the first available logo image for the locale and color preference.
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
     * Collect image paths from the primary or fallback directory.
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
     * Resolve the first available WebP, JPEG or PNG file for a path.
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
}
