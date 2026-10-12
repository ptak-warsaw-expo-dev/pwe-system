<?php

/**
 * File logging and aggregated database console diagnostics.
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Compose file logging and aggregated database console diagnostics in PWE_System_Functions.
 *
 * @internal Composed by core/class-pwe-system-functions.php.
 */
trait PWE_System_Functions_Logging {
    /**
     * Append a timestamped message to a log file in the uploads directory.
     */
    public static function add_log($message, $filename = 'logs') {
        $upload_dir = wp_upload_dir();
        $dir = $upload_dir['basedir'] . '/logs';
        $file = $dir . '/' . $filename . '.log';

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $line = date('Y-m-d H:i:s') . ' - ' . $message . PHP_EOL;

        file_put_contents($file, $line, FILE_APPEND);
    }

    /**
     * Deduplicated diagnostics collected during the current request.
     */
    private static $debug_logs = [];

    /**
     * Collect and deduplicate database diagnostics during the current request.
     */
    private static function debug_log($message, $type = 'log') {

        if (!function_exists('wp_get_current_user')) {
            return;
        }

        if (!current_user_can('administrator') || is_admin()) {
            return;
        }

        $key = $type . '|' . $message;

        if (!isset(self::$debug_logs[$key])) {
            self::$debug_logs[$key] = [
                'type' => $type,
                'message' => $message,
                'count' => 1
            ];
        } else {
            self::$debug_logs[$key]['count']++;
        }
    }

    /**
     * Print aggregated database diagnostics to the browser console when permitted.
     */
    public static function output_db_connection_logs() {

        if (empty(self::$debug_logs)) {
            return;
        }

        echo '<script>';
        echo 'console.groupCollapsed("DB CONNECTIONS (' . self::class . ')");';

        foreach (self::$debug_logs as $log) {

            $count = $log['count'] ?? 1;

            $msg = '[' . $count . 'x] ' . $log['message'];
            $msg = addslashes($msg);

            echo "console.{$log['type']}('{$msg}');";
        }

        echo 'console.groupEnd();';
        echo '</script>';
    }
}
