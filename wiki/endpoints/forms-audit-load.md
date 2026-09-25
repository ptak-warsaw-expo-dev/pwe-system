---
plugin: PWE System
version: 1.0.2
source: uploaded archive
source_commit: null
language: pl
---
# `forms-audit-load`

- **Typ:** `wp-ajax`
- **Trigger/route:** `admin-ajax.php?action=pwe_system_forms_audit_load`
- **Metody:** `POST`
- **Źródło:** `modules/forms-audit/pwe-forms-audit-module.php`
- **Callback:** `PWE_System_Forms_Audit_Tool::ajax_load_audit`
- **Hook:** `wp_ajax_pwe_system_forms_audit_load`
- **Autoryzacja wg kodu:** manage_options + nonce pwe_system_forms_audit_load.

## Nawigacja techniczna

Dalszy przepływ należy śledzić od callbacku/scope pliku przez Symbol → Calls / Called by. Dla entrypointów cross-plugin pomocne są `inventory/integrations.json` i `dependencies.json`.
