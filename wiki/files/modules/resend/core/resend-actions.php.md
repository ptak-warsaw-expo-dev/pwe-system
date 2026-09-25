# `modules/resend/core/resend-actions.php`

Plik first-party PWE System w kategorii `resend`.

## Metadane

- **Kategoria:** `resend`
- **Rozmiar:** 4051 B
- **Liczba linii:** 123
- **Źródło:** `modules/resend/core/resend-actions.php`

## Typy i metody

### class `PWE_System_Resend_Actions` — linia 9

  - `public static init()` — linia 13
  - `public static handle()` — linia 23
  - `public static admin_url(array $args = [])` — linia 99
  - `public static action_form(string $action, string $label, string $class = 'button')` — linia 104
  - `private static safe_redirect(string $url)` — linia 117

## Funkcje globalne

- Brak funkcji globalnych.

## Rejestracje WordPress / GF

- **action:** `admin_init` — linia 20

## Wybrane wywołania statyczne

- `PWE_System_Admin_Access::is_allowed()`
- `PWE_System_Resend_Job::create()`
- `self::safe_redirect()`
- `self::admin_url()`
- `PWE_System_Resend_Job::mutate()`
- `PWE_System_Resend_Job_Runner::run()`
- `PWE_System_Resend_Job::clear_block()`
- `PWE_System_Resend_Job::delete()`

## API WordPress / GF rozpoznane heurystycznie

- `add_action()`
- `sanitize_key()`
- `wp_unslash()`
- `check_admin_referer()`
- `wp_die()`
- `admin_url()`
- `add_query_arg()`
- `esc_url()`
- `wp_nonce_field()`
- `esc_attr()`
- `esc_html()`
- `wp_safe_redirect()`

## Dołączane pliki / wyrażenia include

- `_all_entries']),`

## Tabele / źródła SQL

- Brak statycznie rozpoznanych.

## Uwagi

- Symbole są wyciągane przez `token_get_all`; traity są zachowane z `kind=trait`.
- Dynamiczne rejestracje AJAX z map/stałych są rozwinięte w `hooks.json` i `entrypoints.json`.
- Dokładny Call Graph powstaje niezależnie w Code Indexerze.
