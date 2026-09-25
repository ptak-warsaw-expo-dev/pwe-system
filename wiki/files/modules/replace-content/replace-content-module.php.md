# `modules/replace-content/replace-content-module.php`

Plik first-party PWE System w kategorii `replace-content`.

## Metadane

- **Kategoria:** `replace-content`
- **Rozmiar:** 913 B
- **Liczba linii:** 34
- **Źródło:** `modules/replace-content/replace-content-module.php`

## Typy i metody

### class `PWE_System_Replace_Content` — linia 16

  - `public static init()` — linia 20
  - `public static render_admin_page()` — linia 29

## Funkcje globalne

- Brak funkcji globalnych.

## Rejestracje WordPress / GF

- Brak wykrytych/reprodukowanych rejestracji.

## Wybrane wywołania statyczne

- `PWE_System_Replace_Content_Ajax::init()`
- `PWE_System_Replace_Content_Admin_Page::render()`

## API WordPress / GF rozpoznane heurystycznie

- Brak.

## Dołączane pliki / wyrażenia include

- `_once __DIR__ . '/core/wpml-gateway.php'`
- `_once __DIR__ . '/core/page-locator.php'`
- `_once __DIR__ . '/core/page-meta-adapter.php'`
- `_once __DIR__ . '/core/language-helper.php'`
- `_once __DIR__ . '/core/replace-content-config.php'`
- `_once __DIR__ . '/core/replace-content-service.php'`
- `_once __DIR__ . '/core/ajax-actions.php'`
- `_once __DIR__ . '/core/admin-page.php'`

## Tabele / źródła SQL

- Brak statycznie rozpoznanych.

## Uwagi

- Symbole są wyciągane przez `token_get_all`; traity są zachowane z `kind=trait`.
- Dynamiczne rejestracje AJAX z map/stałych są rozwinięte w `hooks.json` i `entrypoints.json`.
- Dokładny Call Graph powstaje niezależnie w Code Indexerze.
