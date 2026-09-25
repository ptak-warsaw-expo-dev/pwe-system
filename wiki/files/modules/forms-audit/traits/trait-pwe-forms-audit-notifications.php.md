# `modules/forms-audit/traits/trait-pwe-forms-audit-notifications.php`

Plik first-party PWE System w kategorii `forms-audit`.

## Metadane

- **Kategoria:** `forms-audit`
- **Rozmiar:** 73966 B
- **Liczba linii:** 2159
- **Źródło:** `modules/forms-audit/traits/trait-pwe-forms-audit-notifications.php`

## Typy i metody

### trait `PWE_System_Forms_Audit_Notifications_Trait` — linia 7

  - `public ajax_bulk_language_preview()` — linia 9
  - `private detect_language_from_source_url($source_url)` — linia 205
  - `private get_active_notifications_by_language($form)` — linia 223
  - `public ajax_resend_notifications()` — linia 260
  - `private add_admin_notifications_to_unsent_match($form, $entry, $match)` — linia 561
  - `private get_notification_for_entry($form, $entry, $entry_email)` — linia 616
  - `private get_notification_history($entry_id)` — linia 842
  - `private get_notification_error_message($entry_id)` — linia 859
  - `private has_notification_error($entry_id)` — linia 938
  - `private get_failed_notifications_from_gf_notes($form, $entry_id)` — linia 1008
  - `private get_notifications_from_gf_notes($form, $entry_id)` — linia 1097
  - `private notification_name_is_resend($name)` — linia 1170
  - `private get_notification_delivery_state($form, $entry_id)` — linia 1175
  - `private has_sent_notification($entry_id, $form = null)` — linia 1274
  - `private has_resend_notification($form, $entry_id)` — linia 1301
  - `private has_active_notification_error($form, $entry_id)` — linia 1307
  - `private get_entry_with_language_override($form, $entry, $lang)` — linia 1312
  - `private get_notifications_for_language($form, $lang, $entry = null)` — linia 1348
  - `private notification_contains_qr($notification)` — linia 1390
  - `private get_historical_notifications_for_entry($form, $entry)` — linia 1412
  - `private get_qr_resend_notifications_for_entry($form, $entry)` — linia 1461
  - `private get_never_sent_notifications_for_entry($form, $entry)` — linia 1495
  - `private get_resend_notifications_for_entry($form, $entry, $comparison = '')` — linia 1636
  - `private form_has_active_notifications($form)` — linia 1673
  - `private notification_conditional_logic_passes($notification, $form, $entry)` — linia 1693
  - `private normalize_notification_conditional_logic($logic, $form)` — linia 1751
  - `private hydrate_notification_conditional_entry($form, $entry, $logic)` — linia 1784
  - `private find_form_field_id_by_name($form, $name)` — linia 1850
  - `private get_form_field_names_by_id($form, $field_id)` — linia 1878
  - `private get_entry_named_value($form, $entry, $names)` — linia 1908
  - `private get_notification_logic_rule_value($form, $entry, $field_id)` — linia 1971
  - `private get_form_field_property($field, $property)` — linia 2025
  - `private resolve_notification_recipients($notification, $form, $entry)` — linia 2034
  - `private replace_notification_variables($value, $form, $entry)` — linia 2080
  - `private compare_rule_value($actual, $operator, $expected)` — linia 2093
  - `private recipient_array_contains($recipients, $target_email)` — linia 2124
  - `private email_list_contains($emails, $target_email)` — linia 2135

## Funkcje globalne

- Brak funkcji globalnych.

## Rejestracje WordPress / GF

- **action:** `gform_after_email` — linia 420

## Wybrane wywołania statyczne

- `GFAPI::get_form()`
- `GFAPI::get_entries()`
- `GFAPI::get_entry()`
- `GFCommon::send_notification()`
- `GFAPI::add_note()`
- `GFAPI::get_notes()`
- `GFCommon::evaluate_conditional_logic()`
- `GFCommon::replace_variables()`

## API WordPress / GF rozpoznane heurystycznie

- `current_user_can()`
- `wp_send_json_error()`
- `check_ajax_referer()`
- `sanitize_key()`
- `sanitize_text_field()`
- `is_wp_error()`
- `gform_get_meta()`
- `wp_send_json_success()`
- `wp_parse_url()`
- `wp_unslash()`
- `gform_delete_meta()`
- `add_action()`
- `remove_action()`
- `wp_mail()`
- `gform_update_meta()`
- `wp_get_current_user()`
- `wp_strip_all_tags()`

## Dołączane pliki / wyrażenia include

- Brak.

## Tabele / źródła SQL

- Brak statycznie rozpoznanych.

## Uwagi

- Symbole są wyciągane przez `token_get_all`; traity są zachowane z `kind=trait`.
- Dynamiczne rejestracje AJAX z map/stałych są rozwinięte w `hooks.json` i `entrypoints.json`.
- Dokładny Call Graph powstaje niezależnie w Code Indexerze.
