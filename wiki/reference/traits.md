---
plugin: PWE System
version: 1.0.2
source: uploaded archive
source_commit: null
language: pl
---
# Trait composition

PWE System używa traitów w Forms Audit. To istotne dla resolvera symboli: runtime callback należy do klasy konsumującej, ale definicja metody znajduje się w traicie.

## PWE_System_Forms_Audit_Tool

- `PWE_System_Forms_Audit_Render_Trait`
- `PWE_System_Forms_Audit_Notifications_Trait`
- `PWE_System_Forms_Audit_Data_Trait`
- `PWE_System_Forms_Audit_Export_Trait`

Machine-readable mapa znajduje się w `inventory/trait-uses.json`. Endpointy trait-backed zachowują zarówno `symbol` runtime, jak i `definitionSymbol`.
