---
plugin: PWE System
version: 1.0.2
source: uploaded archive
source_commit: null
language: pl
---
# Dane i tabele

## WordPress / plugin

- `<prefix>pwe_system_log` — log operacji DOC Managera,
- `wp_options` — job Resend (`pwe_system_resend_job`) i lock/state helpers,
- Gravity Forms `gf_entry` / `gf_entry_meta` — audyt i QR backfill (nazwy mogą pochodzić z `GFFormsModel`),
- standardowe `posts`, `postmeta`, termy i media — News Sync / Replace Content.

## CAP DB

`PWE_System_Functions` odwołuje się m.in. do: `fairs`, `fair_adds`, `translations`, `associates`, `shop`, `shop_packs`, `meta_data`, `groups`, `form_senders`, `fair_weeks`, `logos`, `conferences`, `conf_adds`, `fair_profiles`, `fair_premieres`, `fair_opinions`, `fair_sectors`, `fair_tickets`, `fair_lectures`, `fair_guests`, `fair_attractions`, `fair_files`, `pwelements`, `pwe_order`.
