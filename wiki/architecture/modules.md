---
plugin: PWE System
version: 1.0.2
source: uploaded archive
source_commit: null
language: pl
---
# Moduły PWE System

## DOC Manager

Administracyjne operacje na katalogu `/doc`: listowanie, katalogi, rename, delete, move, upload, replace i unzip. Wszystkie akcje przechodzą przez capability i nonce.

## Replace Content

Mapuje wskazane adresy stron na shortcody AutoSwitch, rozszerza operację na grupy tłumaczeń WPML i wykonuje zmianę etapami przez AJAX.

## Resend

Job masowej ponownej wysyłki powiadomień Gravity Forms. Ma własny stan, lock, kursor, logi, pauzę/wznowienie i mechanizmy idempotencji oparte o entry meta.

## Forms Audit

Czyta formularze, feedy QR, entry/meta, historię wysyłek i status QR. Oferuje preview, ręczny resend, CSV oraz narzędzie QR backfill.

## Shortcodes

Dwie warstwy: proceduralne `[pwe_*]` oraz `PWE_Shortcodes` kompatybilne z wcześniejszym AutoSwitch.
