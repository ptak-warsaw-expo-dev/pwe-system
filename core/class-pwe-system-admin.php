<?php
if (!defined('ABSPATH')) {
    exit;
}

final class PWE_System_Admin {
    public static function init(): void {
        add_action('admin_menu', [self::class, 'register_menu'], 5);
        add_action('admin_enqueue_scripts', [self::class, 'enqueue_assets']);
        add_filter('admin_body_class', [self::class, 'admin_body_class']);
    }

    public static function register_menu(): void {
        add_menu_page(
            'PWE System',
            'PWE System',
            'pwe_manage_doc',
            'pwe-system',
            [self::class, 'render_dashboard'],
            PWE_SYSTEM_URL . 'assets/images/logo-pwe.webp',
            4
        );

        add_submenu_page(
            'pwe-system',
            'PWE System',
            'Start',
            'pwe_manage_doc',
            'pwe-system',
            [self::class, 'render_dashboard']
        );

        if (class_exists('PWE_System_Replace_Content', false)) {
            add_submenu_page(
                'pwe-system',
                'Replace content',
                'Replace content',
                'manage_options',
                'pwe-system-replace-content',
                [self::class, 'render_replace_content']
            );
        }

        if (class_exists('PWE_System_Resend', false)) {
            add_submenu_page(
                'pwe-system',
                'Resend',
                'Resend',
                'manage_options',
                'pwe-system-resend',
                [self::class, 'render_resend']
            );
        }
    }

    public static function render_replace_content(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Brak uprawnień.', 'pwe-system'));
        }

        echo '<div class="wrap pwe-system-wrap pwe-system-module-page pwe-module-replace-content">';
        self::render_module_header(
            'PWE SYSTEM / CONTENT',
            'Replace content',
            'Kontrolowana podmiana treści stron i ich wersji językowych na wskazane shortcody.',
            'dashicons-update'
        );
        echo '<div class="pwe-module-content">';

        if (class_exists('PWE_System_Replace_Content', false)) {
            PWE_System_Replace_Content::render_admin_page();
        } else {
            echo '<div class="pwe-system-message is-error"><span class="dashicons dashicons-warning"></span><div><strong>Moduł niedostępny</strong><p>Replace content nie został załadowany.</p></div></div>';
        }

