# `modules/resend/resend-module.php`

Plik first-party PWE System w kategorii `resend`.

## Metadane

- **Kategoria:** `resend`
- **Rozmiar:** 1359 B
- **Liczba linii:** 50
- **Źródło:** `modules/resend/resend-module.php`

## Typy i metody

### class `PWE_System_Resend` — linia 7

  - `public static init()` — linia 17
  - `private static includes()` — linia 28
  - `public static render_admin_page()` — linia 45

## Funkcje globalne

- Brak funkcji globalnych.

## Rejestracje WordPress / GF

- Brak wykrytych/reprodukowanych rejestracji.

## Wybrane wywołania statyczne

- `self::includes()`
- `PWE_System_Resend_Actions::init()`
- `PWE_System_Resend_Admin::render()`

## API WordPress / GF rozpoznane heurystycznie

- Brak.

## Dołączane pliki / wyrażenia include

- `s()`
- `s(): void`
- `_once $dir . 'form-repository.php'`
- `_once $dir . 'field-locator.php'`
- `_once $dir . 'notification-name.php'`
- `_once $dir . 'conditional-logic.php'`
- `_once $dir . 'job-lock.php'`
- `_once $dir . 'resend-job.php'`
- `_once $dir . 'gravity-entries.php'`
- `_once $dir . 'notification-matcher.php'`
- `_once $dir . 'job-runner.php'`
- `_once $dir . 'resend-actions.php'`
- `_once $dir . 'admin-page.php'`

## Tabele / źródła SQL

- Brak statycznie rozpoznanych.

## Uwagi

- Symbole są wyciągane przez `token_get_all`; traity są zachowane z `kind=trait`.
- Dynamiczne rejestracje AJAX z map/stałych są rozwinięte w `hooks.json` i `entrypoints.json`.
- Dokładny Call Graph powstaje niezależnie w Code Indexerze.
