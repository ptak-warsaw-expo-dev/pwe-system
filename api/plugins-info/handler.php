<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Verify the API key provided in the request.
 */
function pwe_plugins_info_authorized(): bool
{
    $expected = defined('PWE_API_KEY_2') ? (string) PWE_API_KEY_2 : (string) getenv('PWE_API_KEY_2');
    $provided = (string) ($_SERVER['HTTP_X_PWE_API_KEY'] ?? '');

    return $expected !== '' && $provided !== '' && hash_equals($expected, $provided);
}

/**
 * Define the plugins monitored by CAP.
 */
function pwe_plugins_info_monitored(): array
{
    return [
        'custom-element' => 'custom-element/custom-element.php',
        'pwe-elements-auto-switch' => 'pwe-elements-auto-switch/pwe-elements-auto-switch.php',
        'pwe-elements' => 'PWElements/pwelements.php',
        'pwe-system' => 'pwe-system/pwe-system.php',
        'pwe-multilang' => 'pwe-multilang/pwe-multilang.php',
        'pwe-qr-gravity-forms' => 'pwe-qr-gravity-forms/pwe-qr-gravity-forms.php',
        'pwe-media' => 'pwe-media/pwe-media.php',
        'gravityforms' => 'gravityforms/gravityforms.php',
    ];
}

/**
 * Set false to disable manual CAP updates for a specific plugin.
 */
function pwe_plugins_info_update_permissions(): array
{
    return [
        'custom-element' => true,
        'pwe-elements-auto-switch' => true,
        'pwe-elements' => true,
        'pwe-system' => true,
        'pwe-multilang' => true,
        'pwe-qr-gravity-forms' => true,
        'pwe-media' => true,
        'gravityforms' => false,
    ];
}

/**
 * Collect plugin versions, statuses, and available updates.
 */
function pwe_plugins_info_status(bool $refresh = false): array
{
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
    require_once ABSPATH . 'wp-admin/includes/update.php';

    // Refresh available plugin updates when requested.
    if ($refresh) {
        wp_update_plugins();
    }

    global $wp_version;

    // Load installed plugins and update settings.
    $installed = get_plugins();
    $active = (array) get_option('active_plugins', []);
    $network = is_multisite() ? (array) get_site_option('active_sitewide_plugins', []) : [];
    $automatic = (array) get_site_option('auto_update_plugins', []);

    $globalAutomatic = function_exists('wp_is_auto_update_enabled_for_type')
        ? (bool) wp_is_auto_update_enabled_for_type('plugin') : false;

    // Read cached WordPress update information.
    $updates = get_site_transient('update_plugins');
    $available = is_object($updates) && isset($updates->response) ? (array) $updates->response : [];

    $plugins = [];

    $summary = [
        'total' => 0,
        'installed' => 0,
        'active' => 0,
        'inactive' => 0,
        'missing' => 0,
        'updates_available' => 0,
        'auto_update_enabled' => 0,
        'auto_update_disabled' => 0,
    ];

    $updatePermissions = pwe_plugins_info_update_permissions();

    // Build the status information for each monitored plugin.
    foreach (pwe_plugins_info_monitored() as $slug => $file) {
        $exists = isset($installed[$file]);
        $enabled = $exists && (in_array($file, $active, true) || isset($network[$file]));
        $auto = $exists && in_array($file, $automatic, true);

        $upgrade = $exists ? ($available[$file] ?? null) : null;
        $version = $exists ? (string) ($installed[$file]['Version'] ?? '') : null;
        $newVersion = is_object($upgrade) ? (string) ($upgrade->new_version ?? '') : '';

        $hasUpdate = $newVersion !== '' && $version !== '' && version_compare($newVersion, $version, '>');

        $plugins[$slug] = [
            'slug' => $slug,
            'name' => $exists ? (string) ($installed[$file]['Name'] ?? $slug) : $slug,
            'plugin_file' => $file,
            'installed' => $exists,
            'active' => $enabled,
            'version' => $version,
            'new_version' => $hasUpdate ? $newVersion : null,
            'update_available' => $hasUpdate,
            'update_allowed' => ($updatePermissions[$slug] ?? false) === true,
            'status' => !$exists ? 'missing' : ($enabled ? 'active' : 'inactive'),
            'auto_update' => $auto,
            'auto_update_status' => !$exists ? 'not_installed' : ($auto ? 'enabled' : 'disabled'),
            'auto_update_effective' => $exists ? $auto && $globalAutomatic : null,
        ];

        // Update summary counters.
        $summary['total']++;
        $summary[$exists ? 'installed' : 'missing']++;

        if ($exists) {
            $summary[$enabled ? 'active' : 'inactive']++;
            $summary[$auto ? 'auto_update_enabled' : 'auto_update_disabled']++;
        }

        if ($hasUpdate) {
            $summary['updates_available']++;
        }
    }

    // Return the complete site and plugin status.
    return [
        'status' => 'success',
        'site_url' => home_url('/'),
        'checked_at' => gmdate('c'),
        'php_version' => PHP_VERSION,
        'wordpress_version' => (string) $wp_version,
        'auto_updates_globally_enabled' => $globalAutomatic,
        'summary' => $summary,
        'plugins' => $plugins,
    ];
}

