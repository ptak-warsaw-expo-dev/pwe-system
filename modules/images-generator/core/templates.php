<?php
if (!defined('ABSPATH')) { exit; }

/** Trusted PHP definitions, automatically discovered; never loaded from request paths. */
final class PWE_Image_Templates {
    public static function all(): array {
        static $templates;
        if ($templates !== null) { return $templates; }
        $result = []; $filenames = [];
        foreach (glob(dirname(__DIR__) . '/templates/*.php') as $file) {
            $t = require $file;
            if (!is_array($t) || !isset($t['id'], $t['language'], $t['label'], $t['width'], $t['height'], $t['filename'], $t['assets'], $t['draw'])
                || !preg_match('/^[a-z0-9-]+$/D', $t['id'])
                || !in_array($t['language'], ['pl', 'en'], true)
                || !is_string($t['label']) || $t['label'] === ''
                || !is_int($t['width']) || !is_int($t['height']) || $t['width'] < 1 || $t['height'] < 1
                || $t['width'] * $t['height'] > 4000000
                || !preg_match('/^[a-zA-Z0-9_-]+\.png$/D', $t['filename'])
                || !is_array($t['assets']) || !is_callable($t['draw'])
                || isset($result[$t['id']]) || isset($filenames[$t['filename']])) {
                throw new RuntimeException('Nieprawidłowa lub powielona definicja grafiki: ' . basename($file));
            }
            foreach ($t['assets'] as $asset) {
                if (!is_string($asset) || !preg_match('/^[a-z0-9_]+$/D', $asset)) {
                    throw new RuntimeException('Nieprawidłowy klucz assetu: ' . basename($file));
                }
            }
            $result[$t['id']] = $t; $filenames[$t['filename']] = true;
        }
        if (!$result) { throw new RuntimeException('Brak szablonów grafik.'); }
        return $templates = $result;
    }
}
