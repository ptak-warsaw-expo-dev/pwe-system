<?php
if (!defined('ABSPATH')) { exit; }

return [
    'id' => 'wypromuj-800x800',
    'label' => 'Wypromuj się 800×800 PL',
    'language' => 'pl',
    'width' => 800,
    'height' => 800,
    'filename' => 'wypromuj_800_pl.png',
    'assets' => ['logo_pl', 'pwe_logo', 'background_800'],
    'draw' => static function (PWE_Images_Canvas $canvas, string $date, array $c): void {
        $white = $canvas->color(255, 255, 255);
        $black = $canvas->color(0, 0, 0);
        $canvas->asset($c['background_800'], [0, 0, 800, 640], true);
        // Black overlay: 20% opacity, applied only to the background.
        $canvas->rectangle(0, 0, 799, 639, $canvas->colorAlpha(0, 0, 0, 102));
        $headline = explode("\n", str_replace("\r", '', $c['headline']));
        if (count($headline) > 2) { throw new RuntimeException('Nagłówek może mieć maksymalnie dwie linie.'); }

        foreach ($headline as $i => $line) { $canvas->text($line, [100, 22+29*$i, 600, 29], 20, $white); }
        $canvas->asset($c['logo_pl'], [240, 84, 320, 266]);
        $canvas->text('ZAPRASZAMY NA STOISKO', [200, 358, 400, 28], 10, $white, true, $c['font_small']);
        $canvas->pill(199, 390, 402, 165, 46, $white);
        $canvas->pill(199, 579, 402, 33, 16, $white);
        $canvas->rectangle(0, 640, 625, 799, $white);
        $canvas->rectangle(626, 640, 799, 799, $black);
        $canvas->text($date, [22, 680, 582, 80], 32, $black, true);
        $canvas->asset($c['pwe_logo'], [658, 674, 110, 94]);
    },
];
