# `modules/forms-audit/traits/trait-pwe-forms-audit-data.php`

Plik first-party PWE System w kategorii `forms-audit`.

## Metadane

- **Kategoria:** `forms-audit`
- **Rozmiar:** 28963 B
- **Liczba linii:** 878
- **Źródło:** `modules/forms-audit/traits/trait-pwe-forms-audit-data.php`

## Typy i metody

### trait `PWE_System_Forms_Audit_Data_Trait` — linia 7

  - `private get_audit_session_cache_key($active_form_ids)` — linia 9
  - `private decode_audit_session_cache($packed)` — linia 20
  - `private encode_audit_session_cache($rows)` — linia 48
  - `public clear_audit_session_cache()` — linia 67
  - `private prime_audit_session_cache($active_form_ids, $force_refresh = false)` — linia 90
  - `private build_audit_session_rows($active_form_ids)` — linia 137
  - `private get_search_entry_ids($search, $active_form_ids, $selected_form_id = 0)` — linia 261
  - `private get_form_registration_stats($form_id, $feeds)` — linia 312
  - `private get_redirect_value_for_entry($form_id, $entry_id, $feeds)` — linia 479
  - `private get_entries_page($active_form_ids, $selected_form_id, $search, $status_filter, $notification_filter, $page)` — linia 505
  - `private compare_entry_qr_light($form_id, $entry_id, $feeds, $saved_value)` — linia 603
  - `private get_pwe_feeds($form_id)` — linia 634
  - `private get_feed_name($feed)` — linia 654
  - `private get_feed_custom_keys($feed)` — linia 661
  - `private get_email_field_ids($form)` — linia 682
  - `private get_entry_email($entry, $email_field_ids)` — linia 695
  - `private get_legacy_derived_qr_value($entry_id, $feeds)` — linia 708
  - `private get_entry_saved_qr($entry_id, $feeds)` — linia 736
  - `private extract_qr_value($qr_url)` — linia 817
  - `private compare_entry_qr($form_id, $entry, $feeds, $saved_value)` — linia 833
  - `private get_table_names()` — linia 864

## Funkcje globalne

- Brak funkcji globalnych.

## Rejestracje WordPress / GF

- Brak wykrytych/reprodukowanych rejestracji.

## Wybrane wywołania statyczne

- `GFAPI::get_forms()`
- `GFAPI::get_form()`
- `GFAPI::get_feeds()`
- `GFFormsModel::get_entry_table_name()`
- `GFFormsModel::get_entry_meta_table_name()`

## API WordPress / GF rozpoznane heurystycznie

- `wp_get_session_token()`
- `get_current_user_id()`
- `wp_json_encode()`
- `delete_transient()`
- `get_transient()`
- `set_transient()`
- `is_wp_error()`
- `gform_get_meta()`
- `wp_parse_url()`

## Dołączane pliki / wyrażenia include

- Brak.

## Tabele / źródła SQL

- `gf_entry`
- `gf_entry_meta`

## Uwagi

- Symbole są wyciągane przez `token_get_all`; traity są zachowane z `kind=trait`.
- Dynamiczne rejestracje AJAX z map/stałych są rozwinięte w `hooks.json` i `entrypoints.json`.
- Dokładny Call Graph powstaje niezależnie w Code Indexerze.
