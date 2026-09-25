<?php

if (!defined('ABSPATH')) {
    exit;
}

// The bundled Plugin Update Checker build is 4.9. Keep this small wrapper as
// the single loader used by PWE System instead of referencing a versioned path
// from the updater itself.
require_once __DIR__ . '/plugin-update-checker/load-v4p9.php';
