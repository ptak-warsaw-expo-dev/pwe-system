# `modules/replace-content/core/ajax-actions.php`

Plik first-party PWE System w kategorii `replace-content`.

## Metadane

- **Kategoria:** `replace-content`
- **Rozmiar:** 12265 B
- **Liczba linii:** 365
- **Źródło:** `modules/replace-content/core/ajax-actions.php`

## Typy i metody

### class `PWE_System_Replace_Content_Ajax` — linia 9

  - `public static init()` — linia 18
  - `public static start()` — linia 24
  - `public static step()` — linia 72
  - `private static process_job_step(string $job_key, string $token, array $job, ?int $client_processed, string &$error_message)` — linia 162
  - `private static is_valid_job($job)` — linia 258
  - `private static response_from_job(array $job, string $token)` — linia 272
  - `private static error_response_data(string $message, string $token, $job, bool $retryable)` — linia 305
  - `private static authorize()` — linia 321
  - `private static job_key(string $token)` — linia 330
  - `private static lock_key(string $token)` — linia 335
  - `private static acquire_lock(string $token)` — linia 340
  - `private static release_lock(string $token, string $lock_name)` — linia 352

## Funkcje globalne

- Brak funkcji globalnych.

## Rejestracje WordPress / GF

- **action:** `wp_ajax_pwe_replace_content_start` — linia 20
- **action:** `wp_ajax_pwe_replace_content_step` — linia 21

## Wybrane wywołania statyczne

- `self::authorize()`
- `PWE_System_Replace_Content_Config::get_map()`
- `PWE_System_Replace_Content_Service::build_plan()`
- `PWE_System_Replace_Content_Service::prepare_job()`
- `PWE_System_Replace_Content_Service::empty_result()`
- `self::job_key()`
- `self::is_valid_job()`
- `self::response_from_job()`
- `self::acquire_lock()`
- `self::error_response_data()`
- `self::process_job_step()`
- `self::release_lock()`
- `PWE_System_Replace_Content_Service::recover_page_result()`
- `PWE_System_Replace_Content_Service::process_page()`
- `PWE_System_Replace_Content_Service::add_page_result()`
- `PWE_System_Admin_Access::is_allowed()`
- `self::lock_key()`

## API WordPress / GF rozpoznane heurystycznie

- `add_action()`
- `wp_send_json_success()`
- `wp_generate_password()`
- `set_transient()`
- `wp_send_json_error()`
- `sanitize_key()`
- `wp_unslash()`
- `get_transient()`
- `check_ajax_referer()`
- `get_current_user_id()`

## Dołączane pliki / wyrażenia include

- Brak.

## Tabele / źródła SQL

- Brak statycznie rozpoznanych.

## Uwagi

- Symbole są wyciągane przez `token_get_all`; traity są zachowane z `kind=trait`.
- Dynamiczne rejestracje AJAX z map/stałych są rozwinięte w `hooks.json` i `entrypoints.json`.
- Dokładny Call Graph powstaje niezależnie w Code Indexerze.
