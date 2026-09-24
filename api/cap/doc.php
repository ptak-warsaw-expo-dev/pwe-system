<?php

declare(strict_types=1);

/*
 * PWE CAP - Graphics API
 *
 * GET  -> status wymaganych grafik
 * POST -> podmiana wybranych grafik + zwrot świeżego statusu
 *
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept, X-PWE-API-Key');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

/*
 * Ładujemy WordPress, żeby mieć dostęp m.in. do ABSPATH i stałych.
 * __DIR__:
 * wp-content/plugins/pwe-system/api/cap
 */
$wpLoad = dirname(__DIR__, 5) . '/wp-load.php';

if (is_file($wpLoad)) {
    require_once $wpLoad;
}

$root = defined('ABSPATH')
    ? rtrim((string) ABSPATH, '/\\') . DIRECTORY_SEPARATOR
    : rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), '/\\') . DIRECTORY_SEPARATOR;

if ($root === DIRECTORY_SEPARATOR || !is_dir($root)) {
    pwe_system_api_images_response([
        'status' => 'error',
        'message' => 'Nie udało się ustalić katalogu WordPress.',
    ], 500);
}

/*
 * Jedna lista jest źródłem prawdy zarówno dla statusu, jak i uploadu.
 */
$assets = [
    '/doc/logo.webp' => [700, 400, 100],
    '/doc/logo-en.webp' => [700, 400, 100],
    '/doc/logo-color.webp' => [700, 400, 100],
    '/doc/logo-color-en.webp' => [700, 400, 100],

    '/doc/logo.png' => [700, 400, 100],
    '/doc/logo-en.png' => [700, 400, 100],
    '/doc/logo-color.png' => [700, 400, 100],
    '/doc/logo-color-en.png' => [700, 400, 100],

    '/doc/kongres.webp' => [600, 240, 100],
    '/doc/kongres-en.webp' => [600, 240, 100],
    '/doc/kongres-color.webp' => [600, 240, 100],
    '/doc/kongres-color-en.webp' => [600, 240, 100],

    '/doc/kongres.png' => [600, 240, 100],
    '/doc/kongres-en.png' => [600, 240, 100],
    '/doc/kongres-color.png' => [600, 240, 100],
    '/doc/kongres-color-en.png' => [600, 240, 100],

    '/doc/logo-half.webp' => [700, 400, 100],
    '/doc/logo-half-en.webp' => [700, 400, 100],

    '/doc/favicon.png' => [500, 500, 100],
    '/doc/favicon-color.png' => [500, 500, 100],

    '/doc/logo-x-pl.webp' => [null, 100, 100],
    '/doc/logo-x-en.webp' => [null, 100, 100],

    '/doc/header.jpg' => [600, 300, 100],
    '/doc/header_en.jpg' => [600, 300, 100],
    '/doc/plan.jpg' => [600, 300, 100],
    '/doc/plan-en.jpg' => [600, 300, 100],
    '/doc/vip.jpg' => [600, 300, 100],
    '/doc/vip-en.jpg' => [600, 300, 100],

    '/doc/background.webp' => [2000, 1000, 150],
    '/doc/header_mobile.webp' => [600, 1200, 150],

    '/doc/kafelek.jpg' => [500, 500, 150],
    '/doc/kafelek-en.jpg' => [500, 500, 150],
    '/doc/kafelek_kalendarz.webp' => [500, 500, 150],
    '/doc/kafelek_kalendarz_en.webp' => [500, 500, 150],

    '/doc/badge-mockup.webp' => [800, 947, 150],
    '/doc/badge-mockup-en.webp' => [800, 947, 150],
    '/doc/badgevipmockup.webp' => [800, 947, 150],
    '/doc/badgevipmockup-en.webp' => [800, 947, 150],

    /*
     * W aktualnym Blade sekcja fa-promo nie ma limitu KB.
     */
    '/doc/wypromuj/wypromuj_1200_pl.png' => [1200, 200, null],
    '/doc/wypromuj/wypromuj_1200_en.png' => [1200, 200, null],
    '/doc/wypromuj/wypromuj_800_pl.png' => [800, 800, null],
    '/doc/wypromuj/wypromuj_800_en.png' => [800, 800, null],

    '/doc/photo-calendar.webp' => [1000, 667, 100],
];


/*
 * Adresaci powiadomień po udanym zapisie grafik.
 * Możesz dopisać dowolną liczbę adresów.
 */
