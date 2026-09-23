<?php

if (!defined('ABSPATH')) {
    exit;
}

final class PWE_System_Admin_Access
{
    public static function is_allowed(): bool
    {
        return current_user_can('manage_options');
    }
}
