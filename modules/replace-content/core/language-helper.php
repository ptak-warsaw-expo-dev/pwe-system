<?php

if (!defined('ABSPATH')) {
    exit;
}

final class PWE_System_Replace_Content_Language_Helper
{
    public static function get_active_languages(): array
    {
        return PWE_System_Replace_Content_Wpml_Gateway::get_active_languages();
    }

    public static function get_language_label(string $lang_code, array $active_languages): string
    {
        if (!empty($active_languages[$lang_code]['translated_name'])) {
            return (string) $active_languages[$lang_code]['translated_name'];
        }
        if (!empty($active_languages[$lang_code]['native_name'])) {
            return (string) $active_languages[$lang_code]['native_name'];
        }
        if (!empty($active_languages[$lang_code]['display_name'])) {
            return (string) $active_languages[$lang_code]['display_name'];
        }
        return strtoupper($lang_code);
    }

    public static function get_flag_url(string $lang_code, array $active_languages): string
    {
        if (!empty($active_languages[$lang_code]['country_flag_url'])) {
            return (string) $active_languages[$lang_code]['country_flag_url'];
        }
        if (defined('ICL_PLUGIN_URL')) {
            return ICL_PLUGIN_URL . '/res/flags/' . sanitize_key($lang_code) . '.png';
        }
        return '';
    }
}
