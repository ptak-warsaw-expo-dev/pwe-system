<?php

/**
 * Element paths, asset loading and component rendering.
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Compose element paths, asset loading and component rendering in PWE_System_Functions.
 *
 * @internal Composed by core/class-pwe-system-functions.php.
 */
trait PWE_System_Functions_Elements {
    /**
     * Resolve the base directory of PWE Elements AutoSwitch.
     */
    private static function elements_plugin_path() {
        if (defined('PWE_PLUGIN_PATH') && is_dir(PWE_PLUGIN_PATH)) {
            return trailingslashit(PWE_PLUGIN_PATH);
        }

        $path = trailingslashit(WP_PLUGIN_DIR) . 'pwe-elements-auto-switch/';

        return trailingslashit(apply_filters('pwe_system_elements_plugin_path', $path));
    }

    /**
     * Resolve the base URL of PWE Elements AutoSwitch.
     */
    private static function elements_plugin_url() {
        if (defined('PWE_PLUGIN_FILE') && is_file(PWE_PLUGIN_FILE)) {
            return trailingslashit(plugin_dir_url(PWE_PLUGIN_FILE));
        }

        $url = trailingslashit(plugins_url('pwe-elements-auto-switch'));

        return trailingslashit(apply_filters('pwe_system_elements_plugin_url', $url));
    }

    /**
     * Enqueue the CSS and JavaScript assets of an element.
     */
    public static function assets_per_element($element_slug, $element_type = 'main', $folder = 'elements') {
        $src = $folder . '/' . (!empty($element_type) ? $element_type . '/' : '') . $element_slug . '/assets/';

        $base_dir = self::elements_plugin_path() . $src;
        $base_url = self::elements_plugin_url() . $src;

        $el_name = 'el=' . urlencode($element_slug);

        // Enqueue the stylesheet when it exists.
        if (file_exists($base_dir . 'style.css')) {
            $version = filemtime($base_dir . 'style.css');
            wp_enqueue_style(
                'pwe-' . $element_slug . '-style',
                $base_url . 'style.css?' . $el_name,
                [],
                $version
            );
        }

        // Enqueue the script when it exists.
        if (file_exists($base_dir . 'script.js')) {
            $version = filemtime($base_dir . 'script.js');
            wp_enqueue_script(
                'pwe-' . $element_slug . '-script',
                $base_url . 'script.js?' . $el_name,
                ['jquery'],
                $version,
                true
            );
        }
    }

    /**
     * Enqueue preset assets and expose optional element attributes to JavaScript.
     */
    public static function assets_per_group($element_slug, $group, $element_type = 'main', $folder = 'elements', $atts = null) {
        $src = $folder . '/' . (!empty($element_type) ? $element_type . '/' : '') . $element_slug . '/presets/' . $group . '/assets/';

        $base_dir = self::elements_plugin_path() . $src;
        $base_url = self::elements_plugin_url() . $src;

        $el_name = 'el=' . urlencode($element_slug);
        $el_group = 'gr=' . urlencode($group);

        // Enqueue the stylesheet when it exists.
        if (file_exists($base_dir . 'style.css')) {
            $version = filemtime($base_dir . 'style.css');
            wp_enqueue_style(
                'pwe-' . $element_slug . '-' . $group . '-style',
                $base_url . 'style.css?' . $el_name . '&' . $el_group,
                [],
                $version
            );
        }

        // Enqueue the script when it exists.
        if (file_exists($base_dir . 'script.js')) {
            $handle = 'pwe-' . $element_slug . '-' . $group . '-script';
            $version = filemtime($base_dir . 'script.js');

            wp_enqueue_script(
                $handle,
                $base_url . 'script.js?' . $el_name . '&' . $el_group,
                ['jquery'],
                $version,
                true
            );
            if (!empty($atts)) {
                wp_localize_script($handle, 'pwe_element_atts', $atts);
            }
        }
    }

    /**
     * Load and render a registered PWE component.
     */
    public static function render_component($slug, $group = 'all', $params = []) {
        $components = PWE_Elements_Data::get_all_components();

        if (!isset($components[$slug])) {
            return '';
        }

        $component = $components[$slug];
        $class     = $component['class'];
        $file      = self::elements_plugin_path() . $component['file'];

        if (!class_exists($class)) {
            if (!file_exists($file)) {
                return '';
            }

            require_once $file;
        }

        if (!class_exists($class) || !method_exists($class, 'render')) {
            return '';
        }

        ob_start();
        $class::render($group, $params);
        return ob_get_clean();
    }
}
