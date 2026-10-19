<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/core/wpml-gateway.php';
require_once __DIR__ . '/core/page-locator.php';
require_once __DIR__ . '/core/page-meta-adapter.php';
require_once __DIR__ . '/core/language-helper.php';
require_once __DIR__ . '/core/replace-content-config.php';
require_once __DIR__ . '/core/replace-content-service.php';
require_once __DIR__ . '/core/ajax-actions.php';
require_once __DIR__ . '/core/admin-page.php';

final class PWE_System_Replace_Content
{
    private static bool $initialized = false;

    public static function init(): void
    {
        if (self::$initialized) {
            return;
        }
        self::$initialized = true;
        PWE_System_Replace_Content_Ajax::init();
        add_action('wp_loaded', [self::class, 'auto_replace_layout'], 30); // TEMPORARY <--------------------------------------<
    }

    /**
     * Replace pending layout pages before the frontend page query is executed.
     */
    public static function auto_replace_layout(): void // TEMPORARY <-----------------------------------------------------------<
    {
        if (
            is_admin()
            || wp_doing_ajax()
            || wp_doing_cron()
            || (defined('WP_CLI') && WP_CLI)
            || (defined('REST_REQUEST') && REST_REQUEST)
            || (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST)
        ) {
            return;
        }

        global $wpdb;
        $lock_name = '';

        try {
            $map = PWE_System_Replace_Content_Config::get_map();
            if (empty($map['/layout/'])) {
                return;
            }

            // Use the same inspection and WPML translation discovery as the admin tool.
            $plan = PWE_System_Replace_Content_Service::build_plan([
                '/layout/' => $map['/layout/'],
            ]);
            $job = PWE_System_Replace_Content_Service::prepare_job($plan);

            // Already compliant or missing pages require no writes or cache clearing.
            if (empty($job['tasks'])) {
                return;
            }

            // Serialize simultaneous visits without persisting a completion flag.
            $candidate = 'pwe_auto_layout_' . md5(DB_NAME . '|' . $wpdb->prefix);
            $acquired = $wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, 0)', $candidate));
            if ((string) $acquired !== '1') {
                return;
            }
            $lock_name = $candidate;

            // process_page() rechecks the current state before each mutation.
            $result = PWE_System_Replace_Content_Service::execute($plan);
            foreach ((array) ($result['errors'] ?? []) as $error) {
                error_log('[PWE System] Automatic layout replacement: ' . (string) $error);
            }
        } catch (Throwable $error) {
            error_log('[PWE System] Automatic layout replacement failed: ' . $error->getMessage());
        } finally {
            if ($lock_name !== '') {
                $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)', $lock_name));
            }
        }
    }

    public static function render_admin_page(): void
    {
        PWE_System_Replace_Content_Admin_Page::render();
    }
}