$notificationEmails = [
    'oliwia.ptasinska@warsawexpo.eu',
];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    pwe_system_api_images_response(
        pwe_system_api_images_status($root, $assets)
    );
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    pwe_system_api_images_response([
        'status' => 'error',
        'message' => 'Niedozwolona metoda.',
    ], 405);
}

/*
 * POST może nadpisywać pliki, więc musi być autoryzowany.
 *
 * Ten sam PWE_API_KEY_2 ustaw:
 * - na WordPressie,
 * - w .env aplikacji CAP.
 */
$expectedKey = '';

if (defined('PWE_API_KEY_2')) {
    $expectedKey = (string) constant('PWE_API_KEY_2');
}

if ($expectedKey === '') {
    $envKey = getenv('PWE_API_KEY_2');

    if ($envKey !== false) {
        $expectedKey = (string) $envKey;
    }
}

$providedKey = (string) (
    $_SERVER['HTTP_X_PWE_API_KEY']
    ?? ''
);

if (
    $expectedKey === '' ||
    $providedKey === '' ||
    !hash_equals($expectedKey, $providedKey)
) {
    pwe_system_api_images_response([
        'status' => 'error',
        'message' => 'Brak autoryzacji.',
    ], 401);
}

if (
    empty($_FILES['files']) ||
    empty($_POST['paths']) ||
    !is_array($_POST['paths'])
) {
    pwe_system_api_images_response([
        'status' => 'error',
        'message' => 'Brak plików lub ścieżek.',
    ], 422);
}

$files = pwe_system_api_images_normalize_files($_FILES['files']);
$paths = array_values($_POST['paths']);

if (count($files) !== count($paths)) {
    pwe_system_api_images_response([
        'status' => 'error',
        'message' => 'Liczba plików i ścieżek nie jest zgodna.',
    ], 422);
}

$allowedMime = [
    'png' => ['image/png'],
    'jpg' => ['image/jpeg', 'image/pjpeg'],
    'jpeg' => ['image/jpeg', 'image/pjpeg'],
    'webp' => ['image/webp'],
];

$prepared = [];
$errors = [];

foreach ($files as $index => $file) {

    $path = '/' . ltrim(
        str_replace('\\', '/', (string) $paths[$index]),
        '/'
    );

    /*
     * Najważniejsze zabezpieczenie:
     * można zapisać wyłącznie pliki istniejące na naszej whitelist.
     */
    if (!isset($assets[$path])) {
        $errors[] = $path . ': niedozwolona ścieżka.';
        continue;
    }

    if (
        !isset($file['error']) ||
        (int) $file['error'] !== UPLOAD_ERR_OK
    ) {
        $errors[] = $path . ': błąd uploadu.';
        continue;
    }

    $tmp = (string) ($file['tmp_name'] ?? '');

    if ($tmp === '' || !is_uploaded_file($tmp)) {
        $errors[] = $path . ': nieprawidłowy plik tymczasowy.';
        continue;
    }

    [$expectedWidth, $expectedHeight, $maxKb] = $assets[$path];

    $targetExtension = strtolower(
        (string) pathinfo($path, PATHINFO_EXTENSION)
    );

    $sourceExtension = strtolower(
        (string) pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION)
    );

    if ($sourceExtension !== $targetExtension) {
        $errors[] =
            $path .
            ': wymagany format .' .
            $targetExtension .
            ', otrzymano .' .
            $sourceExtension .
            '.';
        continue;
    }

    if (
        $maxKb !== null &&
        (int) ($file['size'] ?? 0) > ((int) $maxKb * 1024)
    ) {
        $errors[] =
            $path .
            ': plik przekracza limit ' .
            $maxKb .
            ' KB.';
        continue;
    }

    $imageSize = @getimagesize($tmp);

    if (!$imageSize) {
        $errors[] = $path . ': plik nie jest prawidłowym obrazem.';
        continue;
    }

    $actualWidth = (int) $imageSize[0];
    $actualHeight = (int) $imageSize[1];

    if (
        ($expectedWidth !== null && $actualWidth !== (int) $expectedWidth) ||
        ($expectedHeight !== null && $actualHeight !== (int) $expectedHeight)
    ) {
        $errors[] =
            $path .
            ': zły rozmiar ' .
            $actualWidth .
            '×' .
            $actualHeight .
            ', wymagane ' .
            ($expectedWidth ?? 'dowolna') .
            '×' .
            ($expectedHeight ?? 'dowolna') .
            '.';
        continue;
    }

    $mime = '';

    if (class_exists('finfo')) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = (string) $finfo->file($tmp);
    }

    if (
        $mime !== '' &&
        isset($allowedMime[$targetExtension]) &&
        !in_array($mime, $allowedMime[$targetExtension], true)
    ) {
        $errors[] =
            $path .
            ': nieprawidłowy MIME ' .
            $mime .
            '.';
        continue;
    }

    $target = $root . ltrim($path, '/');

    $prepared[] = [
        'path' => $path,
        'tmp' => $tmp,
        'target' => $target,
        'action' => is_file($target) ? 'replaced' : 'added',
    ];
}

