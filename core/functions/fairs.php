<?php

/**
 * Fair data normalization, translated fields and combined fair JSON.
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Compose fair data normalization, translated fields and combined fair JSON in PWE_System_Functions.
 *
 * @internal Composed by core/class-pwe-system-functions.php.
 */
trait PWE_System_Functions_Fairs {
    /**
     * Normalize a CAP fair record into the public fair data structure.
     */
    public static function generate_fair_data($fair) {
        // Decode JSON estimations.
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
            'about_desc_en' => $fair->about_desc_en ?? '',
            'available_languages' => $fair->available_languages ?? ''
        ];

        // Add estimations to data.
        if (!empty($estimations)) {
            foreach ($estimations as $key => $val) {
                $data[$key] = $val;
            }
        }

        return $data;
    }

    /**
     * Normalize translated fields for the active or fallback languages.
     */
    public static function generate_fair_translation_data($fair) {
        $languages = apply_filters('wpml_active_languages', null);

        // Use the fallback language list when WPML does not provide active languages.
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
     * Combine CAP fair data and translations, with the existing remote JSON fallback.
     */
    public static function json_fairs() {
        static $runtime_cache = null;

        if ($runtime_cache !== null) {
            return $runtime_cache;
        }

        $pwe_fairs = self::get_database_fairs_data();
        $pwe_fairs_desc_translations = self::get_database_translations_data();

        static $console_logged = false;

        // Check if data is already in global variable.
        if (!empty($pwe_fairs) && is_array($pwe_fairs)) {
            global $fairs_data;
            $fairs_data = ["fairs" => []];

            // Add data about the fair.
            foreach ($pwe_fairs as $fair) {
                if (!isset($fair->fair_domain) || empty($fair->fair_domain)) {
                    continue;
                }

                $domain = $fair->fair_domain;

                // Save data about the fair in the table.
                $fairs_data["fairs"][$domain] = self::generate_fair_data($fair);
            }

            // Add translations to the fair data.
            foreach ($pwe_fairs_desc_translations as $translation) {
                if (!isset($translation['fair_domain']) || empty($translation['fair_domain'])) {
                    continue;
                }

                $domain = $translation['fair_domain'];

                $translation_data = self::generate_fair_translation_data($translation);

                // Merge data.
                if (isset($fairs_data["fairs"][$domain])) {
                    $fairs_data["fairs"][$domain] = array_merge(
                        $fairs_data["fairs"][$domain],
                        $translation_data
                    );
                }
            }

        } else {
            // URL to JSON file.
            $json_file = 'https://mr.glasstec.pl/doc/pwe-data.json';

            // Getting data from JSON file.
            $json_data = @file_get_contents($json_file); // Use @ to ignore PHP warnings on failure.

            // Checking if data has been downloaded.
            if ($json_data === false) {
                if (current_user_can("administrator") && !is_admin()) {
                    echo '<script>console.error("Failed to fetch data from JSON file: ' . $json_file . '")</script>';
                }
                return null;
            }

            global $fairs_data;
            // Decoding JSON data.
            $fairs_data = json_decode($json_data, true);

            // Checking JSON decoding correctness.
            if (json_last_error() !== JSON_ERROR_NONE) {
                if (current_user_can("administrator") && !is_admin()) {
                    echo '<script>console.error("Error decoding JSON: ' . json_last_error_msg() . '")</script>';
                }
                return null;
            }

            // Checking if the data has the correct structure.
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
}
