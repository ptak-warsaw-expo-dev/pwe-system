---
plugin: PWE System
version: 1.0.2
source: uploaded archive
source_commit: null
language: pl
---
# Proces: aktualizacja pluginu

`PWE_System_Updater` rejestruje `setup_updater()` na `plugins_loaded` priority 5. Ładuje lokalny wrapper Plugin Update Checker, buduje checker dla repozytorium GitHub `ptak-warsaw-expo-dev/pwe-system` i opcjonalnie ustawia authentication token z CAP DB (`github_secret`). Jeżeli API PUC udostępnia tę funkcję, włączane są release assets.