/*
 * Niczego nie zapisujemy, jeżeli choć jeden plik nie przeszedł walidacji.
 * Lepiej odrzucić cały zestaw niż zostawić pół aktualizacji.
 */
if ($errors) {
    pwe_system_api_images_response([
        'status' => 'error',
        'message' => 'Nie zapisano plików. Popraw błędy i spróbuj ponownie.',
        'errors' => $errors,
    ], 422);
}

$staged = [];
$uploadedPaths = [];
$uploadedDetails = [];

foreach ($prepared as $item) {

    $directory = dirname($item['target']);

    if (
        !is_dir($directory) &&
        !@mkdir($directory, 0755, true) &&
        !is_dir($directory)
    ) {
        pwe_system_api_images_cleanup_staged($staged);

        pwe_system_api_images_response([
            'status' => 'error',
            'message' => 'Nie udało się utworzyć katalogu: ' . $directory,
        ], 500);
    }

    $stage = $item['target'] .
        '.pwe-upload-' .
        bin2hex(random_bytes(6));

    if (!move_uploaded_file($item['tmp'], $stage)) {
        pwe_system_api_images_cleanup_staged($staged);

        pwe_system_api_images_response([
            'status' => 'error',
            'message' => 'Nie udało się przygotować pliku: ' . $item['path'],
        ], 500);
    }

    @chmod($stage, 0644);

    $staged[] = [
        'path' => $item['path'],
        'stage' => $stage,
        'target' => $item['target'],
        'action' => $item['action'],
    ];
}

/*
 * Wszystkie pliki są już zwalidowane i zapisane jako pliki tymczasowe.
 * Dopiero teraz podmieniamy produkcyjne assety.
 */
foreach ($staged as $item) {

    if (!@rename($item['stage'], $item['target'])) {

        /*
         * Fallback dla systemów, które nie pozwalają rename() nad istniejącym plikiem.
         */
        if (
            is_file($item['target']) &&
            !@unlink($item['target'])
        ) {
            pwe_system_api_images_cleanup_staged($staged);

            pwe_system_api_images_response([
                'status' => 'error',
                'message' => 'Nie udało się podmienić: ' . $item['path'],
            ], 500);
        }

        if (!@rename($item['stage'], $item['target'])) {
            pwe_system_api_images_cleanup_staged($staged);

            pwe_system_api_images_response([
                'status' => 'error',
                'message' => 'Nie udało się zapisać: ' . $item['path'],
            ], 500);
        }
    }

    @chmod($item['target'], 0644);

    $uploadedPaths[] = $item['path'];
    $uploadedDetails[] = [
        'path' => $item['path'],
        'action' => $item['action'],
    ];
}

$status = pwe_system_api_images_status($root, $assets);

$status['uploaded'] = count($uploadedPaths);
$status['uploaded_files'] = $uploadedPaths;
$status['uploaded_details'] = $uploadedDetails;
$status['message'] = 'Grafiki zostały zapisane.';

pwe_system_api_images_send_notification(
    $notificationEmails,
    $uploadedDetails,
    (string) ($_POST['changed_by_name'] ?? ''),
    (string) ($_POST['changed_by_email'] ?? '')
);

pwe_system_api_images_response($status);


/* ========================================================= */
/* FUNCTIONS                                                 */
/* ========================================================= */

