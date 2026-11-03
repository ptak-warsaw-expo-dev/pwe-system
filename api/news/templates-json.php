<?php
/**
 * Temporary test endpoint for CAP news templates.
 * Saves one JSON file per template and post in this directory.
 */

require_once __DIR__ . '/../../../../../wp-load.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function pwe_templates_test_response($code, $body)
{
    http_response_code($code);
    echo wp_json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    pwe_templates_test_response(405, ['status' => 'error', 'message' => 'Dozwolona metoda: POST']);
}

// The same API key as the legacy news/index.php endpoint.
$key = $_SERVER['HTTP_X_API_KEY'] ?? $_GET['key'] ?? '';
if (!defined('PWE_API_KEY_2') || !$key || !hash_equals((string) PWE_API_KEY_2, (string) $key)) {
    pwe_templates_test_response(401, ['status' => 'error', 'message' => 'Nieprawidlowy klucz API']);
}

$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload) || json_last_error() !== JSON_ERROR_NONE) {
    pwe_templates_test_response(422, ['status' => 'error', 'message' => 'Nieprawidlowy JSON']);
}

$template = $payload['template'] ?? '';
if (!in_array($template, ['summary', 'preview', 'medal-ceremony'], true)) {
    pwe_templates_test_response(422, ['status' => 'error', 'message' => 'Nieznany typ szablonu']);
}

$action = $payload['action'] ?? 'upsert';
if (!in_array($action, ['upsert', 'delete'], true)) {
    pwe_templates_test_response(422, ['status' => 'error', 'message' => 'Nieznana akcja']);
}

if ($action === 'upsert' && (!isset($payload['data']) || !is_array($payload['data']))) {
    pwe_templates_test_response(422, ['status' => 'error', 'message' => 'Brak obiektu data']);
}

// An explicit slug keeps the filename stable even when the title changes.
$source_slug = $payload['slug'] ?? $payload['data']['slug'] ?? $payload['data']['title'] ?? '';
$slug = sanitize_title((string) $source_slug);
if ($slug === '') {
    pwe_templates_test_response(422, ['status' => 'error', 'message' => 'Brak slug lub data.title']);
}

$filename = $template . '-' . $slug . '.json';
$path = __DIR__ . DIRECTORY_SEPARATOR . $filename;

if ($action === 'delete') {
    if (is_file($path) && !unlink($path)) {
        pwe_templates_test_response(500, ['status' => 'error', 'message' => 'Nie mozna usunac pliku']);
    }
    pwe_templates_test_response(200, [
        'status' => 'success', 'action' => 'delete', 'file' => $filename,
    ]);
}

// Do not add metadata or "type" to the user's data.
$document = [
    'template' => $template,
    'data' => $payload['data'],
];
$json = wp_json_encode($document, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if ($json === false || file_put_contents($path, $json . "\n", LOCK_EX) === false) {
    pwe_templates_test_response(500, ['status' => 'error', 'message' => 'Nie mozna zapisac JSON. Sprawdz uprawnienia katalogu API']);
}

pwe_templates_test_response(200, [
    'status' => 'success',
    'action' => 'upsert',
    'template' => $template,
    'file' => $filename,
    'message' => 'Plik JSON zapisany',
]);