---
plugin: PWE System
version: 1.0.2
source: uploaded archive
source_commit: null
language: pl
---
# Przegląd bezpieczeństwa — punkty do weryfikacji

To dokumentacja kodu, nie audyt penetracyjny.

## DOC Manager

Operacje są chronione capability `pwe_manage_doc` i nonce. Kod posiada kontrole containment ścieżek, nazw, rozszerzeń oraz zabezpieczenia ZIP przed traversal/symlinkami. Zmiany są logowane do tabeli pluginu.

## Replace Content / Forms Audit / Backfill

Akcje administracyjne wymagają `manage_options` oraz nonce. Replace Content dodatkowo serializuje kroki przez lock MySQL i stan joba związany z user ID.

## API CAP

GET statusu grafik jest publiczny i ma CORS `*`; POST wymaga klucza. Zakres publicznego statusu warto traktować jako jawny kontrakt API, nie przypadkową właściwość.

## News API

Klucz jest akceptowany zarówno w nagłówku, jak i `?key=`. Warto preferować nagłówek, aby ograniczyć obecność sekretu w URL/logach.

## Sekrety bazodanowe

Kod nie zawiera haseł CAP DB bezpośrednio; pobiera je ze stałych `PWE_DB_*`. Dokumentacja nie kopiuje wartości sekretów ani kluczy środowiskowych.
