---
plugin: PWE System
version: 1.0.2
source: uploaded archive
source_commit: null
language: pl
---
# Proces: synchronizacja newsów

`api/news/index.php` ładuje WordPress i wymaga `PWE_API_KEY_2` przez `?key=` lub `X-API-KEY`.

Payload JSON ma `action=upsert|delete`. Dla `delete` wyszukiwany jest wpis po slug lub `_pwe_sync_slug`, a następnie usuwane są wersje PL/EN. Dla `upsert` endpoint przygotowuje content Uncode `vc_raw_html`, tworzy/aktualizuje wpis PL, przypisuje kategorię, zapisuje `_pwe_sync_slug`, ustawia WPML language details i obraz wyróżniający; analogiczny przepływ wykonuje dla EN.
