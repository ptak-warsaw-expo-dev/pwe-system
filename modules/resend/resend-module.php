<?php

if (!defined('ABSPATH')) {
    exit;
}

final class PWE_System_Resend
{
    public const OPTION_JOB = 'pwe_system_resend_job';
    public const DEFAULT_BATCH_SIZE = 100;
    public const DEFAULT_DELAY = 10;
    public const DEFAULT_MIN_AGE_DAYS = 7;
    public const PAGE_SIZE = 300;

    private static bool $initialized = false;

    public static function init(): void
    {
        if (self::$initialized) {
            return;
        }
        self::$initialized = true;

        self::includes();
        PWE_System_Resend_Actions::init();
    }

    private static function includes(): void
    {
        $dir = __DIR__ . '/core/';

        require_once $dir . 'form-repository.php';
        require_once $dir . 'field-locator.php';
        require_once $dir . 'notification-name.php';
        require_once $dir . 'conditional-logic.php';
        require_once $dir . 'job-lock.php';
        require_once $dir . 'resend-job.php';
        require_once $dir . 'gravity-entries.php';
        require_once $dir . 'notification-matcher.php';
        require_once $dir . 'job-runner.php';
        require_once $dir . 'resend-actions.php';
        require_once $dir . 'admin-page.php';
    }

    public static function render_admin_page(): void
    {
        PWE_System_Resend_Admin::render();
    }
}