function pwe_system_api_images_status(string $root, array $assets): array
{
    $missing = 0;
    $wrong = 0;
    $files = [];

    foreach ($assets as $path => $rules) {

        [$expectedWidth, $expectedHeight, $maxKb] = $rules;

        $file = $root . ltrim($path, '/');

        $item = [
            'path' => $path,
            'missing' => false,
            'wrong' => false,
            'width' => null,
            'height' => null,
            'expected_width' => $expectedWidth,
            'expected_height' => $expectedHeight,
            'size_kb' => null,
            'max_kb' => $maxKb,
        ];

        if (!is_file($file)) {
            $missing++;
            $item['missing'] = true;
            $files[$path] = $item;
            continue;
        }

        $item['size_kb'] = round(
            filesize($file) / 1024,
            1
        );

        $size = @getimagesize($file);

        if (!$size) {
            $wrong++;
            $item['wrong'] = true;
            $files[$path] = $item;
            continue;
        }

        $item['width'] = (int) $size[0];
        $item['height'] = (int) $size[1];

        if (
            ($expectedWidth !== null &&
                $item['width'] !== (int) $expectedWidth) ||
            ($expectedHeight !== null &&
                $item['height'] !== (int) $expectedHeight)
        ) {
            $wrong++;
            $item['wrong'] = true;
        }

        $files[$path] = $item;
    }

    return [
        'status' => 'success',
        'missing' => $missing,
        'wrong' => $wrong,
        'files' => $files,
        'checked_at' => gmdate('c'),
    ];
}

function pwe_system_api_images_send_notification(
    array $recipients,
    array $uploadedDetails,
    string $changedByName = '',
    string $changedByEmail = ''
): void {
    $recipients = array_values(array_filter(array_map(
        'sanitize_email',
        $recipients
    )));

    if (!$recipients || !$uploadedDetails) {
        return;
    }

    $host = (string) ($_SERVER['HTTP_HOST'] ?? '');
    $siteUrl = function_exists('home_url')
        ? home_url('/')
        : (($host !== '') ? 'https://' . $host . '/' : '');

    $capUrl = $host !== ''
        ? 'https://cap.warsawexpo.eu/fairs/info/' . $host
        : 'https://cap.warsawexpo.eu/fairs/info/';

    $added = [];
    $replaced = [];

    foreach ($uploadedDetails as $item) {
        $path = (string) ($item['path'] ?? '');
        $action = (string) ($item['action'] ?? '');

        if ($path === '') {
            continue;
        }

        if ($action === 'added') {
            $added[] = $path;
        } else {
            $replaced[] = $path;
        }
    }

    $actor = trim($changedByName);

    if ($changedByEmail !== '') {
        $actor .= ($actor !== '' ? ' <' : '') .
            $changedByEmail .
            ($actor !== '' ? '>' : '');
    }

    if ($actor === '') {
        $actor = 'CAP';
    }

    $subject = '[PWE CAP] Zmieniono grafiki - ' .
        ($host !== '' ? $host : 'WordPress');

    $lines = [
        'Zapisano grafiki przez panel CAP.',
        '',
        'Strona WordPress: ' . ($siteUrl !== '' ? $siteUrl : $host),
        'Strona w CAP: ' . $capUrl,
        'Wykonał: ' . $actor,
        'Data: ' . wp_date('Y-m-d H:i:s'),
        '',
    ];

    if ($added) {
        $lines[] = 'DODANE (' . count($added) . '):';

        foreach ($added as $path) {
            $lines[] = '- ' . $path;
        }

        $lines[] = '';
    }

    if ($replaced) {
        $lines[] = 'PODMIENIONE (' . count($replaced) . '):';

        foreach ($replaced as $path) {
            $lines[] = '- ' . $path;
        }

        $lines[] = '';
    }

    $lines[] = 'Łącznie zapisano: ' . count($uploadedDetails);

    wp_mail(
        $recipients,
        $subject,
        implode("\n", $lines),
        ['Content-Type: text/plain; charset=UTF-8']
    );
}

function pwe_system_api_images_normalize_files(array $files): array
{
    if (!isset($files['name'])) {
        return [];
    }

    if (!is_array($files['name'])) {
        return [$files];
    }

    $normalized = [];
    $count = count($files['name']);

    for ($i = 0; $i < $count; $i++) {
        $normalized[] = [
            'name' => $files['name'][$i] ?? '',
            'type' => $files['type'][$i] ?? '',
            'tmp_name' => $files['tmp_name'][$i] ?? '',
            'error' => $files['error'][$i] ?? UPLOAD_ERR_NO_FILE,
            'size' => $files['size'][$i] ?? 0,
        ];
    }

    return $normalized;
}

function pwe_system_api_images_cleanup_staged(array $staged): void
{
    foreach ($staged as $item) {
        if (!empty($item['stage']) && is_file($item['stage'])) {
            @unlink($item['stage']);
        }
    }
}

function pwe_system_api_images_response(array $data, int $code = 200): void
{
    http_response_code($code);

    echo json_encode(
        $data,
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );

    exit;
}
