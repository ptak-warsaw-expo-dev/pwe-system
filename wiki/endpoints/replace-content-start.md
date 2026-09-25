---
plugin: PWE System
version: 1.0.2
source: uploaded archive
source_commit: null
language: pl
---
# `replace-content-start`

- **Typ:** `wp-ajax`
- **Trigger/route:** `admin-ajax.php?action=pwe_replace_content_start`
- **Metody:** `POST`
- **Źródło:** `modules/replace-content/core/ajax-actions.php`
- **Callback:** `PWE_System_Replace_Content_Ajax::start`
- **Hook:** `wp_ajax_pwe_replace_content_start`
- **Autoryzacja wg kodu:** manage_options przez PWE_System_Admin_Access + nonce pwe_system_replace_content.

## Nawigacja techniczna

Dalszy przepływ należy śledzić od callbacku/scope pliku przez Symbol → Calls / Called by. Dla entrypointów cross-plugin pomocne są `inventory/integrations.json` i `dependencies.json`.
