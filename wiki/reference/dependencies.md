---
plugin: PWE System
version: 1.0.2
source: uploaded archive
source_commit: null
language: pl
---
# Zależności

- **WordPress** — `runtime-platform` — plugin lifecycle, admin UI, AJAX, posts/meta, capabilities
- **Gravity Forms** — `runtime-plugin` — required by Resend and forms audit data/notification operations
- **PWE QR Gravity Forms** — `conditional-pwe-plugin` — required to instantiate Forms Audit tools; supplies QR generator object
- **PWE Elements AutoSwitch** — `cross-plugin-runtime-data` — shared element/component paths and Replace Content target shortcodes
- **PWE Elements** — `incoming-compatibility-consumer` — can use PWE_System_Functions via PWE_Functions alias and system shortcodes
- **WPML** — `optional-plugin` — Replace Content translation graph and language-aware shortcode data
- **Uncode** — `optional-theme-contract` — Replace Content updates Uncode page metadata; News API emits vc_raw_html
- **WP Rocket** — `optional-plugin` — Replace Content clears page cache after update
- **PWE Multilang** — `optional-pwe-plugin` — primary website-translation.json source for URL shortcodes
- **Plugin Update Checker** — `bundled-third-party` — bundled library loaded through first-party updater wrapper
