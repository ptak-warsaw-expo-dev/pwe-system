<?php
if (!defined('ABSPATH')) exit;

/** Generator dwóch plików QR w /doc; bez zewnętrznych API i bez GD. */
final class PWE_System_Domain_QR {
    private const STATE_OPTION = 'pwe_system_domain_qr_state_v1';
    private const CRON_HOOK = 'pwe_system_domain_qr_check';
    private const LOCK = 'pwe_system_domain_qr_lock';

    public static function init(): void {
        add_action('init', [self::class, 'schedule'], 30);
        add_action(self::CRON_HOOK, [self::class, 'check']);
        add_action('admin_init', [self::class, 'admin_check']);
    }

    public static function schedule(): void {
        if (!wp_next_scheduled(self::CRON_HOOK)) {
            wp_schedule_event(time() + 120, 'daily', self::CRON_HOOK);
        }
    }

    public static function admin_check(): void {
        if (!current_user_can('manage_options')) return;
        $state = get_option(self::STATE_OPTION, []);
        $site = untrailingslashit(home_url('/'));
        $target = home_url('/rejestracja');
        if (!is_array($state) || ($state['domain_url'] ?? '') !== $site ||
            ($state['registration_url'] ?? '') !== $target ||
            !is_file(ABSPATH . 'doc/qr_domain.png') ||
            !is_file(ABSPATH . 'doc/qr_code_rej.svg')) {
            self::check();
        }
    }

    public static function check(): void {
        if (get_transient(self::LOCK)) return;
        set_transient(self::LOCK, 1, 5 * MINUTE_IN_SECONDS);
        try {
            self::generate();
        } catch (\Throwable $e) {
            error_log('[PWE Domain QR] ' . $e->getMessage());
        } finally {
            delete_transient(self::LOCK);
        }
    }

    private static function generate(): void {
        $doc = trailingslashit(ABSPATH) . 'doc/';
        if (!is_dir($doc) && !wp_mkdir_p($doc)) throw new \RuntimeException('Nie można utworzyć /doc/');
        if (!is_writable($doc)) throw new \RuntimeException('Katalog /doc/ nie jest zapisywalny');

        $domain_url = untrailingslashit(home_url('/'));
        $registration_url = home_url('/rejestracja');
        $state = get_option(self::STATE_OPTION, []);
        if (!is_array($state)) $state = [];
        require_once __DIR__ . '/qr-library.php';
        $targets = [
            'domain' => ['file' => 'qr_domain.png', 'url' => $domain_url, 'kind' => 'png'],
            'registration' => ['file' => 'qr_code_rej.svg', 'url' => $registration_url, 'kind' => 'svg'],
        ];
        $new = [];
        foreach ($targets as $key => $item) {
            $path = $doc . $item['file'];
            $prior = $state[$key] ?? [];
            // Dla nieznanego starego PNG wymuszamy jednorazową wymianę na prawidłowy QR.
            $is_current = is_array($prior) && ($prior['url'] ?? '') === $item['url'] &&
                is_file($path) && hash_file('sha256', $path) === ($prior['sha256'] ?? '');
            if (!$is_current) {
                $qr = \PWE_System_QR\QRCode::getMinimumQRCode($item['url'], QR_ERROR_CORRECT_LEVEL_M);
                $content = $item['kind'] === 'svg' ? self::svg($qr, $item['url']) : self::png($qr);
                self::atomic_save($path, $content);
            }
            $new[$key] = [
                'url' => $item['url'],
                'sha256' => hash_file('sha256', $path),
            ];
        }
        $new['domain_url'] = $domain_url;
        $new['registration_url'] = $registration_url;
        update_option(self::STATE_OPTION, $new, false);
    }

    private static function svg($qr, string $url): string {
        $count = $qr->getModuleCount();
        $margin = 4;
        $total = $count + 2 * $margin;
        $s = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $s .= '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="400" viewBox="0 0 ' . $total . ' ' . $total . '">';
        $s .= '<title>' . htmlspecialchars($url, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</title>';
        $s .= '<rect width="100%" height="100%" fill="#fff"/>';
        $s .= '<path fill="#000" d="';
        for ($y = 0; $y < $count; $y++) {
            for ($x = 0; $x < $count; $x++) {
                if ($qr->isDark($y, $x)) $s .= 'M' . ($x + $margin) . ' ' . ($y + $margin) . 'h1v1h-1z';
            }
        }
        return $s . '"/></svg>';
    }

    /** PNG RGB truecolor, czarno-biały, zapis bez rozszerzenia GD. */
    private static function png($qr): string {
        if (!function_exists('gzcompress')) throw new \RuntimeException('Brak rozszerzenia PHP zlib');
        $count = $qr->getModuleCount();
        $scale = 8;
        $margin = 4;
        $width = ($count + 2 * $margin) * $scale;
        $pixels = '';
        for ($y = 0; $y < $width; $y++) {
            $row = "\x00"; // filtr PNG: None
            $my = intdiv($y, $scale) - $margin;
            for ($x = 0; $x < $width; $x++) {
                $mx = intdiv($x, $scale) - $margin;
                $black = $mx >= 0 && $my >= 0 && $mx < $count && $my < $count && $qr->isDark($my, $mx);
                $row .= $black ? "\x00\x00\x00" : "\xff\xff\xff";
            }
            $pixels .= $row;
        }
        return "\x89PNG\r\n\x1a\n" . self::chunk('IHDR', pack('NNCCCCC', $width, $width, 8, 2, 0, 0, 0)) .
            self::chunk('IDAT', gzcompress($pixels, 9)) . self::chunk('IEND', '');
    }

    private static function chunk(string $type, string $data): string {
        return pack('N', strlen($data)) . $type . $data . pack('H8', hash('crc32b', $type . $data));
    }

    private static function atomic_save(string $path, string $bytes): void {
        $tmp = tempnam(dirname($path), '.pweqr-');
        if ($tmp === false) throw new \RuntimeException('Nie można utworzyć pliku tymczasowego');
        if (file_put_contents($tmp, $bytes, LOCK_EX) !== strlen($bytes)) {
            @unlink($tmp);
            throw new \RuntimeException('Nie można zapisać QR: ' . basename($path));
        }
        @chmod($tmp, 0644);
        if (!@rename($tmp, $path)) {
            @unlink($tmp);
            throw new \RuntimeException('Nie można podmienić QR: ' . basename($path));
        }
    }
}
