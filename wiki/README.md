---
plugin: PWE System
version: 1.0.2
source: uploaded archive
source_commit: null
language: pl
---
# PWE System 1.0.2 — dokumentacja techniczna

Wiki v2 powstało na podstawie rzeczywistego kodu z przesłanego archiwum `pwe-system 1.0.2.zip`. PWE System jest centralną warstwą wspólnych funkcji, narzędzi administracyjnych, shortcode'ów, API i procesów Gravity Forms dla ekosystemu PWE.

## Najważniejsze role

- udostępnia `PWE_System_Functions` i alias zgodności `PWE_Functions`,
- przejmuje część wspólnej logiki z PWE Elements / AutoSwitch,
- zarządza `/doc` przez DOC Manager oraz CAP Graphics API,
- synchronizuje newsy przez bezpośredni endpoint HTTP,
- seryjnie podmienia treści stron na shortcody AutoSwitch,
- realizuje masowy resend powiadomień Gravity Forms,
- audytuje formularze/rejestracje/QR i potrafi uzupełniać brakujące QR,
- dostarcza backendowe `[pwe_*]` oraz klasę `PWE_Shortcodes`.

## Inwentaryzacja

- wszystkie pliki archiwum: **131**,
- PHP first-party: **41**,
- klasy: **32**,
- traity: **4**,
- metody: **558**,
- funkcje globalne: **14**,
- deterministyczne shortcody: **155**,
- rodziny shortcode runtime: **10**,
- rejestracje hooków: **47**,
- endpointy/handlery HTTP: **19**,
- opisane procesy: **12**.

## Wiki v2

Warstwa zgodna z dotychczasowym importerem pozostaje w `inventory/files.json`, `php-symbols.json`, `file-analysis.json`, `shortcodes.json`, `endpoints.json`, `hooks.json`, `pages.json` i `documents.json`. Dodatkowo dostępne są `load-graph.json`, `entrypoints.json`, `integrations.json`, `dependencies.json`, `processes.json`, `shortcode-families.json` oraz `issues.json`.

## Zasada interpretacji

PWE System ma wiele zależności cross-plugin. Dokumentacja odróżnia kod własny od integracji z PWE Elements, AutoSwitch i PWE QR Gravity Forms. `issues.json` zawiera flagi do przeglądu; nie należy interpretować ich automatycznie jako potwierdzone podatności.
