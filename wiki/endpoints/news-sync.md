---
plugin: PWE System
version: 1.0.2
source: uploaded archive
source_commit: null
language: pl
---
# `news-sync`

- **Typ:** `direct-http`
- **Trigger/route:** `/wp-content/plugins/pwe-system/api/news/index.php`
- **Metody:** `POST`
- **Źródło:** `api/news/index.php`
- **Callback:** `file scope`
- **Autoryzacja wg kodu:** PWE_API_KEY_2 przez ?key= lub X-API-KEY.

## Uwagi

JSON action=upsert/delete; synchronizuje wpisy PL/EN, WPML i obrazki wyróżniające.

## Nawigacja techniczna

Dalszy przepływ należy śledzić od callbacku/scope pliku przez Symbol → Calls / Called by. Dla entrypointów cross-plugin pomocne są `inventory/integrations.json` i `dependencies.json`.
