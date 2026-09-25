---
plugin: PWE System
version: 1.0.2
source: uploaded archive
source_commit: null
language: pl
---
# `forms-audit-resend`

- **Typ:** `wp-ajax`
- **Trigger/route:** `admin-ajax.php?action=pwe_qr_resend_notifications`
- **Metody:** `POST`
- **Źródło:** `modules/forms-audit/traits/trait-pwe-forms-audit-notifications.php`
- **Callback:** `PWE_System_Forms_Audit_Tool::ajax_resend_notifications`
- **Hook:** `wp_ajax_pwe_qr_resend_notifications`
- **Autoryzacja wg kodu:** manage_options + nonce pwe_qr_resend_notifications; maks. 10 pozycji na request.

## Uwagi

Runtime callback jest metodą klasy przez trait; definicja: PWE_System_Forms_Audit_Notifications_Trait::ajax_resend_notifications.

## Nawigacja techniczna

Dalszy przepływ należy śledzić od callbacku/scope pliku przez Symbol → Calls / Called by. Dla entrypointów cross-plugin pomocne są `inventory/integrations.json` i `dependencies.json`.
