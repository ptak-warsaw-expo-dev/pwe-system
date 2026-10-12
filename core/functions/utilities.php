<?php

/**
 * Content decoding, random identifiers and mobile user-agent detection.
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Compose content decoding, random identifiers and mobile user-agent detection in PWE_System_Functions.
 *
 * @internal Composed by core/class-pwe-system-functions.php.
 */
trait PWE_System_Functions_Utilities {
    /**
     * Generate a random five-digit identifier.
     */
    public static function id_rnd() {
        $id_rnd = rand(10000, 99999);
        return $id_rnd;
    }

    /**
     * Decode Base64 and URL-encoded content, then apply shortcode paragraph cleanup.
     */
    public static function decode_clean_content($encoded_content) {
        $decoded_content = wpb_js_remove_wpautop(urldecode(base64_decode($encoded_content)), true);
        return $decoded_content;
    }

    /**
     * Decode URL-encoded JSON into a PHP value.
     */
    public static function json_decode($encoded_variable) {
        $encoded_variable_urldecode = urldecode($encoded_variable);
        $encoded_variable_json = json_decode($encoded_variable_urldecode, true);
        return $encoded_variable_json;
    }

    /**
     * Check the user agent for the supported mobile device markers.
     */
    public static function checkForMobile() {
        return (preg_match('/Mobile|Android|iPhone/i', $_SERVER['HTTP_USER_AGENT']));
    }
}
