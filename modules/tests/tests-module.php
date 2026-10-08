<?php

if (!defined('ABSPATH')) {
    exit;
}

// final class PWE_System_Tests
// {
//     public const PAGE_SLUG = 'pwe-system-tests';

//     public static function init(): void
//     {
//         self::includes();

//         PWE_System_Tests_Runner::init();
//         PWE_System_Tests_Report::init();
//     }

//     private static function includes(): void
//     {
//         $dir = __DIR__ . '/';

//         require_once $dir . 'checks/qr-feeds.php';
//         require_once $dir . 'checks/translations-consistency.php';
//         require_once $dir . 'checks/notification-placeholders.php';
//         require_once $dir . 'checks/notification-links.php';
//         require_once $dir . 'checks/page-health.php';
//         require_once $dir . 'checks/notification-languages.php';
//         require_once $dir . 'checks/notification-merge-tags.php';
//         require_once $dir . 'checks/confirmation-merge-tags.php';
//         require_once $dir . 'checks/conditional-logic-field-ids.php';
//         require_once $dir . 'core/diagnostic-report.php';
//         require_once $dir . 'core/diagnostic-runner.php';
//         require_once $dir . 'admin/admin-icons.php';
//         require_once $dir . 'admin/result-modal.php';
//         require_once $dir . 'admin/admin-page.php';
//     }

//     /** Run every check (including all Page Health batches) and persist the final report. */
//     public static function run_all(): array
//     {
//         $results = PWE_System_Tests_Runner::run_all();
//         PWE_System_Tests_Report::save($results, PWE_System_Tests_Runner::definitions());
//         return $results;
//     }
//     public static function group(): string
//     {
//         return class_exists('PWE_Multilang_Site_Group') ? PWE_Multilang_Site_Group::label() : 'site-' . get_current_blog_id();
//     }

//     public static function render_admin_page(): void
//     {
//         PWE_System_Tests_Admin::render();
//     }
// }

final class PWE_System_Tests
{
    public const PAGE_SLUG = 'pwe-system-tests';
    private const MIN_MULTILANG_VERSION = '1.1.5';

    private static bool $initialized = false;
    private static ?bool $available = null;

    public static function is_available(): bool
    {
        if (self::$available !== null) {
            return self::$available;
        }

        self::$available = false;

        // Ensure that the PWE Multilang plugin is installed and active
        if (!defined('PWE_MULTILANG_PATH')) {
            return false;
        }

        if (!function_exists('get_plugins') || !function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        foreach (get_plugins('pwe-multilang') as $file => $data) {
            // is_plugin_active() uwzględnia również aktywację sieciową.
            if (is_plugin_active('pwe-multilang/' . $file)
                && !empty($data['Version'])
                && version_compare((string) $data['Version'], self::MIN_MULTILANG_VERSION, '>=')) {
                self::$available = true;
                break;
            }
        }

        return self::$available;
    }

    public static function init(): void
    {
        if (self::$initialized || !self::is_available()) {
            return;
        }

        self::$initialized = true;
        self::includes();

        PWE_System_Tests_Runner::init();
        PWE_System_Tests_Report::init();
    }

    private static function includes(): void
    {
        $dir = __DIR__ . '/';

        require_once $dir . 'checks/qr-feeds.php';
        require_once $dir . 'checks/translations-consistency.php';
        require_once $dir . 'checks/notification-placeholders.php';
        require_once $dir . 'checks/notification-links.php';
        require_once $dir . 'checks/page-health.php';
        require_once $dir . 'checks/notification-languages.php';
        require_once $dir . 'checks/notification-merge-tags.php';
        require_once $dir . 'checks/confirmation-merge-tags.php';
        require_once $dir . 'checks/conditional-logic-field-ids.php';
        require_once $dir . 'core/diagnostic-report.php';
        require_once $dir . 'core/diagnostic-runner.php';
        require_once $dir . 'admin/admin-icons.php';
        require_once $dir . 'admin/result-modal.php';
        require_once $dir . 'admin/admin-page.php';
    }

    /** Run every check (including all Page Health batches) and persist the final report. */
    public static function run_all(): array
    {
        if (!self::is_available()) {
            return [];
        }

        self::init();
        $results = PWE_System_Tests_Runner::run_all();
        PWE_System_Tests_Report::save($results, PWE_System_Tests_Runner::definitions());
        return $results;
    }
    public static function group(): string
    {
        return class_exists('PWE_Multilang_Site_Group') ? PWE_Multilang_Site_Group::label() : 'site-' . get_current_blog_id();
    }

    public static function render_admin_page(): void
    {
        if (!self::is_available()) {
            wp_die(esc_html__('Moduł Testy wymaga aktywnej wtyczki PWE Multilang w wersji 1.1.5 lub nowszej.', 'pwe-system'));
        }

        self::init();
        PWE_System_Tests_Admin::render();
    }
}
