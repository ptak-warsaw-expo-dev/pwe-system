<?php
if (!defined('ABSPATH')) { exit; }

require_once __DIR__ . '/core/templates.php';
require_once __DIR__ . '/core/generator.php';
require_once __DIR__ . '/core/admin.php';

/** WordPress integration and automatic generation lifecycle. */
final class PWE_Images_Module {
    public static function init(): void {
        // Automatic generation is temporarily disabled; API and admin generation remain available.
        // Keep these hooks for a future reactivation of the automatic lifecycle.
        // add_action('template_redirect', [self::class, 'automatic'], 20);
        // add_action('pwe_transients_cleared', [self::class, 'refresh']);
        add_action('admin_menu', [self::class, 'register_menu']);
        add_action('admin_post_pwe_images_generate', [PWE_Images_Admin::class, 'submit']);
        add_action('admin_post_pwe_images_preview', [PWE_Images_Admin::class, 'preview']);
    }

    public static function register_menu(): void {
        add_submenu_page(
            'pwe-system', 'Images Generator', 'Images Generator',
            'manage_options', 'pwe-images-generator', [PWE_Images_Admin::class, 'page']
        );
    }

    public static function refresh(): void {
        // Also invalidate object cache after the legacy SQL transient cleanup.
        delete_transient(PWE_Images_Generator::CHECKED);
        self::automatic();
    }

    public static function automatic(): void {
        if (false !== get_transient(PWE_Images_Generator::CHECKED)) { return; }
        PWE_Images_Generator::generate(false);
    }
}

PWE_Images_Module::init();
