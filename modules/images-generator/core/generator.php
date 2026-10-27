<?php
if (!defined('ABSPATH')) { exit; }

final class PWE_Images_Generator {
    const CHECKED = 'pwe_promo_graphics_checked';
    const LAST = 'pwe_promo_graphics_last_date';
    const ERROR = 'pwe_promo_graphics_error';

    public static function config(): array {
        return apply_filters('pwe_images_generator_config', require __DIR__ . '/config.php');
    }

    /** Returns true, "unchanged", or WP_Error. No output files are touched on the unchanged path. */
    public static function generate(bool $force) {
        $lock = null; $temporary = []; $backups = []; $published = []; $committed = false;
        try {
            if (!shortcode_exists('trade_fair_date')) { throw new RuntimeException('Shortcode trade_fair_date nie jest dostępny.'); }
            $date = html_entity_decode(wp_strip_all_tags(do_shortcode('[trade_fair_date]')), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $date = trim(preg_replace('/[\s\x{00a0}]+/u', ' ', $date));
            if (strlen($date) > 160 || !preg_match('/\d{1,2}.*\b20\d{2}\b/u', $date) || preg_match('/[\[\]<>]/', $date)) {
                throw new RuntimeException('Shortcode nie zwrócił prawidłowej daty targów.');
            }
            if (!$force && get_option(self::LAST) === $date) {
                set_transient(self::CHECKED, $date, 0);
                return 'unchanged';
            }
            $c = self::config();
            $dir = realpath($c['output_dir']);
            if (!$dir || !is_dir($dir) || !is_writable($dir)) { throw new RuntimeException('Katalog wyjściowy musi istnieć i być zapisywalny.'); }
            $lock = @fopen($dir . '/.pwe-promo.lock', 'c');
            if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) { throw new RuntimeException('Inny proces generuje grafiki. Spróbuj ponownie później.'); }
            if (!$force && get_option(self::LAST) === $date) {
                set_transient(self::CHECKED, $date, 0);
                return 'unchanged';
            }
            if (!function_exists('imagettftext')) { throw new RuntimeException('Wymagane GD z FreeType.'); }
            $templates = PWE_Image_Templates::all();
            $english_date = '';
            if (in_array('en', array_column($templates, 'language'), true)) {
                if (!shortcode_exists('trade_fair_date_eng')) { throw new RuntimeException('Brak shortcode trade_fair_date_eng.'); }
                $english_date = trim(preg_replace('/[\s\x{00a0}]+/u', ' ', html_entity_decode(wp_strip_all_tags(do_shortcode('[trade_fair_date_eng]')), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
                if (strlen($english_date) > 160 || !preg_match('/\d{1,2}.*\b20\d{2}\b/u', $english_date) || preg_match('/[\[\]<>]/', $english_date)) { throw new RuntimeException('Nieprawidłowa angielska data targów.'); }
            }
            $assets = ['font_main', 'font_small'];
            foreach ($templates as $template) { $assets = array_merge($assets, $template['assets']); }
            foreach (array_unique($assets) as $key) {
                // Never allow stream wrappers, remote assets or PHP execution via paths.
                if (!isset($c[$key]) || !is_string($c[$key]) || strpos($c[$key], '://') !== false || !is_file($c[$key]) || !is_readable($c[$key])) {
                    throw new RuntimeException('Ustaw czytelną lokalną ścieżkę: ' . $key);
                }
            }
            foreach (['font_main', 'font_small'] as $font) {
                if (!preg_match('/\.(ttf|otf)$/i', $c[$font]) || !@imagettfbbox(12, 0, $c[$font], 'Zażółć')) { throw new RuntimeException('Nieprawidłowy font TTF/OTF: ' . $font); }
            }
            $localized = [];
            foreach (array_unique(array_column($templates, 'language')) as $language) {
                $c['headline'] = trim(html_entity_decode(wp_strip_all_tags(do_shortcode($c['headline_' . $language])), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                if (strpos($c['headline'], '[') !== false) { throw new RuntimeException('Nie można odczytać opisu targów.'); }
                if (function_exists('mb_strtoupper')) { $c['headline'] = mb_strtoupper($c['headline'], 'UTF-8'); }
                // A shared shortcode supplies site-specific copy; balance it into two lines.
                if (strpos($c['headline'], "\n") === false) {
                    $words = preg_split('/\s+/u', $c['headline'], -1, PREG_SPLIT_NO_EMPTY);
                    $best = $c['headline']; $difference = PHP_INT_MAX;
                    for ($i = 1; $i < count($words); ++$i) {
                        $left = implode(' ', array_slice($words, 0, $i));
                        $right = implode(' ', array_slice($words, $i));
                        $delta = abs(strlen($left) - strlen($right));
                        if ($delta < $difference) { $best = $left . "\n" . $right; $difference = $delta; }
                    }
                    $c['headline'] = $best;
                }
                if (empty(trim($c['headline'])) || strlen($c['headline']) > 300) { throw new RuntimeException('Ustaw nagłówek targów (maks. 300 bajtów).'); }
                $localized[$language] = $c;
            }
            require_once __DIR__ . '/canvas.php';
            $renderer = new PWE_Images_Canvas();

            foreach ($templates as $template) {
                $target = $dir . '/' . $template['filename'];
                $temp = tempnam($dir, '.pwe-promo-');
                if (!$temp) { throw new RuntimeException('Nie można utworzyć pliku tymczasowego.'); }
                $temporary[$target] = $temp;
                $language = $template['language'];
                $render_date = $language === 'en' ? $english_date : $date;
                $render_date = mb_strtoupper($render_date, 'UTF-8');
                $renderer->render($template, $render_date, $localized[$language], $temp);
                if (!chmod($temp, 0644)) { throw new RuntimeException('Nie można ustawić praw pliku.'); }
            }
            // Stage both complete PNGs and rollback copies before publishing either one.
            foreach ($temporary as $target => $temp) {
                if (file_exists($target)) {
                    $backup = tempnam($dir, '.pwe-promo-backup-');
                    if (!$backup) { throw new RuntimeException('Nie można utworzyć kopii zapasowej.'); }
                    $backups[$target] = $backup;
                    if (!copy($target, $backup)) { throw new RuntimeException('Nie można zabezpieczyć poprzedniej grafiki.'); }
                }
            }
            foreach ($temporary as $target => $temp) {
                if (!rename($temp, $target)) { throw new RuntimeException('Nie udało się opublikować PNG.'); }
                $published[] = $target;
            }
            update_option(self::LAST, $date, false);
            if (get_option(self::LAST) !== $date) { throw new RuntimeException('Nie udało się zapisać daty generacji.'); }
            $committed = true;
            set_transient(self::CHECKED, $date, 0);
            delete_option(self::ERROR);
            return true;
        } catch (Throwable $e) {
            if (!$committed) {
                foreach (array_reverse($published) as $target) {
                    if (isset($backups[$target])) {
                        if (@rename($backups[$target], $target)) { unset($backups[$target]); }
                        else { unset($backups[$target]); error_log('PWE promo: nie udało się przywrócić ' . $target); }
                    } else { @unlink($target); }
                }
            }
            update_option(self::ERROR, $e->getMessage(), false);
            return new WP_Error('pwe_promo_failed', $e->getMessage());
        } finally {
            foreach (array_merge($temporary, $backups) as $path) { if (is_file($path)) { @unlink($path); } }
            if (is_resource($lock)) { flock($lock, LOCK_UN); fclose($lock); }
        }
    }

}
