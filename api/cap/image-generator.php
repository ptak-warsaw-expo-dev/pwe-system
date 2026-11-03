<?php

declare(strict_types=1);

// PWE CAP - on-demand regeneration of "Wypromuj sie" graphics.
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, max-age=0');
header('Vary: Origin');

$origin = (string) ($_SERVER['HTTP_ORIGIN'] ?? '');
if ($origin === 'https://cap.warsawexpo.eu') {
    header('Access-Control-Allow-Origin: https://cap.warsawexpo.eu');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Accept, X-PWE-API-Key');
}

function pwe_promo_api_reply(int $code, array $body): void {
    http_response_code($code);
    echo json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

if ($origin !== '' && $origin !== 'https://cap.warsawexpo.eu') {
    pwe_promo_api_reply(403, ['status' => 'error', 'message' => 'Niedozwolone pochodzenie żądania.']);
}
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST, OPTIONS');
    pwe_promo_api_reply(405, ['status' => 'error', 'message' => 'Użyj metody POST.']);
}

$wpLoad = dirname(__DIR__, 5) . '/wp-load.php';
if (!is_file($wpLoad)) {
    pwe_promo_api_reply(500, ['status' => 'error', 'message' => 'Brak wp-load.php.']);
}
require_once $wpLoad;

$expectedKey = defined('PWE_API_KEY_2') ? (string) constant('PWE_API_KEY_2') : '';
if ($expectedKey === '') {
    $environment = getenv('PWE_API_KEY_2');
    $expectedKey = $environment === false ? '' : (string) $environment;
}
$providedKey = (string) ($_SERVER['HTTP_X_PWE_API_KEY'] ?? '');
if ($expectedKey === '' || $providedKey === '' || !hash_equals($expectedKey, $providedKey)) {
    pwe_promo_api_reply(401, ['status' => 'error', 'message' => 'Brak autoryzacji.']);
}

if (!class_exists('PWE_Images_Generator')) {
    $module = dirname(__DIR__, 2) . '/modules/images-generator/images-generator.php';
    if (!is_file($module)) {
        pwe_promo_api_reply(503, ['status' => 'error', 'message' => 'Moduł Images Generator nie jest dostępny.']);
    }
    require_once $module;
}
if (!class_exists('PWE_Images_Generator')) {
    pwe_promo_api_reply(503, ['status' => 'error', 'message' => 'Klasa generatora nie została załadowana.']);
}

try {
    // Force bypasses date check; the generator itself protects against concurrent runs.
    $result = PWE_Images_Generator::generate(true);
    if (is_wp_error($result)) {
        pwe_promo_api_reply(422, ['status' => 'error', 'message' => $result->get_error_message()]);
    }
    if ($result !== true) {
        pwe_promo_api_reply(500, ['status' => 'error', 'message' => 'Generator nie potwierdził regeneracji.']);
    }
    pwe_promo_api_reply(200, [
        'status' => 'success',
        'message' => 'Grafiki „Wypromuj się” zostały zregenerowane.',
        'generated_at' => wp_date('Y-m-d H:i:s'),
    ]);
} catch (Throwable $e) {
    error_log('PWE CAP promo regeneration: ' . $e->getMessage());
    pwe_promo_api_reply(500, ['status' => 'error', 'message' => 'Błąd regeneracji grafik. Sprawdź logi WordPress.']);
}
