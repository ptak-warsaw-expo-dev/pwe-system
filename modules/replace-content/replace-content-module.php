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
    }

    public static function render_admin_page(): void
    {
        PWE_System_Replace_Content_Admin_Page::render();
    }
}
