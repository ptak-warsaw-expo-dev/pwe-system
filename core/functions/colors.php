<?php

/**
 * Fair colors, Uncode palettes and brightness adjustment.
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Compose fair colors, Uncode palettes and brightness adjustment in PWE_System_Functions.
 *
 * @internal Composed by core/class-pwe-system-functions.php.
 */
trait PWE_System_Functions_Colors {
    /**
     * Resolve the fair accent or secondary color from the Uncode palette.
     */
    public static function pwe_color($color) {
        $fair_colors = self::findPalletColorsStatic();
        $result_color = null;

        // Handling color 'accent'.
        if (strtolower($color) === 'accent' && isset($fair_colors['Accent'])) {
            $result_color = $fair_colors['Accent'];
        }

        // Handling color 'main2'.
        if (strtolower($color) === 'main2') {
            foreach ($fair_colors as $color_key => $color_value) {
                if (strpos(strtolower($color_key), 'main2') !== false) {
                    $result_color = $color_value;
                    break;
                }
            }
        }

        return $result_color;
    }

    /**
     * Select the first available primary, secondary or default color.
     */
    public static function findColor($primary, $secondary, $default = '') {
        if ($primary != '') {
            return $primary;
        } elseif ($secondary != '') {
            return $secondary;
        } else {
            return $default;
        }
    }

    /**
     * Read the Uncode color palette using the static helper API.
     */
    public static function findPalletColorsStatic() {
        $uncode_options = get_option('uncode');
        $accent_uncode_color = $uncode_options["_uncode_accent_color"];
        $custom_element_colors = array();

        if (isset($uncode_options["_uncode_custom_colors_list"]) && is_array($uncode_options["_uncode_custom_colors_list"])) {
            $custom_colors_list = $uncode_options["_uncode_custom_colors_list"];

            foreach ($custom_colors_list as $color) {
                $title = $color['title'];
                $color_value = $color["_uncode_custom_color"];
                $color_id = $color["_uncode_custom_color_unique_id"];

                if ($accent_uncode_color != $color_id) {
                    $custom_element_colors[$title] = $color_value;
                } else {
                    $accent_color_value = $color_value;
                    $custom_element_colors = array_merge(array('Accent' => $accent_color_value), $custom_element_colors);
                }
            }
            $custom_element_colors = array_merge(array('Default' => ''), $custom_element_colors);
        }
        return $custom_element_colors;
    }

    /**
     * Read the Uncode color palette using the instance helper API.
     */
    public function findPalletColors() {
        $uncode_options = get_option('uncode');
        $accent_uncode_color = $uncode_options["_uncode_accent_color"];
        $custom_element_colors = array();

        if (isset($uncode_options["_uncode_custom_colors_list"]) && is_array($uncode_options["_uncode_custom_colors_list"])) {
            $custom_colors_list = $uncode_options["_uncode_custom_colors_list"];

            foreach ($custom_colors_list as $color) {
                $title = $color['title'];
                $color_value = $color["_uncode_custom_color"];
                $color_id = $color["_uncode_custom_color_unique_id"];

                if ($accent_uncode_color != $color_id) {
                    $custom_element_colors[$title] = $color_value;
                } else {
                    $accent_color_value = $color_value;
                    $custom_element_colors = array_merge(array('Accent' => $accent_color_value), $custom_element_colors);
                }
            }
            $custom_element_colors = array_merge(array('Default' => ''), $custom_element_colors);
        }
        return $custom_element_colors;
    }

    /**
     * Adjust the RGB channels of a hexadecimal color within the valid range.
     */
    public static function adjustBrightness($hex, $steps) {
        // Convert hex to RGB.
        $hex = str_replace('#', '', $hex);
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        // Shift RGB values.
        $r = max(0, min(255, $r + $steps));
        $g = max(0, min(255, $g + $steps));
        $b = max(0, min(255, $b + $steps));

        // Convert RGB back to hex.
        return '#' . str_pad(dechex($r), 2, '0', STR_PAD_LEFT)
            . str_pad(dechex($g), 2, '0', STR_PAD_LEFT)
            . str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
    }
}
