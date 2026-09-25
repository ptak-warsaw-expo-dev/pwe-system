# `modules/resend/core/job-lock.php`

Plik first-party PWE System w kategorii `resend`.

## Metadane

- **Kategoria:** `resend`
- **Rozmiar:** 5717 B
- **Liczba linii:** 216
- **Źródło:** `modules/resend/core/job-lock.php`

## Typy i metody

### class `PWE_System_Resend_Job_Lock` — linia 9

  - `public static acquire()` — linia 17
  - `public static refresh(string $token)` — linia 61
  - `public static release(string $token)` — linia 94
  - `private static key()` — linia 132
  - `private static value(string $token)` — linia 137
  - `private static is_valid($value)` — linia 142
  - `private static is_owned_by($value, string $token)` — linia 151
  - `private static read_raw(string $key)` — linia 157
  - `private static compare_and_delete(string $key, string $expected_raw)` — linia 171
  - `private static compare_and_update(string $key, string $expected_raw, string $next_raw)` — linia 189
  - `private static clear_option_cache(string $key)` — linia 209

## Funkcje globalne

- Brak funkcji globalnych.

## Rejestracje WordPress / GF

- Brak wykrytych/reprodukowanych rejestracji.

## Wybrane wywołania statyczne

- `self::refresh()`
- `self::key()`
- `self::value()`
- `self::read_raw()`
- `self::is_valid()`
- `self::compare_and_delete()`
- `self::is_owned_by()`
- `self::compare_and_update()`
- `self::clear_option_cache()`

## API WordPress / GF rozpoznane heurystycznie

- `wp_generate_uuid4()`
- `add_option()`
- `wp_cache_delete()`

## Dołączane pliki / wyrażenia include

- Brak.

## Tabele / źródła SQL

- Brak statycznie rozpoznanych.

## Uwagi

- Symbole są wyciągane przez `token_get_all`; traity są zachowane z `kind=trait`.
- Dynamiczne rejestracje AJAX z map/stałych są rozwinięte w `hooks.json` i `entrypoints.json`.
- Dokładny Call Graph powstaje niezależnie w Code Indexerze.
