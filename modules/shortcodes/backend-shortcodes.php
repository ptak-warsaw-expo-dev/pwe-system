<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Retrieve fair data for the requested domain.
 *
 * Database getters manage their own persistent caches.
 */
function get_fair_data($specific_domain = null) {
    // Resolve the domain before selecting database records.
    $current_domain = $specific_domain ?: ($_SERVER['HTTP_HOST'] ?? '');

    if ($current_domain === '') {
        $current_domain = parse_url(home_url(), PHP_URL_HOST) ?: '';
    }

    $current_domain = preg_replace(
        '/^www\./i',
        '',
        strtolower(trim((string) $current_domain))
    );

    if ($current_domain === '') {
        return null;
    }

    // Keep a separate request cache for each requested domain.
    static $cached_domains = [];

    if (!array_key_exists($current_domain, $cached_domains)) {
        $cached_domains[$current_domain] = null;
    }

    $cached_data =& $cached_domains[$current_domain];

    if ($cached_data === null) {
        // Fetch records directly by domain, without the calendar date window.
        $pwe_fairs = PWE_Functions::get_database_fairs_data($current_domain);

        // Cache empty results to avoid repeated processing in this request.
        if (empty($pwe_fairs) || !is_array($pwe_fairs)) {
            $cached_data = [];
            return null;
        }

        $pwe_fairs_desc_translations =
            PWE_Functions::get_database_translations_data($current_domain);

        global $fairs_data;
        $fairs_data = ['fairs' => []];

        // Build the base fair records.
        foreach ($pwe_fairs as $fair) {
            if (empty($fair->fair_domain)) {
                continue;
            }

            $domain = $fair->fair_domain;

            $fairs_data['fairs'][$domain] =
                PWE_Functions::generate_fair_data($fair);
        }

        // Merge translated fields into the matching fair records.
        foreach ($pwe_fairs_desc_translations as $translation) {
            if (empty($translation['fair_domain'])) {
                continue;
            }

            $domain = $translation['fair_domain'];

            if (!isset($fairs_data['fairs'][$domain])) {
                continue;
            }

            $translation_data =
                PWE_Functions::generate_fair_translation_data($translation);

            $fairs_data['fairs'][$domain] = array_merge(
                $fairs_data['fairs'][$domain],
                $translation_data
            );
        }

        $cached_data = [];

        // Normalize domain keys consistently with the lookup domain.
        foreach ($fairs_data['fairs'] as $fair) {
            if (empty($fair['domain'])) {
                continue;
            }

            $domain_key = preg_replace(
                '/^www\./i',
                '',
                strtolower(trim((string) $fair['domain']))
            );

            $cached_data[$domain_key] = $fair;
        }
    }

    return $cached_data[$current_domain] ?? null;
}

/**
 * Map shortcode names to fair data fields.
 */
