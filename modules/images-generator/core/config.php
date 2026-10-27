<?php
if (!defined('ABSPATH')) { exit; }

// Wspólna konfiguracja dla wszystkich stron. Ścieżki względem lokalnej instalacji WP.
return [
    'logo_pl' => ABSPATH . 'doc/logo.png',
    'logo_en' => ABSPATH . 'doc/logo-en.png',
    'background_1200' => ABSPATH . 'doc/background.webp',
    'background_800' => ABSPATH . 'doc/background.webp',
    'pwe_logo' => dirname(__DIR__) . '/assets/logo-pwe.webp',
    'font_main' => dirname(__DIR__) . '/assets/DroidSans-Bold.ttf',
    'font_small' => dirname(__DIR__) . '/assets/DroidSans-Bold.ttf',
    'headline_pl' => '[trade_fair_desc]',
    'headline_en' => '[trade_fair_desc_eng]',
    'output_dir' => ABSPATH . 'doc/wypromuj',
];
