<?php
if (!defined('ABSPATH')) { exit; }

/** Local GD renderer. All coordinates are native template pixels. */
final class PWE_Images_Canvas {
    private $image;
    private $font;
    private const SCALE = 3;

    public function asset(string $path, array $box, bool $cover = false): void {
        $info = @getimagesize($path);
        if (!$info || $info[0] * $info[1] > 20000000 || filesize($path) > 25000000) {
            throw new RuntimeException('Nieprawidłowy lub zbyt duży obraz: ' . basename($path));
        }
        $loaders = [IMAGETYPE_PNG => 'imagecreatefrompng', IMAGETYPE_JPEG => 'imagecreatefromjpeg', IMAGETYPE_WEBP => 'imagecreatefromwebp'];
        $loader = $loaders[$info[2]] ?? '';
        $src = $loader && function_exists($loader) ? @$loader($path) : false;
        if (!$src) { throw new RuntimeException('Nie można odczytać assetu: ' . basename($path)); }
        try {
            [$x, $y, $w, $h] = array_map(static function ($value) { return $value * self::SCALE; }, $box);
            if ($cover) {
                $scale = max($w / $info[0], $h / $info[1]);
                $sw = $w / $scale; $sh = $h / $scale;
                imagecopyresampled($this->image, $src, $x, $y, (int)(($info[0]-$sw)/2), (int)(($info[1]-$sh)/2), $w, $h, (int)$sw, (int)$sh);
            } else {
                $scale = min($w / $info[0], $h / $info[1]);
                $dw = (int)round($info[0]*$scale); $dh = (int)round($info[1]*$scale);
                imagecopyresampled($this->image, $src, (int)($x+($w-$dw)/2), (int)($y+($h-$dh)/2), 0, 0, $dw, $dh, $info[0], $info[1]);
            }
        } finally { imagedestroy($src); }
    }

    public function text(string $text, array $box, int $size, int $color, bool $center = true, ?string $font = null): void {
        [$x, $y, $w, $h] = array_map(static function ($value) { return $value * self::SCALE; }, $box);
        $font = $font ?? $this->font;
        $size *= self::SCALE;
        for (; $size >= 8 * self::SCALE; --$size) {
            $b = imagettfbbox($size, 0, $font, $text);
            if (!$b) { throw new RuntimeException('Nie można zmierzyć tekstu.'); }
            $tw = $b[2]-$b[0]; $th = $b[1]-$b[7];
            if ($tw <= $w && $th <= $h) { break; }
        }
        if ($size < 8 * self::SCALE) { throw new RuntimeException('Tekst nie mieści się w szablonie.'); }
        if (!imagettftext($this->image, $size, 0, (int)($x+($center ? ($w-$tw)/2 : 0)-$b[0]), (int)($y+($h-$th)/2-$b[7]), $color, $font, $text)) {
            throw new RuntimeException('Nie można narysować tekstu.');
        }
    }

    public function pill(int $x, int $y, int $w, int $h, int $r, int $color): void {
        $x *= self::SCALE; $y *= self::SCALE;
        $w *= self::SCALE; $h *= self::SCALE; $r *= self::SCALE;
        $r = min($r, (int)floor((min($w, $h) - 1) / 2));
        imagefilledrectangle($this->image, $x+$r, $y, $x+$w-$r-1, $y+$h-1, $color);
        imagefilledrectangle($this->image, $x, $y+$r, $x+$w-1, $y+$h-$r-1, $color);
        foreach ([$x+$r, $x+$w-$r-1] as $cx) {
            foreach ([$y+$r, $y+$h-$r-1] as $cy) { imagefilledellipse($this->image, $cx, $cy, 2*$r, 2*$r, $color); }
        }
    }

    public function rectangle(int $x1, int $y1, int $x2, int $y2, int $color): void {
        imagefilledrectangle($this->image, $x1 * self::SCALE, $y1 * self::SCALE,
            ($x2 + 1) * self::SCALE - 1, ($y2 + 1) * self::SCALE - 1, $color);
    }

    public function colorAlpha(int $r, int $g, int $b, int $alpha): int {
        return imagecolorallocatealpha($this->image, $r, $g, $b, $alpha);
    }

    public function color(int $r, int $g, int $b): int {
        return imagecolorallocate($this->image, $r, $g, $b);
    }

    public function render(array $template, string $date, array $c, string $target): void {
        $width = $template['width'];
        $height = $template['height'];
        $this->font = $c['font_main'];
        $this->image = imagecreatetruecolor($width * self::SCALE, $height * self::SCALE);
        if (!$this->image) { throw new RuntimeException('Brak pamięci dla obrazu.'); }
        try {
            ($template['draw'])($this, $date, $c);
            // Supersampling smooths rounded shapes, logo contours and typography.
            // PNG compression is lossless; quality comes from resampling, not compression level.
            $output = imagecreatetruecolor($width, $height);
            if (!$output) { throw new RuntimeException('Brak pamięci dla docelowego PNG.'); }
            try {
                if (!imagecopyresampled($output, $this->image, 0, 0, 0, 0, $width, $height,
                    $width * self::SCALE, $height * self::SCALE)) {
                    throw new RuntimeException('Nie udało się przeskalować grafiki.');
                }
                if (!imagepng($output, $target, 6)) { throw new RuntimeException('Zapis PNG nie powiódł się.'); }
            } finally { imagedestroy($output); }
            $info = @getimagesize($target);
            if (!$info || $info[0] !== $width || $info[1] !== $height || $info[2] !== IMAGETYPE_PNG) { throw new RuntimeException('Weryfikacja PNG nie powiodła się.'); }
        } finally { imagedestroy($this->image); }
    }
}
