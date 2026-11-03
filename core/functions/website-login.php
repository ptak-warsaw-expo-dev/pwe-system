<?php
/** Shared password gates, composed into PWE_System_Functions. */
if (!defined('ABSPATH')) exit;

trait PWE_System_Functions_Website_Login {
    private static $pwe_website_login_passwords = [];
    private static $pwe_website_login_handled = [];
    private static $pwe_website_login_errors = [];

    public static function website_login(string $key = 'forms_password', string $title = 'Dostęp do strony', string $description = 'Wpisz hasło, aby wyświetlić narzędzie.'): bool {
        self::website_login_no_cache();
        if (!self::website_login_valid_key($key)) {
            echo '<p role="alert">Nieprawidłowa konfiguracja zabezpieczenia.</p>';
            return false;
        }
        self::website_login_process_login();
        if (self::website_login_allowed($key)) return true;
        echo self::website_login_html($key, $title, $description);
        return false;
    }

    public static function website_login_allowed(string $key = 'forms_password'): bool {
        self::website_login_no_cache();
        if (!self::website_login_valid_key($key)) return false;
        $password = self::website_login_password($key);
        if ($password === '') return false;
        $name = self::website_login_cookie_name($key);
        $cookie = isset($_COOKIE[$name]) && is_string($_COOKIE[$name]) ? wp_unslash($_COOKIE[$name]) : '';
        if (!preg_match('/^([0-9]{10,12})\.([a-f0-9]{64})$/D', $cookie, $matches)) return false;
        $expires = (int) $matches[1];
        $now = time();
        if ($expires <= $now || $expires > $now + self::website_login_duration()) return false;
        return hash_equals(self::website_login_signature($key, $expires, $password), $matches[2]);
    }

    public static function website_login_process_login(): void {
        // Also suppress caching on requests carrying an existing access cookie.
        foreach (array_keys($_COOKIE) as $name) {
            if (strpos((string) $name, 'pwe_website_access_') === 0) {
                self::website_login_no_cache();
                break;
            }
        }
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || self::website_login_post('pwe_website_unlock') !== '1') return;
        $key = self::website_login_post('pwe_website_key');
        if (!self::website_login_valid_key($key) || isset(self::$pwe_website_login_handled[$key])) return;
        self::website_login_no_cache();

