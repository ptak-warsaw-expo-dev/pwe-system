# Testy PWE System

Panel **PWE System → Testy** (`admin.php?page=pwe-system-tests`) wymaga `manage_options`. Moduł ładuje `PWE_System::init()`.

## Struktura

- `tests-module.php` — inicjalizacja i jawne ładowanie klas.
- `admin/` — panel, ikony i modal.
- `assets/css/tests.css` — style; nieruchomy nagłówek i stopka modala, przewijana treść.
- `assets/js/` — UI → widok QR → modal → kontroler testów.
- `checks/` — dziewięć kontroli diagnostycznych.
- `core/diagnostic-runner.php` — AJAX, uruchamianie, cache i zasoby.
- `core/diagnostic-report.php` — raport i chroniony odczyt.

## Uruchamianie i raport

Akcja AJAX i nonce: `pwe_system_tests_diagnostic`. Kontrole działają pojedynczo; Page Health działa partiami. Runner blokuje wysyłkę `wp_mail` podczas testu. Wyniki przechowywane są przez 24 godziny w transientach.

Raport `uploads/pwe-system/pwe-system-diagnostic.json` zawiera ostatnie zakończone wyniki każdego testu. „Wyczyść wyniki” usuwa cache, pozostawia raport. Przycisk „Otwórz raport JSON” po prawej stronie szybkich akcji otwiera go w nowej karcie przez `admin-post.php`, po sprawdzeniu uprawnień i nonce. Brak pliku daje czytelny komunikat.

Endpoint `GET /wp-json/pwe-system/v1/diagnostic` wymaga klucza `PWE_API_KEY_3` w nagłówku `Authorization: Bearer …` lub parametrze `token`. Katalog raportu chroni `.htaccess`; na Nginx potrzebna jest analogiczna blokada dostępu do plików.

## Zakres kontroli

- Page Health najpierw pobiera aktywne języki WPML, następnie wybiera ich adresy z `website-translation.json`. Timeout HTTP wynosi 10 sekund. Bieżący host z ustawienia WordPress `home` jest prawidłowy także na środowisku testowym. Obce domeny localhost/dev/staging/test nadal są zgłaszane.
- Linki: wewnętrzny `badge_generator_local` i treści bez linków są pomijane. `{embed_url}` bez dodatkowych parametrów jest poprawne dla wszystkich języków. Abroad dopuszcza języki poza PL. Analizowane są pola właściwe dla typu potwierdzenia.
- Spójność tłumaczeń korzysta z `PWE_MULTILANG_PATH . 'modules/forms/form-templates'` i porównuje klucze języków w plikach powiadomień.
- Zgodność języków powiadomień korzysta z polityki i metadanych szablonu, a przy ich braku z języków wygenerowanych powiadomień aktualnego wariantu. Zależności formularzy są doładowywane w razie potrzeby. Sprawdza kompletność, nadmiarowe pozycje, duplikaty i aktywność. W widoku jeden rodzaj powiadomienia zajmuje jeden wiersz z połączonymi językami. Najpoważniejszy status jest zachowany; szczegóły i pełne dane pozostają dostępne. WPML ma jeden wiersz zgodności aktywnych języków z obsługiwanymi, bez wymogu aktywowania wszystkich języków.
- Pozostałe kontrole: konfiguracja feedów QR, placeholdery, merge tagi powiadomień i potwierdzeń oraz identyfikatory pól conditional logic.

Nową kontrolę dodaj w `checks/`, dołącz w `tests-module.php` i zarejestruj w `PWE_System_Tests_Runner::definitions()`.
