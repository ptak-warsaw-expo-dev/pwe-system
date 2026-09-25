---
plugin: PWE System
version: 1.0.2
source: uploaded archive
source_commit: null
language: pl
---
# Dane, CAP DB i cache

`PWE_System_Functions` zawiera centralny dostęp do danych PWE. `connect_database()` wybiera konfigurację serwera z wartości dostarczanych przez stałe `PWE_DB_*`, preferuje lokalny host i ustawia krótki timeout połączenia.

Warstwa danych obejmuje m.in. targi, tłumaczenia, logotypy, konferencje, profile, premiery, opinie, sektory, bilety, prelegentów, gości, atrakcje, pliki, grupy oraz konfigurację elementów.

Wspólny cache JSON ma osobne helpery do ścieżki, odczytu, pakowania i bezpiecznego zapisu przez plik tymczasowy + `rename()`. `refresh_database_json_cache()` odświeża zestaw źródeł dla domeny.
