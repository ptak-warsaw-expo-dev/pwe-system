# `core/class-pwe-system-admin-ui.php`

Plik first-party PWE System w kategorii `core`.

## Metadane

- **Kategoria:** `core`
- **Rozmiar:** 1836 B
- **Liczba linii:** 49
- **Źródło:** `core/class-pwe-system-admin-ui.php`

## Typy i metody

### class `PWE_System_Admin_UI` — linia 7

  - `public static notice(string $class, string $iconClass, string $contentHtml)` — linia 9
  - `public static status(string $tag, string $variant, string $iconClass, string $contentHtml)` — linia 17
  - `public static checkbox(array $inputAttributes, string $containerClass = 'pwe-checkbox-container', string $wrapperTag = 'label')` — linia 26
  - `public static button(array $attributes, string $innerHtml)` — linia 35
  - `public static attributes(array $attributes)` — linia 40

## Funkcje globalne

- Brak funkcji globalnych.

## Rejestracje WordPress / GF

- Brak wykrytych/reprodukowanych rejestracji.

## Wybrane wywołania statyczne

- `self::attributes()`

## API WordPress / GF rozpoznane heurystycznie

- `esc_attr()`

## Dołączane pliki / wyrażenia include

- Brak.

## Tabele / źródła SQL

- Brak statycznie rozpoznanych.

## Uwagi

- Symbole są wyciągane przez `token_get_all`; traity są zachowane z `kind=trait`.
- Dynamiczne rejestracje AJAX z map/stałych są rozwinięte w `hooks.json` i `entrypoints.json`.
- Dokładny Call Graph powstaje niezależnie w Code Indexerze.
