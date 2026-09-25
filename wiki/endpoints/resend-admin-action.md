---
plugin: PWE System
version: 1.0.2
source: uploaded archive
source_commit: null
language: pl
---
# `resend-admin-action`

- **Typ:** `admin-form`
- **Trigger/route:** `admin.php?page=pwe-system-resend + POST pwe_resend_action`
- **Metody:** `POST`
- **Źródło:** `modules/resend/core/resend-actions.php`
- **Callback:** `PWE_System_Resend_Actions::handle`
- **Hook:** `admin_init`
- **Autoryzacja wg kodu:** manage_options + check_admin_referer(pwe_system_resend_action).

## Uwagi

Akcje start/run/pause/resume/reset sterują jobem pwe_system_resend_job.

## Nawigacja techniczna

Dalszy przepływ należy śledzić od callbacku/scope pliku przez Symbol → Calls / Called by. Dla entrypointów cross-plugin pomocne są `inventory/integrations.json` i `dependencies.json`.