function pwe_get_shortcode_map() {
    $shortcodes = [
        'pwe_name_pl'               => 'name_pl',
        'pwe_name_en'               => 'name_en',
        'pwe_desc_pl'               => 'desc_pl',
        'pwe_desc_en'               => 'desc_en',
        'pwe_fair_id'               => 'id',
        'pwe_short_desc_pl'         => 'short_desc_pl',
        'pwe_short_desc_en'         => 'short_desc_en',
        'pwe_full_desc_pl'          => 'full_desc_pl',
        'pwe_full_desc_en'          => 'full_desc_en',
        'pwe_date_start'            => 'date_start',
        'pwe_date_start_hour'       => 'date_start_hour',
        'pwe_date_end'              => 'date_end',
        'pwe_date_end_hour'         => 'date_end_hour',
        'pwe_edition'               => 'edition',
        'pwe_visitors'              => 'fair_visitors_current',
        'pwe_visitors_foreign'      => 'fair_foreign_current',
        'pwe_exhibitors'            => 'fair_exhibitors_current',
        'pwe_countries'             => 'fair_countries_current',
        'pwe_area'                  => 'fair_area_current',
        'pwe_statistics_year_curr'  => 'fair_year_current',
        'pwe_visitors_prev'         => 'fair_visitors_previous',
        'pwe_visitors_foreign_prev' => 'fair_foreign_previous',
        'pwe_exhibitors_prev'       => 'fair_exhibitors_previous',
        'pwe_countries_prev'        => 'fair_countries_previous',
        'pwe_area_prev'             => 'fair_area_previous',
        'pwe_statistics_year_prev'  => 'fair_year_previous',
        'pwe_hall'                  => 'hall',
        'pwe_hall_entrance'         => 'hall_entrance',
        'pwe_color_accent'          => 'color_accent',
        'pwe_color_main2'           => 'color_main2',
        'pwe_badge'                 => 'badge',
        'pwe_facebook'              => 'facebook',
        'pwe_instagram'             => 'instagram',
        'pwe_linkedin'              => 'linkedin',
        'pwe_youtube'               => 'youtube',
        'pwe_catalog'               => 'catalog',
        'pwe_catalog_id'            => 'catalog_id',
        'pwe_catalog_archive'       => 'catalog_archive',
        'pwe_catalog_id_archive'    => 'catalog_id_archive',
        'pwe_catalog_type'          => 'catalog_type',
        'pwe_catalog_numbers'       => 'catalog_numbers',
        'pwe_catalog_featured'      => 'catalog_featured',
        'pwe_category_pl'           => 'category_pl',
        'pwe_category_en'           => 'category_en',
        'pwe_industry'              => 'industry',
        'pwe_group'                 => 'group',
        'pwe_conference_name'       => 'conference_name',
        'pwe_conference_title_pl'   => 'conference_title_pl',
        'pwe_conference_title_en'   => 'conference_title_en',
        'pwe_conference_desc_pl'    => 'conference_desc_pl',
        'pwe_conference_desc_en'    => 'conference_desc_en',
        'pwe_about_title_pl'        => 'about_title_pl',
        'pwe_about_title_en'        => 'about_title_en',
        'pwe_about_desc_pl'         => 'about_desc_pl',
        'pwe_about_desc_en'         => 'about_desc_en',
        'pwe_available_languages'   => 'available_languages',
    ];

    // Use active WPML languages when available.
    $languages = apply_filters('wpml_active_languages', null);
    $langs = [];

    if (!empty($languages) && is_array($languages)) {
        foreach ($languages as $lang) {
            $code = $lang['language_code'];

            if (in_array($code, ['pl', 'en'], true)) {
                continue;
            }

            $langs[] = $code;
        }
    } else {
        $langs = [
            'cs',
            'de',
            'it',
            'lt',
            'lv',
            'ru',
            'sk',
            'uk',
            'et',
            'es',
            'fr',
            'ro',
            'hu',
            'nl',
        ];
    }

    // Register language-specific field mappings.
    foreach ($langs as $code) {
        $shortcodes["pwe_name_{$code}"] = "name_{$code}";
        $shortcodes["pwe_desc_{$code}"] = "desc_{$code}";
        $shortcodes["pwe_short_desc_{$code}"] = "short_desc_{$code}";
        $shortcodes["pwe_full_desc_{$code}"] = "full_desc_{$code}";
        $shortcodes["pwe_conference_title_{$code}"] = "conference_title_{$code}";
        $shortcodes["pwe_conference_desc_{$code}"] = "conference_desc_{$code}";
        $shortcodes["pwe_about_title_{$code}"] = "about_title_{$code}";
        $shortcodes["pwe_about_desc_{$code}"] = "about_desc_{$code}";
        $shortcodes["pwe_category_{$code}"] = "category_{$code}";
    }

    return $shortcodes;
}

/**
 * Return a language-specific fair name template.
 *
 * The {name} placeholder may precede or follow the fair descriptor.
 */
function pwe_fair_name_label($lang) {
    $templates = [
        'pl' => 'Targi {name}',
        'en' => 'The {name} fair',
        'de' => 'Die Messe {name}',
        'cs' => 'Veletrh {name}',
        'sk' => 'Veľtrh {name}',
        'es' => 'La feria {name}',
        'et' => 'Mess {name}',
        'fr' => 'Le salon {name}',
        'hu' => 'A {name} vásár',
        'it' => 'La fiera {name}',
        'lt' => 'Paroda {name}',
        'lv' => 'Izstāde {name}',
        'ro' => 'Târgul {name}',
        'uk' => 'Виставка {name}',
        'ru' => 'Выставка {name}',
        'nl' => 'De {name} beurs',
    ];

    $lang = strtolower(
        substr(str_replace('_', '-', trim((string) $lang)), 0, 2)
    );

    return $templates[$lang] ?? $templates['en'];
}

