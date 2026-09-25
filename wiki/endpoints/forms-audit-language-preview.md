---
plugin: PWE System
version: 1.0.2
source: uploaded archive
source_commit: null
language: pl
---
# `forms-audit-language-preview`

- **Typ:** `wp-ajax`
- **Trigger/route:** `admin-ajax.php?action=pwe_qr_bulk_language_preview`
- **Metody:** `POST`
- **Źródło:** `modules/forms-audit/traits/trait-pwe-forms-audit-notifications.php`
- **Callback:** `PWE_System_Forms_Audit_Tool::ajax_bulk_language_preview`
- **Hook:** `wp_ajax_pwe_qr_bulk_language_preview`
- **Autoryzacja wg kodu:** manage_options + nonce pwe_qr_bulk_language_preview.

## Uwagi

Runtime callback jest metodą klasy przez trait; definicja: PWE_System_Forms_Audit_Notifications_Trait::ajax_bulk_language_preview.

## Nawigacja techniczna

Dalszy przepływ należy śledzić od callbacku/scope pliku przez Symbol → Calls / Called by. Dla entrypointów cross-plugin pomocne są `inventory/integrations.json` i `dependencies.json`.
