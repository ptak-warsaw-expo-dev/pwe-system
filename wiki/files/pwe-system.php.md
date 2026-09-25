# `pwe-system.php`

Główny bootstrap pluginu.

## Metadane

- **Kategoria:** `bootstrap`
- **Rozmiar:** 1319 B
- **Liczba linii:** 39
- **Źródło:** `pwe-system.php`

## Typy i metody

- Brak klas/traitów.

## Funkcje globalne

- Brak funkcji globalnych.

## Rejestracje WordPress / GF

- **action:** `plugins_loaded` — linia 38

## Wybrane wywołania statyczne

- Brak.

## API WordPress / GF rozpoznane heurystycznie

- `plugin_dir_path()`
- `plugin_dir_url()`
- `determine_locale()`
- `add_action()`

## Dołączane pliki / wyrażenia include

- `_once PWE_SYSTEM_PATH . 'core/class-pwe-system-functions.php'`
- `_once PWE_SYSTEM_PATH . 'core/class-pwe-system.php'`
- `_once PWE_SYSTEM_PATH . 'core/class-pwe-system-updater.php'`

## Tabele / źródła SQL

- Brak statycznie rozpoznanych.

## Uwagi

- Symbole są wyciągane przez `token_get_all`; traity są zachowane z `kind=trait`.
- Dynamiczne rejestracje AJAX z map/stałych są rozwinięte w `hooks.json` i `entrypoints.json`.
- Dokładny Call Graph powstaje niezależnie w Code Indexerze.
