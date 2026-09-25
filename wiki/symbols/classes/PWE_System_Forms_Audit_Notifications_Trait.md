# Trait `PWE_System_Forms_Audit_Notifications_Trait`

**Źródło:** `modules/forms-audit/traits/trait-pwe-forms-audit-notifications.php:7`  
**Typ:** `trait`  
**Metody:** 37

## Rola

Symbol jest zdefiniowany w `modules/forms-audit/traits/trait-pwe-forms-audit-notifications.php`. Status runtime pliku można sprawdzić w `inventory/load-graph.json`.

## Metody

- [`public ajax_bulk_language_preview()`](../methods/PWE_System_Forms_Audit_Notifications_Trait/ajax_bulk_language_preview.md) — linia 9
- [`private detect_language_from_source_url($source_url)`](../methods/PWE_System_Forms_Audit_Notifications_Trait/detect_language_from_source_url.md) — linia 205
- [`private get_active_notifications_by_language($form)`](../methods/PWE_System_Forms_Audit_Notifications_Trait/get_active_notifications_by_language.md) — linia 223
- [`public ajax_resend_notifications()`](../methods/PWE_System_Forms_Audit_Notifications_Trait/ajax_resend_notifications.md) — linia 260
- [`private add_admin_notifications_to_unsent_match($form, $entry, $match)`](../methods/PWE_System_Forms_Audit_Notifications_Trait/add_admin_notifications_to_unsent_match.md) — linia 561
- [`private get_notification_for_entry($form, $entry, $entry_email)`](../methods/PWE_System_Forms_Audit_Notifications_Trait/get_notification_for_entry.md) — linia 616
- [`private get_notification_history($entry_id)`](../methods/PWE_System_Forms_Audit_Notifications_Trait/get_notification_history.md) — linia 842
- [`private get_notification_error_message($entry_id)`](../methods/PWE_System_Forms_Audit_Notifications_Trait/get_notification_error_message.md) — linia 859
- [`private has_notification_error($entry_id)`](../methods/PWE_System_Forms_Audit_Notifications_Trait/has_notification_error.md) — linia 938
- [`private get_failed_notifications_from_gf_notes($form, $entry_id)`](../methods/PWE_System_Forms_Audit_Notifications_Trait/get_failed_notifications_from_gf_notes.md) — linia 1008
- [`private get_notifications_from_gf_notes($form, $entry_id)`](../methods/PWE_System_Forms_Audit_Notifications_Trait/get_notifications_from_gf_notes.md) — linia 1097
- [`private notification_name_is_resend($name)`](../methods/PWE_System_Forms_Audit_Notifications_Trait/notification_name_is_resend.md) — linia 1170
- [`private get_notification_delivery_state($form, $entry_id)`](../methods/PWE_System_Forms_Audit_Notifications_Trait/get_notification_delivery_state.md) — linia 1175
- [`private has_sent_notification($entry_id, $form = null)`](../methods/PWE_System_Forms_Audit_Notifications_Trait/has_sent_notification.md) — linia 1274
- [`private has_resend_notification($form, $entry_id)`](../methods/PWE_System_Forms_Audit_Notifications_Trait/has_resend_notification.md) — linia 1301
- [`private has_active_notification_error($form, $entry_id)`](../methods/PWE_System_Forms_Audit_Notifications_Trait/has_active_notification_error.md) — linia 1307
- [`private get_entry_with_language_override($form, $entry, $lang)`](../methods/PWE_System_Forms_Audit_Notifications_Trait/get_entry_with_language_override.md) — linia 1312
- [`private get_notifications_for_language($form, $lang, $entry = null)`](../methods/PWE_System_Forms_Audit_Notifications_Trait/get_notifications_for_language.md) — linia 1348
- [`private notification_contains_qr($notification)`](../methods/PWE_System_Forms_Audit_Notifications_Trait/notification_contains_qr.md) — linia 1390
- [`private get_historical_notifications_for_entry($form, $entry)`](../methods/PWE_System_Forms_Audit_Notifications_Trait/get_historical_notifications_for_entry.md) — linia 1412
- [`private get_qr_resend_notifications_for_entry($form, $entry)`](../methods/PWE_System_Forms_Audit_Notifications_Trait/get_qr_resend_notifications_for_entry.md) — linia 1461
- [`private get_never_sent_notifications_for_entry($form, $entry)`](../methods/PWE_System_Forms_Audit_Notifications_Trait/get_never_sent_notifications_for_entry.md) — linia 1495
- [`private get_resend_notifications_for_entry($form, $entry, $comparison = '')`](../methods/PWE_System_Forms_Audit_Notifications_Trait/get_resend_notifications_for_entry.md) — linia 1636
- [`private form_has_active_notifications($form)`](../methods/PWE_System_Forms_Audit_Notifications_Trait/form_has_active_notifications.md) — linia 1673
- [`private notification_conditional_logic_passes($notification, $form, $entry)`](../methods/PWE_System_Forms_Audit_Notifications_Trait/notification_conditional_logic_passes.md) — linia 1693
- [`private normalize_notification_conditional_logic($logic, $form)`](../methods/PWE_System_Forms_Audit_Notifications_Trait/normalize_notification_conditional_logic.md) — linia 1751
- [`private hydrate_notification_conditional_entry($form, $entry, $logic)`](../methods/PWE_System_Forms_Audit_Notifications_Trait/hydrate_notification_conditional_entry.md) — linia 1784
- [`private find_form_field_id_by_name($form, $name)`](../methods/PWE_System_Forms_Audit_Notifications_Trait/find_form_field_id_by_name.md) — linia 1850
- [`private get_form_field_names_by_id($form, $field_id)`](../methods/PWE_System_Forms_Audit_Notifications_Trait/get_form_field_names_by_id.md) — linia 1878
- [`private get_entry_named_value($form, $entry, $names)`](../methods/PWE_System_Forms_Audit_Notifications_Trait/get_entry_named_value.md) — linia 1908
- [`private get_notification_logic_rule_value($form, $entry, $field_id)`](../methods/PWE_System_Forms_Audit_Notifications_Trait/get_notification_logic_rule_value.md) — linia 1971
- [`private get_form_field_property($field, $property)`](../methods/PWE_System_Forms_Audit_Notifications_Trait/get_form_field_property.md) — linia 2025
- [`private resolve_notification_recipients($notification, $form, $entry)`](../methods/PWE_System_Forms_Audit_Notifications_Trait/resolve_notification_recipients.md) — linia 2034
- [`private replace_notification_variables($value, $form, $entry)`](../methods/PWE_System_Forms_Audit_Notifications_Trait/replace_notification_variables.md) — linia 2080
- [`private compare_rule_value($actual, $operator, $expected)`](../methods/PWE_System_Forms_Audit_Notifications_Trait/compare_rule_value.md) — linia 2093
- [`private recipient_array_contains($recipients, $target_email)`](../methods/PWE_System_Forms_Audit_Notifications_Trait/recipient_array_contains.md) — linia 2124
- [`private email_list_contains($emails, $target_email)`](../methods/PWE_System_Forms_Audit_Notifications_Trait/email_list_contains.md) — linia 2135

## Dokument pliku

- [Otwórz dokumentację `modules/forms-audit/traits/trait-pwe-forms-audit-notifications.php`](../../files/modules/forms-audit/traits/trait-pwe-forms-audit-notifications.php.md)
