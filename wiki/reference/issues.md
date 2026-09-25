---
plugin: PWE System
version: 1.0.2
source: uploaded archive
source_commit: null
language: pl
---
# Flagi do przeglądu

## `version-metadata-mismatch`

- **Severity:** `review`
- **Status:** `confirmed`
- **Źródło:** `pwe-system.php`
- Wersja jest niespójna pomiędzy nagłówkiem pluginu, stałą runtime i README.

Plugin header: 1.0.2; PWE_SYSTEM_VERSION: 1.0.0; README deklaruje 1.2.0. Może to wpływać na cache-busting i diagnostykę wersji.
## `news-api-query-key`

- **Severity:** `review`
- **Status:** `design-review`
- **Źródło:** `api/news/index.php`
- News API dopuszcza PWE_API_KEY_2 w parametrze query `?key=` oprócz nagłówka X-API-KEY.

Sekrety w query string mogą trafić do logów serwera/proxy/history; nagłówek ogranicza ten kanał ekspozycji.
## `cap-public-status`

- **Severity:** `review`
- **Status:** `intentional-behavior`
- **Źródło:** `api/cap/doc.php`
- CAP Graphics API udostępnia GET statusu wymaganych plików bez klucza i zezwala na CORS `*`.

Kod rozdziela publiczny odczyt statusu od autoryzowanego POST; warto potwierdzić, że zakres zwracanych informacji ma pozostać publiczny.
## `forms-audit-conditional-boot`

- **Severity:** `info`
- **Status:** `architecture`
- **Źródło:** `modules/forms-audit/pwe-forms-audit-module.php`
- Forms Audit nie aktywuje się bez PWE QR Gravity Forms i dostępnego obiektu generatora QR.

To celowa zależność cross-plugin; UI audytu może być niedostępne mimo aktywnego PWE System, jeśli zależność nie jest gotowa.
## `trait-method-resolution`

- **Severity:** `info`
- **Status:** `indexer-design`
- **Źródło:** `modules/forms-audit/pwe-forms-audit-module.php`
- Część callbacków Forms Audit jest dostępna runtime przez use Trait, ale definicje metod należą do traitów.

Indexer callbacków/call graph powinien być trait-aware; inaczej może oznaczyć poprawny callback PWE_System_Forms_Audit_Tool jako missing_method. Powiązania są zapisane w inventory/trait-uses.json oraz definitionSymbol endpointów.
