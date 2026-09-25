# `modules/forms-audit/pwe-forms-audit-module.php`

Plik first-party PWE System w kategorii `forms-audit`.

## Metadane

- **Kategoria:** `forms-audit`
- **Rozmiar:** 7458 B
- **Liczba linii:** 217
- **Źródło:** `modules/forms-audit/pwe-forms-audit-module.php`

## Typy i metody

### class `PWE_System_Forms_Audit_Tool` — linia 19

  - `public __construct($qr)` — linia 42
  - `public register_submenu()` — linia 55
  - `public render_page()` — linia 67
  - `public enqueue_assets()` — linia 108
  - `public ajax_load_audit()` — linia 129
### class `PWE_System_Forms_Audit_Module` — linia 185

  - `public static boot()` — linia 192
  - `public static is_ready()` — linia 210

## Funkcje globalne

- Brak funkcji globalnych.

## Rejestracje WordPress / GF

- **action:** `admin_menu` — linia 45
- **action:** `admin_post_pwe_qr_export_mismatches` — linia 46
- **action:** `wp_ajax_pwe_qr_resend_notifications` — linia 47
- **action:** `wp_ajax_pwe_qr_bulk_language_preview` — linia 48
- **action:** `wp_ajax_pwe_system_forms_audit_load` — linia 49
- **action:** `admin_enqueue_scripts` — linia 50
- **action:** `pwe_system_forms_audit_cache_invalidate` — linia 51
- **action:** `plugins_loaded` — linia 216

## Wybrane wywołania statyczne

- `PWE_System_Admin::render_module_header()`
- `GFAPI::get_forms()`
- `PWE_QR_Gravity_Forms::get_instance()`
- `PWE_System_Forms_Audit_Module::boot()`

## API WordPress / GF rozpoznane heurystycznie

- `add_action()`
- `current_user_can()`
- `wp_die()`
- `do_action()`
- `is_admin()`
- `sanitize_key()`
- `wp_enqueue_script()`
- `wp_localize_script()`
- `admin_url()`
- `wp_create_nonce()`
- `wp_send_json_error()`
- `check_ajax_referer()`
- `wp_unslash()`
- `wp_send_json_success()`

## Dołączane pliki / wyrażenia include

- `_once __DIR__ . '/traits/trait-pwe-forms-audit-render.php'`
- `_once __DIR__ . '/traits/trait-pwe-forms-audit-notifications.php'`
- `_once __DIR__ . '/traits/trait-pwe-forms-audit-data.php'`
- `_once __DIR__ . '/traits/trait-pwe-forms-audit-export.php'`
- `_once __DIR__ . '/tools/class-pwe-system-forms-backfill-tool.php'`

## Tabele / źródła SQL

- Brak statycznie rozpoznanych.

## Uwagi

- Symbole są wyciągane przez `token_get_all`; traity są zachowane z `kind=trait`.
- Dynamiczne rejestracje AJAX z map/stałych są rozwinięte w `hooks.json` i `entrypoints.json`.
- Dokładny Call Graph powstaje niezależnie w Code Indexerze.
