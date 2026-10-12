<?php

/**
 * WPBakery range parameter registration and HTML rendering.
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Compose wPBakery range parameter registration and HTML rendering in PWE_System_Functions.
 *
 * @internal Composed by core/class-pwe-system-functions.php.
 */
trait PWE_System_Functions_WPBakery {
    /**
     * Register the existing WPBakery input range parameter callback.
     */
    public static function inputRange() {
        if (function_exists('vc_add_shortcode_param')) {
            vc_add_shortcode_param('input_range', array('PWEHeader', 'input_range_field_html'));
        }
    }

    /**
     * Render the WPBakery range parameter and its current value.
     */
    public static function input_range_field_html($settings, $value) {
        $id = uniqid('range_');
        return '<div class="pwe-input-range">'
            . '<input type="range" '
            . 'id="' . esc_attr($id) . '" '
            . 'name="' . esc_attr($settings['param_name']) . '" '
            . 'class="wpb_vc_param_value ' . esc_attr($settings['param_name']) . ' ' . esc_attr($settings['type']) . '_field" '
            . 'value="' . esc_attr($value) . '" '
            . 'min="' . esc_attr($settings['min']) . '" '
            . 'max="' . esc_attr($settings['max']) . '" '
            . 'step="' . esc_attr($settings['step']) . '" '
            . 'oninput="document.getElementById(\'value_' . esc_attr($id) . '\').innerHTML = this.value" '
            . '/>'
            . '<span id="value_' . esc_attr($id) . '">' . esc_attr($value) . '</span>'
            . '</div>';
    }
}