        // Verify the server-issued form scope BEFORE looking up central metadata.
        if (!wp_verify_nonce(self::website_login_post('_pwe_website_nonce'), 'pwe_website_login:' . $key)) {
            self::$pwe_website_login_handled[$key] = true;
            self::$pwe_website_login_errors[$key] = 'Formularz wygasł. Odśwież stronę i spróbuj ponownie.';
            return;
        }
        // Allow a render-time retry if the existing settings class is loaded late.
        if (!is_callable(['PWE_Functions', 'get_database_meta_data'])) return;
        self::$pwe_website_login_handled[$key] = true;
        $attempt_key = self::website_login_attempt_key($key);
        $attempts = (int) get_transient($attempt_key);
        if ($attempts >= 10) {
            self::$pwe_website_login_errors[$key] = 'Zbyt wiele prób. Spróbuj ponownie za 15 minut.';
            return;
        }
        $password = self::website_login_password($key);
        if ($password === '') {
            self::$pwe_website_login_errors[$key] = 'Brak hasła w centralnej konfiguracji. Skontaktuj się z administratorem.';
            return;
        }
        if (!hash_equals($password, self::website_login_post('pwe_website_password'))) {
            set_transient($attempt_key, $attempts + 1, 15 * MINUTE_IN_SECONDS);
            self::$pwe_website_login_errors[$key] = $attempts + 1 >= 10
                ? 'Zbyt wiele prób. Spróbuj ponownie za 15 minut.' : 'Nieprawidłowe hasło.';
            return;
        }
        if (headers_sent()) {
            self::$pwe_website_login_errors[$key] = 'Nie udało się zapisać dostępu. Skontaktuj się z administratorem.';
            return;
        }
        $expires = time() + self::website_login_duration();
        $token = $expires . '.' . self::website_login_signature($key, $expires, $password);
        $name = self::website_login_cookie_name($key);
        $saved = setcookie($name, $token, [
            'expires' => $expires,
            'path' => (defined('COOKIEPATH') && COOKIEPATH ? COOKIEPATH : '/'),
            'secure' => is_ssl(), 'httponly' => true, 'samesite' => 'Lax',
        ]);
        if (!$saved) {
            self::$pwe_website_login_errors[$key] = 'Nie udało się zapisać dostępu. Sprawdź działanie ciasteczek.';
            return;
        }
        delete_transient($attempt_key);
        unset(self::$pwe_website_login_errors[$key]);
        // Render immediately on the same URL, only after Set-Cookie succeeded.
        $_COOKIE[$name] = $token;
    }

    private static function website_login_valid_key(string $key): bool {
        return (bool) preg_match('/^[a-z][a-z0-9_]{0,63}$/D', $key);
    }

    private static function website_login_duration(): int { return 8 * HOUR_IN_SECONDS; }
    private static function website_login_cookie_name(string $key): string { return 'pwe_website_access_' . $key; }

    private static function website_login_signature(string $key, int $expires, string $password): string {
        return hash_hmac('sha256', 'pwe-website-login|' . get_current_blog_id() . '|' . $key . '|' . $expires, wp_salt('auth') . '|' . $password);
    }

    private static function website_login_attempt_key(string $key): string {
        $ip = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        return 'pwe_website_attempts_' . hash('sha256', $key . '|' . $ip . '|' . wp_salt('nonce'));
    }

    private static function website_login_post(string $name): string {
        return isset($_POST[$name]) && is_string($_POST[$name]) ? wp_unslash($_POST[$name]) : '';
    }

    private static function website_login_password(string $key): string {
        if (array_key_exists($key, self::$pwe_website_login_passwords)) return self::$pwe_website_login_passwords[$key];
        if (!is_callable(['PWE_Functions', 'get_database_meta_data'])) return '';
        $raw = PWE_Functions::get_database_meta_data($key);
        $password = '';
        for ($i = 0; $i < 10; $i++) {
            if (is_object($raw)) $raw = get_object_vars($raw);
            if (is_array($raw)) {
                if (array_key_exists($key, $raw)) {
                    // The named field is the final password: preserve every byte.
                    $password = is_string($raw[$key]) ? $raw[$key] : '';
                    break;
                }
                if (array_key_exists('meta_data', $raw)) { $raw = $raw['meta_data']; continue; }
                if (count($raw) === 1 && is_int(array_key_first($raw))) { $raw = reset($raw); continue; }
                // Never substitute a different field, e.g. forms_password for cc_password.
                break;
            }
            if (is_string($raw)) {
                $decoded = json_decode($raw, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) { $raw = $decoded; continue; }
                $password = $raw;
            }
            break;
        }
        return self::$pwe_website_login_passwords[$key] = $password;
    }

    private static function website_login_no_cache(): void {
        if (!defined('DONOTCACHEPAGE')) define('DONOTCACHEPAGE', true);
        if (!headers_sent()) {
            nocache_headers();
            header('Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0');
        }
    }

    private static function website_login_html(string $key, string $title, string $description): string {
        static $number = 0;
        $id = 'pwe_website_password_' . ++$number;
        $message = self::$pwe_website_login_errors[$key] ?? '';
        if ($message === '' && self::website_login_password($key) === '') {
            $message = 'Brak hasła w centralnej konfiguracji. Skontaktuj się z administratorem.';
        } elseif ($message === '' && (int) get_transient(self::website_login_attempt_key($key)) >= 10) {
            $message = 'Zbyt wiele prób. Spróbuj ponownie za 15 minut.';
        }
        $classes = 'pwe-website-password-gate' . ($key === 'forms_password' ? ' pwe-forms-password-gate' : '');
        return '<div class="' . esc_attr($classes) . '" style="max-width:440px;margin:35px auto;padding:28px;border:1px solid #ddd;border-radius:12px;background:#fff">'
            . '<h3 style="margin:0 0 12px">' . esc_html($title) . '</h3><p>' . esc_html($description) . '</p>'
            . ($message !== '' ? '<p role="alert">' . esc_html($message) . '</p>' : '')
            . '<form method="post" action="" autocomplete="off">'
            . '<input type="hidden" name="pwe_website_key" value="' . esc_attr($key) . '">'
            . '<input type="hidden" name="pwe_website_unlock" value="1">'
            . '<input type="hidden" name="_pwe_website_nonce" value="' . esc_attr(wp_create_nonce('pwe_website_login:' . $key)) . '">'
            . '<label for="' . esc_attr($id) . '">Hasło</label>'
            . '<input id="' . esc_attr($id) . '" type="password" name="pwe_website_password" required autocomplete="current-password" style="display:block;width:100%;margin:8px 0 15px;padding:10px;box-sizing:border-box">'
            . '<button type="submit" class="btn">Odblokuj</button></form></div>';
    }
}

// Registered on inclusion; the class must compose this trait before template_redirect.
add_action('template_redirect', static function (): void {
    if (is_callable(['PWE_System_Functions', 'website_login_process_login'])) {
        PWE_System_Functions::website_login_process_login();
    }
}, 1);
