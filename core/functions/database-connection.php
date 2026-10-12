<?php

/**
 * CAP database server selection, connection reuse and timeouts.
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Compose cAP database server selection, connection reuse and timeouts in PWE_System_Functions.
 *
 * @internal Composed by core/class-pwe-system-functions.php.
 */
trait PWE_System_Functions_Database_Connection {
    /**
     * Resolve a server address when the request does not provide one.
     */
    private static function resolve_server_addr_fallback() {
        $host = php_uname('n');
        switch ($host) {
            case 'dedyk93.cyber-folks.pl':
                return '94.152.206.93';
            case 'dedyk180.cyber-folks.pl':
                return '94.152.207.180';
            case 'dedyk239.cyber-folks.pl':
                return '91.225.28.47';
            case 'dedyk1072.cyber-folks.pl':
                return '91.225.28.72';
            default:
                return '';
        }
    }

    /**
     * Build the CAP database server list and identify the local server.
     */
    private static function get_database_servers() {

        // All PWE servers.
        $servers = [
            [
                'host' => 'dedyk93.cyber-folks.pl',
                'name' => defined('PWE_DB_NAME_93') ? PWE_DB_NAME_93 : null,
                'user' => defined('PWE_DB_USER_93') ? PWE_DB_USER_93 : null,
                'pass' => defined('PWE_DB_PASSWORD_93') ? PWE_DB_PASSWORD_93 : null,
                'ip'  => '94.152.206.93'
            ],
            [
                'host' => 'dedyk180.cyber-folks.pl',
                'name' => defined('PWE_DB_NAME_180') ? PWE_DB_NAME_180 : null,
                'user' => defined('PWE_DB_USER_180') ? PWE_DB_USER_180 : null,
                'pass' => defined('PWE_DB_PASSWORD_180') ? PWE_DB_PASSWORD_180 : null,
                'ip'  => '94.152.207.180'
            ],
            [
                'host' => 'dedyk239.cyber-folks.pl',
                'name' => defined('PWE_DB_NAME_239') ? PWE_DB_NAME_239 : null,
                'user' => defined('PWE_DB_USER_239') ? PWE_DB_USER_239 : null,
                'pass' => defined('PWE_DB_PASSWORD_239') ? PWE_DB_PASSWORD_239 : null,
                'ip'  => '91.225.28.47'
            ],
            [
                'host' => 'dedyk1072.cyber-folks.pl',
                'name' => defined('PWE_DB_NAME_1072') ? PWE_DB_NAME_1072 : null,
                'user' => defined('PWE_DB_USER_1072') ? PWE_DB_USER_1072 : null,
                'pass' => defined('PWE_DB_PASSWORD_1072') ? PWE_DB_PASSWORD_1072 : null,
                'ip'  => '91.225.28.72'
            ],
        ];

        // CRON fallback.
        if (empty($_SERVER['SERVER_ADDR'])) {
            $_SERVER['SERVER_ADDR'] = self::resolve_server_addr_fallback();
        }

        $current_ip = $_SERVER['SERVER_ADDR'];

        // Finding which host is localhost.
        foreach ($servers as &$server) {
            if ($server['ip'] === $current_ip) {
                $server['host'] = 'localhost';
                $server['is_local'] = true;
            } else {
                $server['is_local'] = false;
            }
        }
        unset($server);

        return $servers;
    }

    /**
     * Cached CAP database connection for the current request.
     */
    private static $cached_db_connection = null;

    /**
     * Reuse or open a CAP database connection using the available servers.
     */
    public static function connect_database() {

        // Return cached connection if already connected.
        if (self::$cached_db_connection !== null) {
            if (self::$cached_db_connection->dbh) {
                return self::$cached_db_connection;
            }
        }

        $servers = self::get_database_servers();

        // Timeout filter (only once).
        static $timeout_filter_added = false;
        if (!$timeout_filter_added) {
            add_filter('wpdb_connect_timeout', [self::class, 'set_db_timeout']);
            $timeout_filter_added = true;
        }

        // Find local server.
        $is_local_server = false;
        foreach ($servers as $s) {
            if (!empty($s['is_local'])) {
                $is_local_server = true;
                break;
            }
        }

        // If running on local server, only use that one.
        if ($is_local_server) {
            $servers = array_values(array_filter($servers, function ($s) {
                return !empty($s['is_local']);
            }));
        }

        // Local in-request blocking (no transient).
        static $blocked_hosts = [];

        foreach ($servers as $server) {

            if (empty($server['user']) || empty($server['pass']) || empty($server['name'])) {
                continue;
            }

            $host = $server['host'] ?? 'localhost';

            // Skip if already marked as blocked in THIS request.
            if (!empty($blocked_hosts[$host])) {
                continue;
            }

            // Try connecting.
            $wpdb = new wpdb($server['user'], $server['pass'], $server['name'], $host);

            if (!$wpdb->dbh) {

                error_log("PWE DB: Cannot connect to $host (immediate block).");
                error_log(json_encode([
                    'error' => mysqli_connect_error(),
                    'host'  => $host,
                    'db'    => $server['name']
                ], JSON_UNESCAPED_UNICODE));

                self::add_log('[PWE_System_Functions] DB CONNECTION FAILED: Host: ' . $host . ' User: ' . $server['name'] . ' Error: ' . mysqli_connect_error(), 'db-connections');

                // Mark blocked for this request so next iterations skip it.
                $blocked_hosts[$host] = true;
                continue;
            }

            // Test query.
            $test = $wpdb->get_var("SELECT 1");
            if ((int)$test !== 1) {
                error_log("PWE DB: Test query failed on $host (immediate block).");
                $blocked_hosts[$host] = true;
                continue;
            }

            // Cache connection.
            self::$cached_db_connection = $wpdb;
            return $wpdb;
        }

        // No server worked.
        error_log('PWE DB: All connection attempts failed.');
        return false;
    }

    /**
     * Return the connection timeout used by the wpdb timeout filter.
     */
    public static function set_db_timeout() {
        return 2;
    }
}
