---
plugin: PWE System
version: 1.0.2
source: uploaded archive
source_commit: null
language: pl
---
# Proces: CAP DB i cache JSON

`PWE_System_Functions::connect_database()` wybiera konfigurację spośród hostów PWE na podstawie stałych `PWE_DB_*`. Jeśli bieżący serwer odpowiada jednej z konfiguracji, używany jest `localhost`; pozostałe hosty są pomijane. Połączenie jest cache'owane w obrębie requestu i używa 2-sekundowego timeoutu.

Metody `get_database_*` pobierają dane z CAP DB. Wiele źródeł ma JSON cache z helperami `read_database_json_cache()` i `write_database_json_cache()`. Zapis używa tymczasowego pliku i `rename()`, a katalog cache dostaje ochronny `index.php`.
