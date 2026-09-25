---
plugin: PWE System
version: 1.0.2
source: uploaded archive
source_commit: null
language: pl
---
# `doc-delete`

- **Typ:** `wp-ajax`
- **Trigger/route:** `admin-ajax.php?action=pwe_system_doc_delete`
- **Metody:** `POST`
- **Źródło:** `modules/doc-manager/class-pwe-system-doc-manager.php`
- **Callback:** `PWE_System_Doc_Manager::ajax_delete`
- **Hook:** `wp_ajax_pwe_system_doc_delete`
- **Autoryzacja wg kodu:** Capability pwe_manage_doc + nonce pwe_system_doc_manager.

## Nawigacja techniczna

Dalszy przepływ należy śledzić od callbacku/scope pliku przez Symbol → Calls / Called by. Dla entrypointów cross-plugin pomocne są `inventory/integrations.json` i `dependencies.json`.
