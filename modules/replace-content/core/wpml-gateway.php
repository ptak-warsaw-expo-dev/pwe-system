<?php

if (!defined('ABSPATH')) {
    exit;
}

final class PWE_System_Replace_Content_Wpml_Gateway
{
    public static function is_active(): bool
    {
        return defined('ICL_SITEPRESS_VERSION');
    }

    public static function get_active_languages(): array
    {
        $languages = apply_filters('wpml_active_languages', null, ['skip_missing' => 0]);
        return is_array($languages) ? $languages : [];
    }

    public static function get_element_type(string $post_type = 'page'): string
    {
        return (string) apply_filters('wpml_element_type', $post_type);
    }

    public static function get_trid(int $post_id, string $element_type): int
    {
        return (int) apply_filters('wpml_element_trid', null, $post_id, $element_type);
    }

    public static function get_element_translations(int $trid, string $element_type): array
    {
        if ($trid <= 0) {
            return [];
        }
        $translations = apply_filters('wpml_get_element_translations', [], $trid, $element_type);
        return is_array($translations) ? $translations : [];
    }

    public static function get_element_language(int $post_id, string $element_type): string
    {
        $details = apply_filters('wpml_element_language_details', null, [
            'element_id' => $post_id,
            'element_type' => $element_type,
        ]);

        if (is_object($details)) {
            return (string) ($details->language_code ?? '');
        }
        if (is_array($details)) {
            return (string) ($details['language_code'] ?? '');
        }
        return '';
    }
}
