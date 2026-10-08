<?php
if (!defined('ABSPATH')) { exit; }

/** A request runs one unit; the browser queues groups without long AJAX requests. */
final class PWE_System_Tests_Runner
{
    public static function init(): void
    {
        add_action('wp_ajax_pwe_system_tests_diagnostic', [self::class, 'ajax']);
        add_action('admin_enqueue_scripts', [self::class, 'assets']);
    }

    public static function allowed(): bool
    {
        return current_user_can('manage_options');
    }

    public static function assets(): void
    {
        if (($_GET['page'] ?? '') !== PWE_System_Tests::PAGE_SLUG || !self::allowed()) { return; }
        $base = 'modules/tests/assets/';
        wp_enqueue_style('pwe-system-tests', PWE_SYSTEM_URL . $base . 'css/tests.css', ['pwe-system-admin'], (string) filemtime(PWE_SYSTEM_PATH . $base . 'css/tests.css'));
        $scripts = [
            'tests-ui' => [],
            'tests-qr-view' => ['pwe-system-tests-ui'],
            'tests-modal' => ['pwe-system-tests-ui', 'pwe-system-tests-qr-view'],
            'tests' => ['pwe-system-tests-modal'],
        ];
        foreach ($scripts as $name => $dependencies) {
            $file = $base . 'js/' . $name . '.js';
            wp_enqueue_script('pwe-system-' . $name, PWE_SYSTEM_URL . $file, $dependencies, (string) filemtime(PWE_SYSTEM_PATH . $file), true);
        }
    }
    public static function definitions(): array
    {
        return [
            'page-health' => [
                'id' => 'page-health',
                'name' => 'Page Health',
                'category' => 'Strony i zasoby',
                'component' => 'Pages',
                'critical' => true,
                'description' => 'Sprawdza adresy z website-translation.json tylko dla aktywnych języków WPML: HTTP, błędy PHP w odpowiedzi, lokalne JS/CSS i adresy localhost/dev/staging. Analiza po PHP, bez uruchamiania JavaScript. Test działa partiami.',
                'callback' => [PWE_System_Tests_Page_Health::class, 'run'],
            ],
            'qr-feeds' => [
                'id' => 'qr-feeds',
                'name' => 'QR: konfiguracja zapisanych feedów',
                'category' => 'QR i feedy',
                'component' => 'QR',
                'critical' => true,
                'description' => 'Porównuje rzeczywiste feedy formularzy z flagą Multilang: wartość [trade_fair_feed_prefix] + trzycyfrowe ID oraz rnd + 5 cyfr. Bez zapisu zmian.',
                'callback' => [PWE_System_Tests_QR_Feeds::class, 'run'],
            ],
            'translations-consistency' => [
                'id' => 'translations-consistency',
                'name' => 'Tłumaczenia: spójność powiadomień formularzy',
                'category' => 'Tłumaczenia i języki',
                'component' => 'Translations',
                'critical' => true,
                'description' => 'Porównuje każdy plik translations.php w powiadomieniach szablonów formularzy: czy pl, en i pozostałe języki tłumaczą dokładnie ten sam zestaw zmiennych, osobno dla każdego szablonu i powiadomienia. Bez zapisu zmian.',
                'callback' => [PWE_System_Tests_Translations_Consistency::class, 'run'],
            ],
            'notification-placeholders' => [
                'id' => 'notification-placeholders',
                'name' => 'Tłumaczenia: pozostałości placeholderów w powiadomieniach',
                'category' => 'Tłumaczenia i języki',
                'component' => 'Notifications',
                'critical' => true,
                'description' => 'Skanuje zapisane powiadomienia formularzy z flagą Multilang w poszukiwaniu niepodmienionych placeholderów szablonu tłumaczeń w postaci {{...}}. Bez zapisu zmian.',
                'callback' => [PWE_System_Tests_Notification_Placeholders::class, 'run'],
            ],
            'notification-languages' => [
                'id' => 'notification-languages',
                'name' => 'Tłumaczenia: zgodność powiadomień z językami WPML',
                'category' => 'Tłumaczenia i języki',
                'component' => 'Notifications',
                'critical' => true,
                'description' => 'Sprawdza obsługę aktywnych języków WPML, języki zapisanych powiadomień oraz braki, duplikaty i nadmiarowe powiadomienia względem właściwego wariantu szablonu. Bez zapisu zmian.',
                'callback' => [PWE_System_Tests_Notification_Languages::class, 'run'],
            ],
            'notification-merge-tags' => [
                'id' => 'notification-merge-tags',
                'name' => 'Powiadomienia: poprawność merge tagów pól',
                'category' => 'Testy formularzy',
                'component' => 'Notifications',
                'critical' => true,
                'description' => 'Sprawdza, czy merge tagi pól w powiadomieniach formularzy Multilang wskazują istniejące pola. Tagi systemowe Gravity Forms są pomijane. Bez zapisu zmian.',
                'callback' => [PWE_System_Tests_Notification_Merge_Tags::class, 'run'],
            ],
            'confirmation-merge-tags' => [
                'id' => 'confirmation-merge-tags',
                'name' => 'Potwierdzenia: poprawność merge tagów pól',
                'category' => 'Testy formularzy',
                'component' => 'Notifications',
                'critical' => true,
                'description' => 'Sprawdza, czy merge tagi pól w potwierdzeniach formularzy Multilang wskazują istniejące pola. Tagi systemowe Gravity Forms są pomijane. Bez zapisu zmian.',
                'callback' => [PWE_System_Tests_Confirmation_Merge_Tags::class, 'run'],
            ],
            'conditional-logic-field-ids' => [
                'id' => 'conditional-logic-field-ids',
                'name' => 'Conditional logic: poprawność field ID',
                'category' => 'Testy formularzy',
                'component' => 'Forms',
                'critical' => true,
                'description' => 'Sprawdza field ID używane w conditional logic pól, powiadomień i potwierdzeń formularzy Multilang. Bez zapisu zmian.',
                'callback' => [PWE_System_Tests_Conditional_Logic_Field_Ids::class, 'run'],
            ],
            'notification-links' => [
                'id' => 'notification-links',
                'name' => 'Tłumaczenia: poprawność linków językowych w powiadomieniach',
                'category' => 'Tłumaczenia i języki',
                'component' => 'Pages',
                'critical' => true,
                'description' => 'Porównuje linki zapisane w powiadomieniach i potwierdzeniach formularzy z flagą Multilang z website-translation.json. Uwzględnia powiadomienia Abroad (bez PL), wewnętrzny Badge generator, przekierowania {embed_url} i treści bez linków. Sprawdza: czy każdy link do strony używa URL-a właściwego dla języka danego powiadomienia (np. de → /de/kontakt/, pl → /kontakt/), a nie URL-a innego języka. Bez zapisu zmian.',
                'callback' => [PWE_System_Tests_Notification_Links::class, 'run'],
            ],
        ];
    }

