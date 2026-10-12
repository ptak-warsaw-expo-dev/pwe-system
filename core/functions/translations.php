<?php

/**
 * Translation context, JSON translations, locale and URL maps.
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Compose translation context, JSON translations, locale and URL maps in PWE_System_Functions.
 *
 * @internal Composed by core/class-pwe-system-functions.php.
 */
trait PWE_System_Functions_Translations {
    /**
     * Current element, preset and type used to resolve translations.
     */
    private static $translation_context = [
        'element_slug' => null,
        'group' => null,
        'element_type' => 'main'
    ];

    /**
     * Set the element, preset and type used to resolve translations.
     */
    public static function set_translation_context($element_slug, $group, $element_type = 'main') {
        self::$translation_context['element_slug'] = $element_slug;
        self::$translation_context['group'] = $group;
        self::$translation_context['element_type'] = $element_type;
    }

    /**
     * Loaded translations indexed by element type, slug and preset.
     */
    private static $translation_cache = [];

    /**
     * Resolve a translation using the current context and locale.
     *
     * Preset translations override shared element translations by locale.
     * Missing locale entries fall back to en_US; missing keys return the key.
     */
    public static function multi_translation($key) {
        $ctx = self::$translation_context;

        if (empty($ctx['element_slug']) || empty($ctx['group'])) {
            return $key;
        }

        $locale = get_locale();

        // Cache depends on the element and preset because preset translations can override
        // translations from the main assets directory.
        $cache_key = implode('/', [
            $ctx['element_type'],
            $ctx['element_slug'],
            $ctx['group'],
        ]);

        if (!isset(self::$translation_cache[$cache_key])) {
            // Determine the main element directory.
            //
            // Components: components/{element-slug}/.
            //
            // Other elements: elements/{element-type}/{element-slug}/.
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

            // Merge recursively by locale.
            //
            // Values from the preset file override values from the global file.
            self::$translation_cache[$cache_key] = array_replace_recursive(
                $global_translations,
                $preset_translations
            );
        }

        $translations_data = self::$translation_cache[$cache_key];

        // First use the current locale. If it does not exist, fall back to en_US.
        $map = $translations_data[$locale]
            ?? $translations_data['en_US']
            ?? [];

        return $map[$key] ?? $key;
    }

    /**
     * Read and decode a translation JSON file.
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

    /**
     * Return website URL map files in source priority order.
     *
     * PWE Multilang is the primary source; the PWE System copy is a fallback.
     */
    public static function get_website_translation_files() {
        return [
            trailingslashit(WP_PLUGIN_DIR) . 'pwe-multilang/website-translation.json',
            PWE_SYSTEM_PATH . 'data/website-translation.json',
        ];
    }

    /**
     * Resolve the current language code using WPML or the WordPress locale.
     */
    public static function lang() {

        // Resolve the language through WPML first.
        if (defined('ICL_LANGUAGE_CODE') && !empty(ICL_LANGUAGE_CODE)) {
            return strtolower(ICL_LANGUAGE_CODE);
        }

        if (function_exists('apply_filters')) {
            $wpml_lang = apply_filters('wpml_current_language', null);
            if (!empty($wpml_lang)) {
                return strtolower($wpml_lang);
            }
        }

        // Fall back to the WordPress locale.
        $lang = get_locale(); // For example, "en_US", "pl_PL" or "de_DE".
        $lang = strtolower(str_replace('-', '_', $lang));

        return strtolower(substr($lang, 0, 2));
    }

    /**
     * Check whether the current WordPress locale is Polish.
     */
    public static function lang_pl() {
        return get_locale() == 'pl_PL';
    }

    /**
     * Select text using the existing Polish, German or English locale rules.
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
}
