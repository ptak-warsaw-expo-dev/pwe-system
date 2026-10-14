<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register shared PWE System hooks and page activity logging.
 */
final class PWE_System_Hooks {
    private static bool $initialized = false;

    /**
     * Register callbacks once per request, regardless of plugin load order.
     */
    public static function init(): void {
        if (self::$initialized) {
            return;
        }

        self::$initialized = true;

        self::normalize_http_host();

        add_action('gform_post_form_restored', [self::class, 'log_form_restored'], 10, 1);
    }

    /**
     * Remove the optional leading www. prefix from the current request host.
     */
    public static function normalize_http_host(): void {
        if (!isset($_SERVER['HTTP_HOST']) || !is_string($_SERVER['HTTP_HOST'])) {
            return;
        }

        $_SERVER['HTTP_HOST'] = preg_replace('/^www\./i', '', $_SERVER['HTTP_HOST']);
    }

    /**
     * Record a form restored from the Gravity Forms trash and its acting user.
     *
     * @param int $form_id Restored form ID.
     */
    public static function log_form_restored($form_id): void {
        $form_id = absint($form_id);

        if ($form_id === 0) {
            return;
        }

        $user = wp_get_current_user();
        $user_id = (int) $user->ID;
        $login = $user_id > 0 ? (string) $user->user_login : 'system/anonymous';
        $form_title = '';

        // Keep the event useful even if form metadata is unavailable.
        if (class_exists('GFAPI') && is_callable(['GFAPI', 'get_form'])) {
            $form = GFAPI::get_form($form_id);

            if (is_array($form)) {
                $form_title = (string) ($form['title'] ?? '');
            }
        }

        self::write_page_log(sprintf(
            '[Gravity Forms] Przywrócono formularz ID: %d | Nazwa: %s | User ID: %d | Login: %s',
            $form_id,
            $form_title !== '' ? $form_title : '-',
            $user_id,
            $login
        ));
    }

    /**
     * Append one event to uploads/logs/gf-logs.log in the WordPress timezone.
     *
     * @param string $message Event details without a timestamp.
     * @return bool Whether the complete event was written.
     */
    private static function write_page_log(string $message): bool {
        $uploads = wp_upload_dir(null, false);

        if (!empty($uploads['error']) || empty($uploads['basedir'])) {
            error_log('[PWE System] Cannot resolve the uploads directory for page activity logging.');
            return false;
        }

        $directory = trailingslashit($uploads['basedir']) . 'logs';

        if (!is_dir($directory) && !wp_mkdir_p($directory)) {
            error_log('[PWE System] Cannot create the page activity log directory.');
            return false;
        }

        // Keep each event on one line, including untrusted form titles and logins.
        $message = preg_replace('/[\r\n\x00]+/', ' ', $message);
        $line = '[' . current_time('mysql') . '] ' . $message . PHP_EOL;
        $file = trailingslashit($directory) . 'gf-logs.log';

        // Append under an exclusive lock so simultaneous events cannot overlap.
        $written = @file_put_contents($file, $line, FILE_APPEND | LOCK_EX);

        if ($written === false || $written !== strlen($line)) {
            error_log('[PWE System] Cannot append the complete page activity event to the log file.');
            return false;
        }

        return true;
    }
}