---
plugin: PWE System
version: 1.0.2
source: uploaded archive
source_commit: null
language: pl
---
# Bundlowane zależności third-party

- `plugin-update-checker/` — Plugin Update Checker 4.9.
- `plugin-update-checker.php` jest małym wrapperem first-party i dlatego pozostaje w symbol/file inventory.

Kod biblioteki jest obecny w `files.json`, ale wyłączony z first-party `php-symbols.json`, aby nie zanieczyszczać wyszukiwania i Call Graphu.
