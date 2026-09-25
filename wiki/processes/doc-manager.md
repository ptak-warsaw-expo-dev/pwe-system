---
plugin: PWE System
version: 1.0.2
source: uploaded archive
source_commit: null
language: pl
---
# Proces: DOC Manager

## UI → AJAX

Ekran `pwe-system-doc` dostaje `ajaxUrl`, nonce i bazowy URL `/doc`. Każda operacja wywołuje jeden z `wp_ajax_pwe_system_doc_*`.

## Wspólna ochrona

`PWE_System_Doc_Manager::guard()` wymaga capability `pwe_manage_doc`, weryfikuje nonce i zapewnia katalog `/doc`. Helpery normalizują ścieżki, odrzucają `..`, bajty NUL/control chars i sprawdzają containment przez `realpath`.

## Operacje

Listowanie, mkdir, rename, delete, move, upload, replace i unzip kończą się wpisem do `<prefix>pwe_system_log`. Upload/unzip posiadają dodatkową kontrolę nazw i typów; ZIP odrzuca symlinki i path traversal.
