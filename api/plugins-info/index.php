
<?php
/**
 * Legacy direct endpoint.
 * Prefer /wp-json/pwe-system/v1/plugins-info because security plugins
 * may block direct PHP execution under wp-content.
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, max-age=0');
header('X-Content-Type-Options: nosniff');

/**
 * Send a JSON response and terminate the request.
 */
function pwe_plugins_info_reply(int $code, array $data): void
{
    http_response_code($code);

    echo json_encode(
        $data,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
    );

    exit;
}

// Locate the WordPress bootstrap file.
$wpLoad = dirname(__DIR__, 5) . '/wp-load.php';

if (!is_file($wpLoad)) {
    pwe_plugins_info_reply(500, [
        'status' => 'error',
        'message' => 'Brak wp-load.php.',
    ]);
}

// Load WordPress and the plugin information handler.
require_once $wpLoad;
require_once __DIR__ . '/handler.php';

// Verify the API key before processing the request.
if (!pwe_plugins_info_authorized()) {
    pwe_plugins_info_reply(401, [
        'status' => 'error',
        'message' => 'Brak autoryzacji.',
    ]);
}

$method = $_SERVER['REQUEST_METHOD'] ?? '';

// Handle plugin information requests.
if ($method === 'GET') {
    pwe_plugins_info_reply(
        200,
        pwe_plugins_info_status(($_GET['refresh'] ?? '') === '1')
    );
}

// Handle manual plugin update requests.
if ($method === 'POST') {
    $body = json_decode((string) file_get_contents('php://input'), true);

    // Validate the JSON request body.
    if (!is_array($body)) {
        pwe_plugins_info_reply(422, [
            'status' => 'error',
            'message' => 'Nieprawidłowy JSON.',
        ]);
    }

    list($status, $data) = pwe_plugins_info_update((string) ($body['slug'] ?? ''));

    pwe_plugins_info_reply($status, $data);
}

// Reject unsupported HTTP methods.
header('Allow: GET, POST');

pwe_plugins_info_reply(405, [
    'status' => 'error',
    'message' => 'Niedozwolona metoda.',
]);
