<?php
if (!defined('ABSPATH')) { exit; }

/** Read-only inspection of persisted Multilang forms and QR feeds. */
final class PWE_System_Tests_QR_Feeds
{
    public static function run(): array
    {
        if (!class_exists('GFAPI')) {
            return ['status' => 'error', 'message' => 'Gravity Forms jest niedostępne. Nie sprawdzono feedów.'];
        }
        if (!shortcode_exists('trade_fair_feed_prefix')) {
            return ['status' => 'error', 'message' => 'Shortcode [trade_fair_feed_prefix] nie jest zarejestrowany.'];
        }
        $prefix = trim((string) do_shortcode('[trade_fair_feed_prefix]'));
        if ($prefix === '' || strpos($prefix, '[trade_fair_feed_prefix') !== false || strip_tags($prefix) !== $prefix) {
            return ['status' => 'error', 'message' => 'Shortcode [trade_fair_feed_prefix] nie zwrócił poprawnego prefiksu tekstowego.'];
        }
        // null includes active and inactive forms; false excludes trash.
        $forms = GFAPI::get_forms(null, false);
        if (is_wp_error($forms) || !is_array($forms)) {
            return ['status' => 'error', 'message' => 'Nie udało się odczytać formularzy.'];
        }
        $checked = []; $errors = []; $warnings = []; $formCount = 0; $feedCount = 0; $templateQr = []; $legacyTemplates = null;
        foreach ($forms as $summary) {
            $id = (int) ($summary['id'] ?? 0);
            $form = GFAPI::get_form($id);
            if (is_wp_error($form) || !is_array($form)) {
                $errors[] = "Formularz #$id: nie udało się odczytać danych i flagi Multilang.";
                continue;
            }
            if (empty($form['pwe_multilang_managed']) || !empty($form['is_trash'])) { continue; }
            $formCount++;
            $title = '#' . $id . ' · ' . ($form['title'] ?? 'Bez nazwy');
            // A saved template identity is authoritative; never infer QR from the feed itself.
            try {
                if (!class_exists('PWE_Multilang_Form_Template_Registry')) {
                    throw new RuntimeException('Rejestr szablonów Forms jest niedostępny.');
                }
                $slug = (string) ($form['pwe_multilang_template_slug'] ?? '');
                if ($slug !== '') {
                    if (!array_key_exists($slug, $templateQr)) {
                        $provider = PWE_Multilang_Form_Template_Registry::providers()[$slug] ?? null;
                        if (!$provider) { throw new RuntimeException('Nie znaleziono przypisanego szablonu: ' . $slug . '.'); }
                        $payload = $provider->payload(PWE_Multilang_Year_Resolver::configured());
                        $templateQr[$slug] = !empty($payload['qr']['enabled']);
                    }
                    $qrEnabled = $templateQr[$slug];
                } else {
                    // Older managed forms may have no identity; use the existing registry matcher.
                    if ($legacyTemplates === null) {
                        $legacyTemplates = PWE_Multilang_Form_Template_Registry::getTemplates(null, $forms);
                    }
                    $matches = [];
                    foreach ($legacyTemplates as $template) {
                        foreach ($template['targets'] ?? [] as $target) {
                            if ((int) ($target['form_id'] ?? 0) === $id) {
                                $matches[] = !empty($target['payload']['qr']['enabled']);
                            }
                        }
                    }
                    if (count($matches) !== 1) { throw new RuntimeException('Nie można jednoznacznie przypisać formularza do szablonu.'); }
                    $qrEnabled = $matches[0];
                }
            } catch (Throwable $error) {
                $warnings[] = $title . ': ' . $error->getMessage();
                $checked[] = ['title' => $title, 'status' => 'warning', 'message' => 'Nie sprawdzono feedu: ' . $error->getMessage()];
                continue;
            }
            if (!$qrEnabled) {
                $checked[] = ['title' => $title, 'status' => 'skipped', 'message' => 'QR wyłączony lub niezdefiniowany w szablonie'];
                continue;
            }
            $expected = $prefix . str_pad((string) $id, 3, '0', STR_PAD_LEFT);
            $idValid = $id >= 1 && $id <= 999;
            if (!$idValid) { $errors[] = "$title: ID formularza nie mieści się w trzech cyfrach (001–999)."; }
            $feeds = GFAPI::get_feeds(null, $id, null, null);
            if (is_wp_error($feeds) && $feeds->get_error_code() === 'not_found') { $feeds = []; }
            if (is_wp_error($feeds) || !is_array($feeds)) {
                $errors[] = "$title: nie udało się odczytać feedów QR.";
                $checked[] = ['title' => $title, 'status' => 'error', 'message' => 'Błąd odczytu feedów.'];
                continue;
            }
            $qrFeeds = array_filter($feeds, static fn($feed) => is_array($feed)
                && (int) ($feed['form_id'] ?? 0) === $id
                && in_array($feed['addon_slug'] ?? '', ['pwe_qr', 'qr-code'], true));
            if (!$qrFeeds) {
                $errors[] = "$title: brak zapisanego feedu QR.";
                $checked[] = ['title' => $title, 'status' => 'error', 'message' => 'Brak feedu QR.', 'expected' => $expected . ' + rnd[5 cyfr]', 'actual' => null];
            }
            foreach ($qrFeeds as $feed) {
                $feedCount++;
                $fields = $feed['meta']['qrcodeFields'] ?? [];
                $first = is_array($fields[0] ?? null) ? $fields[0] : [];
                $second = is_array($fields[1] ?? null) ? $fields[1] : [];
                $savedPrefix = $first['custom_key'] ?? null;
                $savedRnd = $second['custom_key'] ?? null;
                $issues = [];
                if (!$idValid) { $issues[] = 'ID formularza musi mieć maksymalnie 3 cyfry.'; }
                if ($savedPrefix !== $expected) { $issues[] = 'Prefiks + ID różni się od oczekiwanej wartości.'; }
                if (!is_string($savedRnd) || preg_match('/\Arnd[0-9]{5}\z/', $savedRnd) !== 1) { $issues[] = 'Druga część musi mieć postać rnd i dokładnie 5 cyfr.'; }
                foreach ([$first, $second] as $index => $field) {
                    if (($field['key'] ?? '') !== 'gf_custom' || ($field['value'] ?? '') !== 'id') {
                        $issues[] = 'Część ' . ($index + 1) . ': wymagane key=gf_custom i value=id.';
                    }
                }
                if (!is_array($fields) || count($fields) !== 2) { $issues[] = 'Konfiguracja powinna zawierać dokładnie dwie części kodu.'; }
                $feedTitle = $title . ' / feed #' . ($feed['id'] ?? '?') . ' · ' . ($feed['meta']['feedName'] ?? $feed['addon_slug']);
                foreach ($issues as $issue) { $errors[] = $feedTitle . ': ' . $issue; }
                $checked[] = [
                    'title' => $feedTitle, 'status' => $issues ? 'error' : 'success',
                    'expected' => ['prefix_id' => $expected, 'random_part' => 'rnd + 5 cyfr'],
                    'actual' => ['prefix_id' => $savedPrefix, 'random_part' => $savedRnd],
                    'message' => $issues ? implode(' ', $issues) : 'Obie części zapisanej konfiguracji są poprawne.',
                    'fields' => $fields,
                ];
            }
        }
        return [
            'status' => $errors ? 'error' : ($warnings ? 'warning' : ($formCount ? 'success' : 'skipped')),
            'message' => $formCount ? "Sprawdzono formularze Multilang: $formCount; feedy QR: $feedCount. Problemy: " . count($errors) . '. Ostrzeżenia: ' . count($warnings) . '.' : ($errors ? 'Nie udało się sprawdzić wszystkich formularzy.' : 'Brak formularzy z flagą Multilang poza koszem.'),
            'details' => ['summary' => ['forms' => $formCount, 'feeds' => $feedCount], 'prefix_shortcode' => '[trade_fair_feed_prefix]', 'live_prefix' => $prefix, 'checked' => $checked, 'errors' => $errors, 'warnings' => $warnings],
            'suggestions' => $errors || $warnings ? ['Sprawdź wskazane formularze i feedy QR. Test tylko odczytuje konfigurację — niczego nie zmienia.'] : [],
        ];
    }
}
