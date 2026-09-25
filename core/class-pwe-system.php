<?php
if (!defined('ABSPATH')) {
    exit;
}

final class PWE_System {
    private static bool $initialized = false;

    public static function init(): void {
        if (self::$initialized) {
            return;
        }
        self::$initialized = true;

        require_once PWE_SYSTEM_PATH . 'core/class-pwe-system-admin-access.php';
        require_once PWE_SYSTEM_PATH . 'core/class-pwe-system-admin-ui.php';
        require_once PWE_SYSTEM_PATH . 'core/class-pwe-system-admin.php';
        require_once PWE_SYSTEM_PATH . 'modules/doc-manager/class-pwe-system-doc-manager.php';
        require_once PWE_SYSTEM_PATH . 'modules/replace-content/replace-content-module.php';
        require_once PWE_SYSTEM_PATH . 'modules/resend/resend-module.php';

        self::sync_capabilities();
        add_action('init', [self::class, 'sync_capabilities'], 99);

        PWE_System_Replace_Content::init();
        PWE_System_Resend::init();
        PWE_System_Admin::init();
        PWE_System_Doc_Manager::init();

        // Audyt formularzy należy wyłącznie do PWE System. PWE QR Gravity Forms
        // pozostaje źródłem generatora QR używanego przez część kontroli audytu.
        $forms_audit_module = PWE_SYSTEM_PATH . 'modules/forms-audit/pwe-forms-audit-module.php';
        if (is_file($forms_audit_module)) {
            require_once $forms_audit_module;
        }

        // CAP/data shortcodes must be available before PWE_Shortcodes is initialized.
        // PWE_Shortcodes uses [pwe_*] values as its lower-level data source.
        // Older PWElements versions may already provide this procedural layer,
        // so never redeclare the same global functions.
        if (!function_exists('pwe_get_shortcode_map')) {
            require_once PWE_SYSTEM_PATH . 'modules/shortcodes/backend-shortcodes.php';
        }

        // Shortcodes were moved from pwe-elements-auto-switch.
        // Keep the original class name for backward compatibility with existing code.
        if (!class_exists('PWE_Shortcodes')) {
            require_once PWE_SYSTEM_PATH . 'modules/shortcodes/class-shortcodes.php';
        }
    }

    public static function activate(): void {
        require_once PWE_SYSTEM_PATH . 'modules/doc-manager/class-pwe-system-doc-manager.php';
        self::sync_capabilities();
        PWE_System_Doc_Manager::ensure_doc_directory();
        PWE_System_Doc_Manager::install_log_table();
    }

    public static function sync_capabilities(): void {
        foreach (['administrator', 'logotype_edytor'] as $role_name) {
            $role = get_role($role_name);
            if ($role && !$role->has_cap('pwe_manage_doc')) {
                $role->add_cap('pwe_manage_doc');
            }
        }
    }
}
