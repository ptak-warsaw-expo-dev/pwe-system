<?php
if (!defined('ABSPATH')) { exit; }

return [
    'id' => 'wypromuj-1200x200',
    'label' => 'Wypromuj się 1200×200 PL',
    'language' => 'pl',
    'width' => 1200,
    'height' => 200,
    'filename' => 'wypromuj_1200_pl.png',
    'assets' => ['logo_pl', 'pwe_logo', 'background_1200'],
    'draw' => static function (PWE_Images_Canvas $canvas, string $date, array $c): void {
        $white = $canvas->color(255, 255, 255);
        $black = $canvas->color(0, 0, 0);
        $canvas->asset($c['background_1200'], [0, 0, 1200, 200], true);
        // Black overlay: 20% opacity, applied only to the background.
        $canvas->rectangle(0, 0, 1199, 199, $canvas->colorAlpha(0, 0, 0, 102));
        $headline = explode("\n", str_replace("\r", '', $c['headline']));
        if (count($headline) > 2) { throw new RuntimeException('Nagłówek może mieć maksymalnie dwie linie.'); }

        $canvas->asset($c['logo_pl'], [50, 5, 226, 190]);
        foreach ($headline as $i => $line) { $canvas->text($line, [328, 48+28*$i, 410, 28], 20, $white, false); }
        $canvas->text($date, [328, 119, 410, 40], 32, $white, false);
        $canvas->pill(767, 31, 310, 102, 26, $white);
        $canvas->text('ZAPRASZAMY NA STOISKO', [750, 154, 168, 27], 10, $white, true, $c['font_small']);
        $canvas->pill(930, 153, 147, 30, 15, $white);
        $canvas->rectangle(1111, 110, 1189, 184, $black);
        $canvas->asset($c['pwe_logo'], [1120, 119, 62, 58]);
    },
];
