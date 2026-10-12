<?php

/**
 * Compose the shared PWE System helper API from focused traits.
 *
 * See functions/README.md for the file map and complete method index.
 */
if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/functions/database-connection.php';
require_once __DIR__ . '/functions/database-cache.php';
require_once __DIR__ . '/functions/database-data.php';
require_once __DIR__ . '/functions/elements.php';
require_once __DIR__ . '/functions/translations.php';
require_once __DIR__ . '/functions/gravity-forms.php';
require_once __DIR__ . '/functions/sessions.php';
require_once __DIR__ . '/functions/logging.php';
require_once __DIR__ . '/functions/media.php';
require_once __DIR__ . '/functions/colors.php';
require_once __DIR__ . '/functions/fairs.php';
require_once __DIR__ . '/functions/dates.php';
require_once __DIR__ . '/functions/utilities.php';
require_once __DIR__ . '/functions/wpbakery.php';

/**
 * Shared helper API for PWE System and existing PWE Elements integrations.
 *
 * Traits keep all methods and properties in this class, including private helpers.
 * Keep the existing method names, signatures and static/instance usage unchanged.
 */
class PWE_System_Functions {
    use PWE_System_Functions_Database_Connection;
    use PWE_System_Functions_Database_Cache;
    use PWE_System_Functions_Database_Data;
    use PWE_System_Functions_Elements;
    use PWE_System_Functions_Translations;
    use PWE_System_Functions_Gravity_Forms;
    use PWE_System_Functions_Sessions;
    use PWE_System_Functions_Logging;
    use PWE_System_Functions_Media;
    use PWE_System_Functions_Colors;
    use PWE_System_Functions_Fairs;
    use PWE_System_Functions_Dates;
    use PWE_System_Functions_Utilities;
    use PWE_System_Functions_WPBakery;
}

// Register aggregated database console diagnostics in the page footer.
add_action('wp_footer', ['PWE_System_Functions', 'output_db_connection_logs']);

// Keep the legacy class name available to existing PWE Elements code.
if (!class_exists('PWE_Functions', false)) {
    class_alias('PWE_System_Functions', 'PWE_Functions');
}

// Keep the legacy class name available to existing PWE Elements code.
if (!class_exists('PWECommonFunctions', false)) {
    class_alias('PWE_System_Functions', 'PWECommonFunctions');
}

