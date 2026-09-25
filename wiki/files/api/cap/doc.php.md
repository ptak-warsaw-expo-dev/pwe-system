# `api/cap/doc.php`

Bezpośredni CAP Graphics API.

## Metadane

- **Kategoria:** `direct-api`
- **Rozmiar:** 17225 B
- **Liczba linii:** 655
- **Źródło:** `api/cap/doc.php`

## Typy i metody

- Brak klas/traitów.

## Funkcje globalne

- `pwe_system_api_images_status(string $root, array $assets)` — linia 441
- `pwe_system_api_images_send_notification(array $recipients, array $uploadedDetails, string $changedByName = '', string $changedByEmail = '')` — linia 511
- `pwe_system_api_images_normalize_files(array $files)` — linia 608
- `pwe_system_api_images_cleanup_staged(array $staged)` — linia 634
- `pwe_system_api_images_response(array $data, int $code = 200)` — linia 643

## Rejestracje WordPress / GF

- Brak wykrytych/reprodukowanych rejestracji.

## Wybrane wywołania statyczne

- Brak.

## API WordPress / GF rozpoznane heurystycznie

- `home_url()`
- `wp_date()`
- `wp_mail()`

## Dołączane pliki / wyrażenia include

- `_once $wpLoad`

## Tabele / źródła SQL

- Brak statycznie rozpoznanych.

## Uwagi

- Symbole są wyciągane przez `token_get_all`; traity są zachowane z `kind=trait`.
- Dynamiczne rejestracje AJAX z map/stałych są rozwinięte w `hooks.json` i `entrypoints.json`.
- Dokładny Call Graph powstaje niezależnie w Code Indexerze.
