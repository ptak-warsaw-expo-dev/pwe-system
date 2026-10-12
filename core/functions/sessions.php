<?php

/**
 * Registration page detection for session handling.
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Compose registration page detection for session handling in PWE_System_Functions.
 *
 * @internal Composed by core/class-pwe-system-functions.php.
 */
trait PWE_System_Functions_Sessions {
    /**
     * Check whether the request is an AJAX request or a registration session page.
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

            $default_language = apply_filters('wpml_default_language', null);

            $allowed_paths = [
                '/potwierdzenie-rejestracji',
            ];

            if ($default_language && $default_language !== 'pl') {
                $allowed_paths[] = '/pl/potwierdzenie-rejestracji';
            }

            // Read the URL map from PWE Multilang.
            $json_file = WP_PLUGIN_DIR . '/pwe-multilang/website-translation.json';

            if (file_exists($json_file)) {
                $json_content = file_get_contents($json_file);
                $translations = json_decode($json_content, true);

                if (is_array($translations)) {
                    // Include the second registration step in the session page keys.
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
                }
            }
        }

        $current_path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
        $current_path = '/' . trim($current_path, '/') . '/';

        foreach ($allowed_paths as $path) {
            $normalized_path = '/' . trim($path, '/') . '/';

            // Match the normalized path exactly.
            if ($current_path === $normalized_path) {
                return true;
            }

            // Match translated paths that end with the normalized registration path.
            if ($normalized_path !== '/' && str_ends_with($current_path, $normalized_path)) {
                return true;
            }
        }

        return false;
    }
}
