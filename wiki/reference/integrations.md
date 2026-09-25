---
plugin: PWE System
version: 1.0.2
source: uploaded archive
source_commit: null
language: pl
---
# Integracje

- **WordPress** — `platform`
- **Gravity Forms** — `plugin`
- **PWE QR Gravity Forms** — `pwe-plugin`; required for Forms Audit QR-aware boot
- **PWE Elements** — `pwe-plugin`; incoming compatibility consumer
- **PWE Elements AutoSwitch** — `pwe-plugin`; shared elements/components + target shortcodes + API migration bridges
- **WPML** — `plugin`
- **Uncode** — `theme`
- **WP Rocket** — `plugin`
- **PWE Multilang** — `pwe-plugin/data-provider`; preferred website-translation.json for URL shortcodes
- **CAP database** — `database/external-data`
- **GitHub Releases** — `update-service`
- **WordPress/PHPMailer SMTP** — `mail`

## Najważniejsze granice

Najistotniejsze dla przyszłego cross-plugin graphu są relacje z PWE Elements AutoSwitch i PWE QR Gravity Forms. PWE Elements jest również konsumentem warstwy zgodności `PWE_Functions`/shortcode dostarczanej przez PWE System.
