# `modules/resend/core/form-repository.php`

Plik first-party PWE System w kategorii `resend`.

## Metadane

- **Kategoria:** `resend`
- **Rozmiar:** 1276 B
- **Liczba linii:** 60
- **Źródło:** `modules/resend/core/form-repository.php`

## Typy i metody

### class `PWE_System_Resend_Form_Repository` — linia 9

  - `public static available()` — linia 11
  - `public static get(int $form_id)` — linia 16
  - `public static is_managed(array $form)` — linia 27
  - `public static is_managed_id(int $form_id)` — linia 32
  - `public static managed()` — linia 40

## Funkcje globalne

- Brak funkcji globalnych.

## Rejestracje WordPress / GF

- Brak wykrytych/reprodukowanych rejestracji.

## Wybrane wywołania statyczne

- `self::available()`
- `GFAPI::get_form()`
- `self::get()`
- `self::is_managed()`
- `GFAPI::get_forms()`

## API WordPress / GF rozpoznane heurystycznie

- Brak.

## Dołączane pliki / wyrażenia include

- Brak.

## Tabele / źródła SQL

- Brak statycznie rozpoznanych.

## Uwagi

- Symbole są wyciągane przez `token_get_all`; traity są zachowane z `kind=trait`.
- Dynamiczne rejestracje AJAX z map/stałych są rozwinięte w `hooks.json` i `entrypoints.json`.
- Dokładny Call Graph powstaje niezależnie w Code Indexerze.
