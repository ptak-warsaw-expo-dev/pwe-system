---
plugin: PWE System
version: 1.0.2
source: uploaded archive
source_commit: null
language: pl
---
# `cap-graphics`

- **Typ:** `direct-http`
- **Trigger/route:** `/wp-content/plugins/pwe-system/api/cap/doc.php`
- **Metody:** `GET`, `POST`, `OPTIONS`
- **Źródło:** `api/cap/doc.php`
- **Callback:** `file scope`
- **Autoryzacja wg kodu:** GET bez klucza; POST wymaga X-PWE-API-Key zgodnego z PWE_API_KEY_2. OPTIONS kończy się 204.

## Uwagi

Operuje wyłącznie na whiteliście plików /doc; POST wysyła powiadomienie po zmianach.

## Nawigacja techniczna

Dalszy przepływ należy śledzić od callbacku/scope pliku przez Symbol → Calls / Called by. Dla entrypointów cross-plugin pomocne są `inventory/integrations.json` i `dependencies.json`.
