---
plugin: PWE System
version: 1.0.2
source: uploaded archive
source_commit: null
language: pl
---
# `forms-backfill-scan`

- **Typ:** `wp-ajax`
- **Trigger/route:** `admin-ajax.php?action=pwe_system_forms_backfill_scan`
- **Metody:** `POST`
- **Źródło:** `modules/forms-audit/tools/class-pwe-system-forms-backfill-tool.php`
- **Callback:** `PWE_System_Forms_Backfill_Tool::ajax_scan`
- **Hook:** `wp_ajax_pwe_system_forms_backfill_scan`
- **Autoryzacja wg kodu:** manage_options + nonce pwe_system_forms_backfill.

## Nawigacja techniczna

Dalszy przepływ należy śledzić od callbacku/scope pliku przez Symbol → Calls / Called by. Dla entrypointów cross-plugin pomocne są `inventory/integrations.json` i `dependencies.json`.