/**
 * Update a monitored plugin through WordPress.
 */
function pwe_plugins_info_update(string $slug): array
{
    $monitored = pwe_plugins_info_monitored();

    // Validate the requested plugin and its update permissions.
    if (!isset($monitored[$slug])) {
        return [422, ['status' => 'error', 'message' => 'Nieznana wtyczka.']];
    }

    if ((pwe_plugins_info_update_permissions()[$slug] ?? false) !== true) {
        return [403, ['status' => 'error', 'message' => 'Aktualizacja tej wtyczki z CAP jest zablokowana.']];
    }

    // Check multisite and filesystem restrictions.
    if (is_multisite() && !is_main_site()) {
        return [403, ['status' => 'error', 'message' => 'Aktualizacje multisite wymagają witryny głównej.']];
    }

    if (!wp_is_file_mod_allowed('cap_plugins_update')) {
        return [403, ['status' => 'error', 'message' => 'Aktualizacje plików są zablokowane.']];
    }

    // Load WordPress plugin update dependencies.
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
    require_once ABSPATH . 'wp-admin/includes/update.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

    $file = $monitored[$slug];

    // Make sure the plugin is installed.
    if (!isset(get_plugins()[$file])) {
        return [404, ['status' => 'error', 'message' => 'Wtyczka nie jest zainstalowana.']];
    }

    // Keep the previous activation state; REST updates do not reactivate plugins automatically.
    $wasActive = is_plugin_active($file);
    $wasNetworkActive = is_multisite() && is_plugin_active_for_network($file);

    // An atomic option insert prevents concurrent updates on the same WordPress installation.
    $lockKey = 'pwe_cap_plugin_update_lock';
    $lock = (int) get_option($lockKey, 0);

    if ($lock && $lock < time() - 600) {
        delete_option($lockKey);
    }

    if (!add_option($lockKey, time(), '', false)) {
        return [409, ['status' => 'error', 'message' => 'Inna aktualizacja już trwa.']];
    }

    try {
        // Refresh update information before starting.
        wp_update_plugins();

        $updates = get_site_transient('update_plugins');
        $upgrade = is_object($updates) ? ($updates->response[$file] ?? null) : null;
        $current = (string) (get_plugins()[$file]['Version'] ?? '');
        $target = is_object($upgrade) ? (string) ($upgrade->new_version ?? '') : '';

        if ($target === '' || !version_compare($target, $current, '>')) {
            return [409, ['status' => 'error', 'message' => 'Brak nowszej wersji dostępnej przez mechanizm WordPress.']];
        }

        // Run the WordPress plugin upgrader.
        $upgrader = new Plugin_Upgrader(new Automatic_Upgrader_Skin());
        $result = $upgrader->upgrade($file);

        if (is_wp_error($result)) {
            error_log('PWE plugin update failed (' . $slug . '): ' . $result->get_error_message());

            return [500, ['status' => 'error', 'message' => 'Aktualizacja nie powiodła się. Szczegóły w logach WordPress.']];
        }

        if ($result !== true) {
            return [500, ['status' => 'error', 'message' => 'Aktualizator nie potwierdził powodzenia.']];
        }

        // Verify the installed version after the update.
        wp_clean_plugins_cache(true);

        $after = get_plugins();
        $actual = (string) ($after[$file]['Version'] ?? '');

        if ($actual === '' || version_compare($actual, $current, '<=')) {
            return [500, ['status' => 'error', 'message' => 'Nie udało się potwierdzić nowej wersji wtyczki.']];
        }

        // WordPress deactivates a site-active plugin during a manual upgrade.
        // Restore only plugins that were active before the update.
        if (($wasActive || $wasNetworkActive) && !is_plugin_active($file)) {
            $activation = activate_plugin($file, '', $wasNetworkActive, true);
            if (is_wp_error($activation)) {
                error_log('PWE CAP plugin activation failed (' . $slug . '): ' . $activation->get_error_message());
                return [500, ['status' => 'error', 'message' => 'Wtyczka została zaktualizowana, ale nie udało się przywrócić jej aktywacji. Sprawdź logi WordPress.']];
            }
        }

        error_log('PWE CAP plugin updated: ' . $slug . ' ' . $current . ' -> ' . $actual);

        return [
            200,
            [
                'status' => 'success',
                'message' => 'Wtyczka została zaktualizowana.',
                'slug' => $slug,
                'previous_version' => $current,
                'version' => $actual,
                'active' => is_plugin_active($file),
            ],
        ];

    } catch (Throwable $e) {
        error_log('PWE CAP plugin update exception (' . $slug . '): ' . $e->getMessage());

        return [500, ['status' => 'error', 'message' => 'Błąd aktualizacji. Sprawdź logi WordPress.']];

    } finally {
        // Recover the prior active state after an interrupted or failed upgrade,
        // but only if WordPress still recognizes the plugin as installed.
        if (($wasActive || $wasNetworkActive) && !is_plugin_active($file)) {
            wp_clean_plugins_cache(false);
            if (isset(get_plugins()[$file])) {
                $activation = activate_plugin($file, '', $wasNetworkActive, true);
                if (is_wp_error($activation)) {
                    error_log('PWE CAP plugin activation recovery failed (' . $slug . '): ' . $activation->get_error_message());
                }
            }
        }
        // Always release the update lock.
        delete_option($lockKey);
    }
}

/**
 * Authorize requests to the plugin information API.
 */
function pwe_plugins_info_rest_permission()
{
    return pwe_plugins_info_authorized()
        ? true
        : new WP_Error('pwe_unauthorized', 'Brak autoryzacji.', ['status' => 401]);
}

/**
 * Register the plugin information and update endpoints.
 */
add_action('rest_api_init', function () {

    // Return plugin information and available updates.
    register_rest_route('pwe-system/v1', '/plugins-info', [
        'methods' => 'GET',
        'permission_callback' => 'pwe_plugins_info_rest_permission',
        'callback' => function (WP_REST_Request $request) {
            return rest_ensure_response(pwe_plugins_info_status($request->get_param('refresh') === '1'));
        },
    ]);

    // Update a selected plugin.
    register_rest_route('pwe-system/v1', '/plugins-info/update', [
        'methods' => 'POST',
        'permission_callback' => 'pwe_plugins_info_rest_permission',
        'callback' => function (WP_REST_Request $request) {
            $params = $request->get_json_params();
            list($status, $data) = pwe_plugins_info_update((string) ($params['slug'] ?? ''));

            return new WP_REST_Response($data, $status);
        },
    ]);
});