    private static function key(string $id): string
    {
        return 'pwe_system_diagnostic_' . md5(PWE_System_Tests::group() . '_' . $id);
    }

    public static function results(): array
    {
        $results = [];
        foreach (self::definitions() as $id => $test) {
            $result = get_transient(self::key($id));
            if (is_array($result)) { $results[$id] = $result; }
        }
        return $results;
    }

    public static function run(string $id, bool $continue = false): array
    {
        $test = self::definitions()[$id] ?? null;
        if (!$test) { throw new InvalidArgumentException('Nieznany test.'); }
        $start = microtime(true);
        // Guard the complete callback, including payload generation. Never invoke SMTP.
        $block = static function () { return true; };
        add_filter('pre_wp_mail', $block, PHP_INT_MAX);
        try {
            $requires_multilang = in_array($id, ['page-health', 'translations-consistency', 'notification-links', 'notification-languages'], true);
            if ($requires_multilang && (!defined('PWE_MULTILANG_PATH') || ($id === 'notification-languages' && !class_exists('PWE_Multilang_Language_Catalog')))) {
                $result = ['status' => 'skipped', 'message' => 'Ten test wymaga aktywnej wtyczki PWE Multilang.'];
            } else {
                $result = $id === 'page-health'
                    ? PWE_System_Tests_Page_Health::run($continue)
                    : call_user_func($test['callback']);
            }
        } catch (Throwable $error) {
            $result = ['status' => 'error', 'message' => $error->getMessage(),
                'details' => ['exception' => get_class($error), 'file' => basename($error->getFile()), 'line' => $error->getLine()],
                'suggestions' => ['Sprawdź wskazany komponent i ponów test.']];
        } finally {
            remove_filter('pre_wp_mail', $block, PHP_INT_MAX);
        }
        $result += ['details' => [], 'suggestions' => []];
        $result['execution_time'] ??= round((microtime(true) - $start) * 1000, 2);
        $result['timestamp'] = gmdate('c');
        set_transient(self::key($id), $result, DAY_IN_SECONDS);
        if (empty($result['continue']) && ($result['status'] ?? '') !== 'running') {
            try { PWE_System_Tests_Report::save(self::results(), self::definitions()); }
            catch (Throwable $error) {
                $result['details']['warnings'][] = 'Raport JSON: ' . $error->getMessage();
                if (in_array($result['status'] ?? '', ['success', 'skipped'], true)) { $result['status'] = 'warning'; }
                set_transient(self::key($id), $result, DAY_IN_SECONDS);
            }
        }
        return $result;
    }

    public static function run_group(array $ids): array
    {
        $results = [];
        foreach ($ids as $id) {
            $results[$id] = self::run($id);
            while (!empty($results[$id]['continue'])) { $results[$id] = self::run($id, true); }
        }
        return $results;
    }

    public static function run_all(): array { return self::run_group(array_keys(self::definitions())); }

    public static function ajax(): void
    {
        if (!self::allowed()) { wp_send_json_error(['message' => 'Brak uprawnień.'], 403); }
        check_ajax_referer('pwe_system_tests_diagnostic', 'nonce');
        $operation = sanitize_key(wp_unslash($_POST['operation'] ?? ''));
        $id = sanitize_text_field(wp_unslash($_POST['id'] ?? ''));
        try {
            if ($operation === 'clear') {
                foreach (self::definitions() as $key => $test) { delete_transient(self::key($key)); }
                wp_send_json_success([]);
            }
            if (!isset(self::definitions()[$id])) { throw new InvalidArgumentException('Nieznany test.'); }
            if ($operation !== 'run' && !($operation === 'continue' && $id === 'page-health')) { throw new InvalidArgumentException('Nieznana operacja.'); }
            wp_send_json_success(self::run($id, $operation === 'continue'));
        } catch (Throwable $error) {
            wp_send_json_error(['message' => $error->getMessage()], 400);
        }
    }
}
