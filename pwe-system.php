<?php
/**
 * Plugin Name: PWE System
 * Plugin URI: https://github.com/ptak-warsaw-expo-dev/pwe-system
 * Description: Central system for PWE tools and modules, including administration, integrations, data management and automation.
 * Version: 1.0.1
 * Requires PHP: 7.4
 * Author: PWE Web Developers
 * Co-author: Anton Melnychuk, Piotr Krupniewski, Jakub Choła
 * Author URI: https://github.com/ptak-warsaw-expo-dev
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI: https://github.com/ptak-warsaw-expo-dev/pwe-system/releases/latest
 * Text Domain: pwe-system
 */

if (!defined('ABSPATH')) {
    exit;
}

define('PWE_SYSTEM_VERSION', '1.0.0');
define('PWE_SYSTEM_FILE', __FILE__);
define('PWE_SYSTEM_PATH', plugin_dir_path(__FILE__));
define('PWE_SYSTEM_URL', plugin_dir_url(__FILE__));

if (!defined('PWE_LANG')) {
    define('PWE_LANG', substr(determine_locale(), 0, 2));
}

require_once PWE_SYSTEM_PATH . 'core/class-pwe-system-functions.php';
require_once PWE_SYSTEM_PATH . 'core/class-pwe-system.php';

require_once PWE_SYSTEM_PATH . 'core/class-pwe-system-updater.php';

new PWE_System_Updater();

register_activation_hook(__FILE__, ['PWE_System', 'activate']);
add_action('plugins_loaded', ['PWE_System', 'init'], 20);
