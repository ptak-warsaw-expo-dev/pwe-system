# `modules/forms-audit/traits/trait-pwe-forms-audit-render.php`

Plik first-party PWE System w kategorii `forms-audit`.

## Metadane

- **Kategoria:** `forms-audit`
- **Rozmiar:** 55944 B
- **Liczba linii:** 1331
- **Źródło:** `modules/forms-audit/traits/trait-pwe-forms-audit-render.php`

## Typy i metody

### trait `PWE_System_Forms_Audit_Render_Trait` — linia 7

  - `private render_styles()` — linia 9
  - `private render_forms_table($forms)` — linia 349
  - `private render_form_registration_stats($stats)` — linia 453
  - `private render_entries_table($forms)` — linia 472
  - `private render_resend_tools($selected_form_id = 0, $status_filter = '', $notification_filter = '', $search = '')` — linia 769
  - `private render_notification_column($match, $resend_notification_names = [], $form = [], $entry_id = 0)` — linia 1067
  - `private render_notification_match($match)` — linia 1085
  - `private render_filters($active_forms, $selected_form_id, $search, $status_filter, $notification_filter, $per_page)` — linia 1133
  - `private render_export_button($selected_form_id, $search)` — linia 1183
  - `private render_pagination($total, $page, $selected_form_id, $search, $status_filter, $notification_filter, $per_page)` — linia 1203
  - `private render_feeds_for_entry($feeds, $status_class = '')` — linia 1243
  - `private render_saved_qr_history($qr_url, $qr_value, $resend_qr_url, $resend_qr_value)` — linia 1272
  - `private render_saved_qr($qr_url, $qr_value)` — linia 1298
  - `private render_comparison_status($status)` — linia 1319

## Funkcje globalne

- Brak funkcji globalnych.

## Rejestracje WordPress / GF

- Brak wykrytych/reprodukowanych rejestracji.

## Wybrane wywołania statyczne

- `GFAPI::get_entry()`

## API WordPress / GF rozpoznane heurystycznie

- `esc_html()`
- `esc_attr()`
- `sanitize_text_field()`
- `wp_unslash()`
- `sanitize_key()`
- `is_wp_error()`
- `gform_get_meta()`
- `wp_json_encode()`
- `esc_url()`
- `admin_url()`
- `wp_create_nonce()`
- `wp_nonce_url()`
- `add_query_arg()`

## Dołączane pliki / wyrażenia include

- Brak.

## Tabele / źródła SQL

- Brak statycznie rozpoznanych.

## Uwagi

- Symbole są wyciągane przez `token_get_all`; traity są zachowane z `kind=trait`.
- Dynamiczne rejestracje AJAX z map/stałych są rozwinięte w `hooks.json` i `entrypoints.json`.
- Dokładny Call Graph powstaje niezależnie w Code Indexerze.