/**
 * Remove a fair descriptor from the beginning or end of a name.
 *
 * Words occurring inside the event name remain unchanged.
 */
function pwe_strip_label($name) {
    $name = trim((string) $name);

    $labels = [
        'Trade fairs',
        'Trade fair',
        'The fair',
        'Fair',
        'Targi',
        'Die Messe',
        'Messe',
        'Veletrh',
        'Veľtrh',
        'La feria',
        'Feria',
        'Mess',
        'Le salon',
        'Salon',
        'La fiera',
        'Fiera',
        'Paroda',
        'Izstāde',
        'Târgul',
        'Târg',
        'Виставка',
        'Выставка',
        'Vásár',
        'De',
        'Beurs',
    ];

    usort($labels, static function ($a, $b) {
        return strlen($b) <=> strlen($a);
    });

    $escaped = array_map(static function ($label) {
        return preg_quote($label, '/');
    }, $labels);

    $alternatives = implode('|', $escaped);

    // Multiple passes handle repeated descriptors.
    for ($i = 0; $i < 3; $i++) {
        $updated = preg_replace(
            '/^(?:' . $alternatives . ')(?:\s+|\s*[:\-–]\s*)/iu',
            '',
            $name,
            1
        );

        if ($updated !== $name) {
            $name = trim($updated);
            continue;
        }

        $updated = preg_replace(
            '/\s+(?:' . $alternatives . ')$/iu',
            '',
            $name,
            1
        );

        if ($updated !== $name) {
            $name = trim($updated);
            continue;
        }

        break;
    }

    return $name;
}

/**
 * Register shortcodes backed by fair data.
 */
function register_dynamic_shortcodes() {
    $shortcodes = pwe_get_shortcode_map();

    foreach ($shortcodes as $shortcode => $field) {
        add_shortcode($shortcode, function ($atts) use ($shortcode, $field) {
            $atts = shortcode_atts(
                [
                    'domain' => null,
                    'label'  => '0',
                ],
                $atts,
                $shortcode
            );

            $fair_data = get_fair_data($atts['domain']);
            $value = $fair_data[$field] ?? '';

            // Add a fair descriptor only when explicitly requested.
            if (
                !preg_match('/^pwe_name_([a-z]{2})$/', $shortcode, $matches)
                || !in_array(
                    strtolower((string) $atts['label']),
                    ['targi_targi', 'true', 'yes'],
                    true
                )
                || !is_string($value)
                || trim($value) === ''
            ) {
                return $value;
            }

            $name = pwe_strip_label($value);

            if ($name === '') {
                return $value;
            }

            return str_replace(
                '{name}',
                $name,
                pwe_fair_name_label($matches[1])
            );
        });
    }
}

add_action('init', 'register_dynamic_shortcodes');

/**
 * Replace Gravity Forms merge tags with fair data.
 *
 * Supported syntax:
 * {pwe_name_pl}
 * {pwe_name_pl:domain=example.com}
 */
function PWE_GF_shortcodes(
    $text,
    $form,
    $entry,
    $url_encode,
    $esc_html,
    $nl2br,
    $format
) {
    $shortcode_map = pwe_get_shortcode_map();

    preg_match_all(
        '/\{(pwe_[a-z0-9_]+)(:domain=([^}]+))?\}/i',
        $text,
        $matches,
        PREG_SET_ORDER
    );

    if (!$matches) {
        return $text;
    }

    foreach ($matches as $match) {
        $full_tag = $match[0];
        $tag_name = $match[1];
        $domain = $match[3] ?? null;

        if (!isset($shortcode_map[$tag_name])) {
            continue;
        }

        $fair_data = get_fair_data($domain);

        if (!$fair_data || !is_array($fair_data)) {
            $text = str_replace($full_tag, '', $text);
            continue;
        }

        $field = $shortcode_map[$tag_name];
        $value = $fair_data[$field] ?? '';

        $text = str_replace($full_tag, esc_html($value), $text);
    }

    return $text;
}

add_filter('gform_replace_merge_tags', 'PWE_GF_shortcodes', 10, 7);