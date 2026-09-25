---
plugin: PWE System
version: 1.0.2
source: uploaded archive
source_commit: null
language: pl
---
# Proces: CAP Graphics API

`api/cap/doc.php` jest bezpośrednim file-entrypointem.

1. OPTIONS kończy się 204.
2. Plik ładuje `wp-load.php`.
3. GET zwraca status wymaganych grafik z whitelisty `/doc` bez klucza API.
4. POST wymaga `X-PWE-API-Key` zgodnego z `PWE_API_KEY_2`.
5. Każda ścieżka musi istnieć w statycznej whiteliście; walidowane są MIME, rozmiar/wymiary i limity KB.
6. Uploady są przygotowywane w stagingu i dopiero potem podmieniane docelowo.
7. Po sukcesie wysyłane jest powiadomienie e-mail o zmianach.
8. Odpowiedź zawiera świeży status zestawu grafik.
