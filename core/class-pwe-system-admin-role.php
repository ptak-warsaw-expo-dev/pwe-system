<?php

if (!defined('ABSPATH')) {
    exit;
}

// Create custom roles
add_action('init', function () {

    // Get administrator capabilities
    $admin_role = get_role('administrator');

    if (!$admin_role) {
        return;
    }


    // Create Web Developer role with administrator permissions
    if (!get_role('web-dev')) {

        add_role(
            'web-dev',
            'Web Developer',
            $admin_role->capabilities
        );

    } else {

        // Update role permissions if role already exists
        $web_dev_role = get_role('web-dev');

        foreach ($admin_role->capabilities as $cap => $enabled) {
            $web_dev_role->add_cap($cap, $enabled);
        }
    }

});

// Sync users + assign roles
add_action('init', function () {

    $users_data = [
        [
            'username' => 'Anton',
            'email'    => 'anton.melnychuk@warsawexpo.eu',
            'roles'    => ['administrator', 'web-dev'],
        ],
        [
            'username' => 'PiotrK',
            'email'    => 'piotr.krupniewski@warsawexpo.eu',
            'roles'    => ['administrator', 'web-dev'],
        ],
        [
            'username' => 'jakub.chola',
            'email'    => 'jakub.chola@warsawexpo.eu',
            'roles'    => ['administrator', 'web-dev'],
        ],
        [
            'username' => 'Marek',
            'email'    => 'marek.rumianek@warsawexpo.eu',
            'roles'    => ['administrator', 'web-dev'],
        ],
        [
            'username' => 'PiotrB',
            'email'    => 'piotr.bargiel@warsawexpo.eu',
            'roles'    => ['administrator', 'web-dev'],
        ],
        [
            'username' => 'Sergiusz',
            'email'    => 's.skrypnychenko@warsawexpo.eu',
            'roles'    => ['administrator', 'web-dev'],
        ],
        [
            'username' => 'JakubG',
            'email'    => 'jakub.goral@warsawexpo.eu',
            'roles'    => ['administrator'],
        ],
    ];

    foreach ($users_data as $user_data) {

        $user = get_user_by('email', $user_data['email']);

        // Create user if not exists
        if (!$user) {

            $user_id = wp_create_user(
                $user_data['username'],
                wp_generate_password(12, true, true),
                $user_data['email']
            );


            if (is_wp_error($user_id)) {
                error_log('User create error: ' . $user_data['email']);
                continue;
            }

            $user = new WP_User($user_id);
        }


        // Add required roles
        foreach ($user_data['roles'] as $role) {

            if (!in_array($role, (array) $user->roles, true)) {
                $user->add_role($role);
            }

        }

    }

});