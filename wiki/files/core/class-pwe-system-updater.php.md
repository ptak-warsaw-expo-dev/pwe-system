# `core/class-pwe-system-updater.php`

Plik first-party PWE System w kategorii `core`.

## Metadane

- **Kategoria:** `core`
- **Rozmiar:** 1862 B
- **Liczba linii:** 63
- **Źródło:** `core/class-pwe-system-updater.php`

## Typy i metody

### class `PWE_System_Updater` — linia 7

  - `public __construct()` — linia 11
  - `public setup_updater()` — linia 18

## Funkcje globalne

- Brak funkcji globalnych.

## Rejestracje WordPress / GF

- **action:** `plugins_loaded` — linia 12

## Wybrane wywołania statyczne

- `Puc_v4_Factory::buildUpdateChecker()`
- `PWE_System_Functions::get_database_meta_data()`

## API WordPress / GF rozpoznane heurystycznie

- `add_action()`

## Dołączane pliki / wyrażenia include

- `_once $checker_file`

## Tabele / źródła SQL

- Brak statycznie rozpoznanych.

## Uwagi

- Symbole są wyciągane przez `token_get_all`; traity są zachowane z `kind=trait`.
- Dynamiczne rejestracje AJAX z map/stałych są rozwinięte w `hooks.json` i `entrypoints.json`.
- Dokładny Call Graph powstaje niezależnie w Code Indexerze.
