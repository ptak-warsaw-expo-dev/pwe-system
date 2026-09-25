# `core/class-pwe-system.php`

Plik first-party PWE System w kategorii `core`.

## Metadane

- **Kategoria:** `core`
- **Rozmiar:** 2816 B
- **Liczba linii:** 68
- **Źródło:** `core/class-pwe-system.php`

## Typy i metody

### class `PWE_System` — linia 6

  - `public static init()` — linia 9
  - `public static activate()` — linia 52
  - `public static sync_capabilities()` — linia 59

## Funkcje globalne

- Brak funkcji globalnych.

## Rejestracje WordPress / GF

- **action:** `init` — linia 23

## Wybrane wywołania statyczne

- `self::sync_capabilities()`
- `PWE_System_Replace_Content::init()`
- `PWE_System_Resend::init()`
- `PWE_System_Admin::init()`
- `PWE_System_Doc_Manager::init()`
- `PWE_System_Doc_Manager::ensure_doc_directory()`
- `PWE_System_Doc_Manager::install_log_table()`

## API WordPress / GF rozpoznane heurystycznie

- `add_action()`
- `get_role()`

## Dołączane pliki / wyrażenia include

- `_once PWE_SYSTEM_PATH . 'core/class-pwe-system-admin-access.php'`
- `_once PWE_SYSTEM_PATH . 'core/class-pwe-system-admin-ui.php'`
- `_once PWE_SYSTEM_PATH . 'core/class-pwe-system-admin.php'`
- `_once PWE_SYSTEM_PATH . 'modules/doc-manager/class-pwe-system-doc-manager.php'`
- `_once PWE_SYSTEM_PATH . 'modules/replace-content/replace-content-module.php'`
- `_once PWE_SYSTEM_PATH . 'modules/resend/resend-module.php'`
- `_once $forms_audit_module`
- `_once PWE_SYSTEM_PATH . 'modules/shortcodes/backend-shortcodes.php'`
- `_once PWE_SYSTEM_PATH . 'modules/shortcodes/class-shortcodes.php'`

## Tabele / źródła SQL

- Brak statycznie rozpoznanych.

## Uwagi

- Symbole są wyciągane przez `token_get_all`; traity są zachowane z `kind=trait`.
- Dynamiczne rejestracje AJAX z map/stałych są rozwinięte w `hooks.json` i `entrypoints.json`.
- Dokładny Call Graph powstaje niezależnie w Code Indexerze.