        echo '</div></div>';
    }

    public static function render_resend(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Brak uprawnień.', 'pwe-system'));
        }

        echo '<div class="wrap pwe-system-wrap pwe-system-module-page pwe-module-resend">';
        self::render_module_header(
            'PWE SYSTEM / GRAVITY FORMS',
            'Resend',
            'Masowa ponowna wysyłka powiadomień Gravity Forms z kontrolą partii, statusu i historii procesu.',
            'dashicons-email-alt2'
        );
        echo '<div class="pwe-module-content">';

        if (class_exists('PWE_System_Resend', false)) {
            PWE_System_Resend::render_admin_page();
        } else {
            echo '<div class="pwe-system-message is-error"><span class="dashicons dashicons-warning"></span><div><strong>Moduł niedostępny</strong><p>Resend nie został załadowany.</p></div></div>';
        }

        echo '</div></div>';
    }

    public static function render_module_header(string $kicker, string $title, string $description, string $icon = ''): void {
        ?>
        <div class="pwe-module-hero">
            <div class="pwe-module-title-row">
                <img src="<?php echo esc_url(PWE_SYSTEM_URL . 'assets/images/logo-pwe.webp'); ?>" alt="PWE" class="pwe-module-logo">
                <div>
                    <div class="pwe-system-kicker"><?php echo esc_html($kicker); ?></div>
                    <h1><?php echo esc_html($title); ?></h1>
                    <p><?php echo esc_html($description); ?></p>
                </div>
            </div>
            <?php if ($icon !== '') : ?>
                <span class="pwe-module-hero-icon dashicons <?php echo esc_attr($icon); ?>" aria-hidden="true"></span>
            <?php endif; ?>
        </div>
        <?php
    }

    public static function enqueue_assets(string $hook): void {
        // The menu icon is visible on every wp-admin screen, so the small
        // global stylesheet must be loaded outside PWE System pages as well.
        // All other selectors in this file are scoped to PWE System classes.
        $file = PWE_SYSTEM_PATH . 'assets/css/admin.css';
        wp_enqueue_style(
            'pwe-system-admin',
            PWE_SYSTEM_URL . 'assets/css/admin.css',
            [],
            is_file($file) ? (string) filemtime($file) : PWE_SYSTEM_VERSION
        );

        $page = sanitize_key((string) ($_GET['page'] ?? ''));

        if ($page === 'pwe-system-resend') {
            $resend_script = PWE_SYSTEM_PATH . 'assets/js/resend.js';
            wp_enqueue_script(
                'pwe-system-resend',
                PWE_SYSTEM_URL . 'assets/js/resend.js',
                ['jquery'],
                is_file($resend_script) ? (string) filemtime($resend_script) : PWE_SYSTEM_VERSION,
                true
            );
        }


        if ($page === 'pwe-system-replace-content') {
            $replace_script = PWE_SYSTEM_PATH . 'assets/js/replace-content.js';
            wp_enqueue_script(
                'pwe-system-replace-content',
                PWE_SYSTEM_URL . 'assets/js/replace-content.js',
                ['jquery'],
                is_file($replace_script) ? (string) filemtime($replace_script) : PWE_SYSTEM_VERSION,
                true
            );
        }
    }

    public static function admin_body_class(string $classes): string {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if ($screen && strpos((string) $screen->id, 'pwe-system') !== false) {
            $classes .= ' pwe-system-screen';
        }
        return $classes;
    }

    public static function render_dashboard(): void {
        if (!current_user_can('pwe_manage_doc')) {
            wp_die(esc_html__('Brak uprawnień.', 'pwe-system'));
        }

        $is_admin = current_user_can('manage_options');

        $doc_url = admin_url('admin.php?page=pwe-system-doc');
        $shortcodes_url = admin_url('admin.php?page=pwe-system-shortcodes');
        $cap_endpoint = plugins_url('api/cap/doc.php', PWE_SYSTEM_FILE);
        $news_endpoint = plugins_url('api/news/index.php', PWE_SYSTEM_FILE);
        $functions_ok = class_exists('PWE_System_Functions') && class_exists('PWE_Functions');
        ?>
        <div class="wrap pwe-system-wrap">
            <div class="pwe-system-hero">
                <img src="<?php echo esc_url(PWE_SYSTEM_URL . 'assets/images/logo-pwe.webp'); ?>" alt="PWE" class="pwe-system-logo">
                <div>
                    <div class="pwe-system-kicker">PWE SYSTEM</div>
                    <h1>Systemowe narzędzia w jednym miejscu</h1>
                    <p>Modułowa baza pod funkcje wspólne dla stron PWE: zarządzanie plikami, API, shortcody i kolejne moduły systemowe.</p>
                </div>
                <span class="pwe-system-version">v<?php echo esc_html(PWE_SYSTEM_VERSION); ?></span>
            </div>

            <div class="pwe-system-grid">
                <a class="pwe-system-card" href="<?php echo esc_url($doc_url); ?>">
                    <span class="dashicons dashicons-open-folder"></span>
                    <h2>DOC Manager</h2>
                    <p>Zarządzanie całym katalogiem <code>/doc</code>: upload, foldery, podmiana, zmiana nazw, przenoszenie i usuwanie.</p>
                    <strong>Otwórz manager →</strong>
                </a>

                <?php if ($is_admin) : ?>
                    <a class="pwe-system-card" href="<?php echo esc_url($shortcodes_url); ?>">
                        <span class="dashicons dashicons-editor-code"></span>
                        <h2>Shortcody</h2>
                        <p>Dotychczasowe PWE Shortcodes przeniesione z <code>pwe-elements-auto-switch</code>.</p>
                        <strong>Otwórz shortcody →</strong>
                    </a>

                    <div class="pwe-system-card">
                        <span class="dashicons dashicons-rest-api"></span>
                        <h2>API</h2>
                        <p>Endpointy przeniesione do nowej warstwy systemowej.</p>
                        <code class="pwe-system-endpoint"><?php echo esc_html($cap_endpoint); ?></code>
                        <code class="pwe-system-endpoint"><?php echo esc_html($news_endpoint); ?></code>
                    </div>

                    <?php if (class_exists('PWE_System_Replace_Content', false)) : ?>
                        <a class="pwe-system-card" href="<?php echo esc_url(admin_url('admin.php?page=pwe-system-replace-content')); ?>">
                            <span class="dashicons dashicons-update"></span>
                            <h2>Replace content</h2>
                            <p>Systemowy moduł podmiany treści stron i wersji językowych na wskazane shortcody.</p>
                            <strong>Otwórz moduł →</strong>
                        </a>
                    <?php endif; ?>

                    <?php if (class_exists('PWE_System_Resend', false)) : ?>
                        <a class="pwe-system-card" href="<?php echo esc_url(admin_url('admin.php?page=pwe-system-resend')); ?>">
                            <span class="dashicons dashicons-email-alt2"></span>
                            <h2>Resend</h2>
                            <p>Masowe ponowne wysyłanie powiadomień Gravity Forms z kolejką, kontrolą partii i historią procesu.</p>
                            <strong>Otwórz moduł →</strong>
                        </a>
                    <?php endif; ?>

                    <?php if (class_exists('PWE_System_Forms_Audit_Module', false) && PWE_System_Forms_Audit_Module::is_ready()) : ?>
                        <a class="pwe-system-card" href="<?php echo esc_url(admin_url('admin.php?page=pwe-system-forms-audit')); ?>">
                            <span class="dashicons dashicons-search"></span>
                            <h2>Audyt formularzy i rejestracji</h2>
                            <p>Audyt formularzy Gravity Forms, feedów QR, zapisanych kodów oraz historii rejestracji i powiadomień.</p>
                            <strong>Otwórz audyt →</strong>
                        </a>
                    <?php endif; ?>

                    <div class="pwe-system-card">
                        <span class="dashicons dashicons-admin-plugins"></span>
                        <h2>Status zależności</h2>
                        <p class="pwe-status <?php echo $functions_ok ? 'is-ok' : 'is-warning'; ?>">
                            <?php echo $functions_ok
                                ? 'PWE_System_Functions jest aktywne. Alias PWE_Functions zachowuje zgodność ze starymi elementami.'
                                : 'Brak warstwy PWE_System_Functions. Funkcje współdzielone nie są dostępne.'; ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}
