<?php
if (!defined('ABSPATH')) { exit; }

/** Local SVG sprite; reuses the admin panel's existing module artwork. */
final class PWE_System_Tests_Icons
{
    public static function icon(string $name): string
    {
        return '<svg class="diag-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><use href="#pwe-tests-icon-' . esc_attr($name) . '"></use></svg>';
    }

    public static function sprite(): void
    {
        $paths = [
            'check' => '<path d="m5 12 4 4L19 6"/>',
            'search' => '<circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/>',
            'play' => '<path d="m8 4 12 8-12 8Z"/>',
            'grid' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
            'list' => '<path d="M9 6h12M9 12h12M9 18h12M3 6h1M3 12h1M3 18h1"/>',
            'trash' => '<path d="M3 6h18M9 6V3h6v3M5 6l1 15h12l1-15M10 10v7m4-7v7"/>',
            'plus' => '<path d="M12 4v16M4 12h16"/>',
            'close' => '<path d="m6 6 12 12M6 18 18 6"/>',
            'arrow' => '<path d="M4 12h16m-6-6 6 6-6 6"/>',
            'chevron' => '<path d="m6 9 6 6 6-6"/>',
            'language' => '<path d="M3 5h12M9 3v2M6 5c0 5 4 9 9 10M12 5c0 5-4 9-9 10M13 21l4-10 4 10m-7-3h6"/>',
            'shield' => '<path d="m12 3 8 3v5c0 5-4 8-8 10-4-2-8-5-8-10V6Z"/><path d="m8 11 3 3 5-5"/>',
            'tools' => '<path d="m14 6 4 4 3-3a6 6 0 0 1-8 8l-6 6-4-4 6-6a6 6 0 0 1 8-8Z"/>',
            'code' => '<path d="m8 6-6 6 6 6m8-12 6 6-6 6M14 3l-4 18"/>',
            'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
            'info' => '<circle cx="12" cy="12" r="9"/><path d="M12 11v6m0-10v1"/>',
        ];
        $paths += [
            'tests' => $paths['shield'], 'forms' => $paths['list'], 'entries' => $paths['list'],
            'sync' => '<path d="M20 7a9 9 0 0 0-15-2L2 8m0-5v5h5M4 17a9 9 0 0 0 15 2l3-3m0 5v-5h-5"/>',
            'warning' => '<path d="m12 3 10 18H2Z M12 9v5m0 3v1"/>',
            'gf_addon' => $paths['grid'], 'log' => $paths['list'],
            'mail' => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 5 10 8L22 5"/>',
        ];        echo '<svg class="diag-sprite" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" width="0" height="0"><defs>';
        foreach ($paths as $name => $path) { echo '<symbol id="pwe-tests-icon-' . esc_attr($name) . '" viewBox="0 0 24 24">' . $path . '</symbol>'; }
        echo '</defs></svg>';
    }
}
