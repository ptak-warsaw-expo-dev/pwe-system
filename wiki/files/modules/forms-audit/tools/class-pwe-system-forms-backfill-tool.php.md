# `modules/forms-audit/tools/class-pwe-system-forms-backfill-tool.php`

Plik first-party PWE System w kategorii `forms-audit`.

## Metadane

- **Kategoria:** `forms-audit`
- **Rozmiar:** 11503 B
- **Liczba linii:** 344
- **Źródło:** `modules/forms-audit/tools/class-pwe-system-forms-backfill-tool.php`

## Typy i metody

### class `PWE_System_Forms_Backfill_Tool` — linia 11

  - `public __construct($qr)` — linia 18
  - `public enqueue_assets()` — linia 27
  - `public render_audit_section()` — linia 46
  - `public ajax_scan()` — linia 87
  - `public ajax_generate()` — linia 129
  - `private authorize_request()` — linia 183
  - `private get_active_feeds(int $form_id)` — linia 193
  - `private save_qr_code_link_to_entry_meta(array $entry, array $form)` — linia 208
  - `private build_qr_image_url(string $value, string $label, int $size, string $logo_url = '')` — linia 254
  - `private get_table_names()` — linia 279
  - `private count_entries(int $form_id, bool $missing_only)` — linia 293
  - `private get_missing_entry_ids(int $form_id, int $limit)` — linia 321

## Funkcje globalne

- Brak funkcji globalnych.

## Rejestracje WordPress / GF

- **action:** `pwe_system_forms_audit_tools` — linia 21
- **action:** `admin_enqueue_scripts` — linia 22
- **action:** `wp_ajax_pwe_system_forms_backfill_scan` — linia 23
- **action:** `wp_ajax_pwe_system_forms_backfill_generate` — linia 24

## Wybrane wywołania statyczne

- `GFAPI::get_forms()`
- `GFAPI::get_form()`
- `GFAPI::get_entry()`
- `GFAPI::get_feeds()`
- `GFFormsModel::get_entry_table_name()`
- `GFFormsModel::get_entry_meta_table_name()`

## API WordPress / GF rozpoznane heurystycznie

- `add_action()`
- `is_admin()`
- `sanitize_key()`
- `wp_enqueue_script()`
- `wp_localize_script()`
- `admin_url()`
- `wp_create_nonce()`
- `current_user_can()`
- `wp_send_json_error()`
- `wp_send_json_success()`
- `is_wp_error()`
- `do_action()`
- `check_ajax_referer()`
- `esc_url_raw()`
- `gform_update_meta()`
- `gform_get_meta()`
- `wp_salt()`
- `add_query_arg()`
- `home_url()`

## Dołączane pliki / wyrażenia include

- Brak.

## Tabele / źródła SQL

- `gf_entry`
- `gf_entry_meta`

## Uwagi

- Symbole są wyciągane przez `token_get_all`; traity są zachowane z `kind=trait`.
- Dynamiczne rejestracje AJAX z map/stałych są rozwinięte w `hooks.json` i `entrypoints.json`.
- Dokładny Call Graph powstaje niezależnie w Code Indexerze.
