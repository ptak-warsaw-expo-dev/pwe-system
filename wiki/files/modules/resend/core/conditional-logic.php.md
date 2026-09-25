# `modules/resend/core/conditional-logic.php`

Plik first-party PWE System w kategorii `resend`.

## Metadane

- **Kategoria:** `resend`
- **Rozmiar:** 802 B
- **Liczba linii:** 33
- **Źródło:** `modules/resend/core/conditional-logic.php`

## Typy i metody

### class `PWE_System_Resend_Conditional_Logic` — linia 12

  - `public static compile($logic, string $context, array $owner = [])` — linia 14
  - `public static evaluate($logic, array $form, array $entry)` — linia 19

## Funkcje globalne

- Brak funkcji globalnych.

## Rejestracje WordPress / GF

- Brak wykrytych/reprodukowanych rejestracji.

## Wybrane wywołania statyczne

- `GFCommon::evaluate_conditional_logic()`

## API WordPress / GF rozpoznane heurystycznie

- `apply_filters()`

## Dołączane pliki / wyrażenia include

- Brak.

## Tabele / źródła SQL

- Brak statycznie rozpoznanych.

## Uwagi

- Symbole są wyciągane przez `token_get_all`; traity są zachowane z `kind=trait`.
- Dynamiczne rejestracje AJAX z map/stałych są rozwinięte w `hooks.json` i `entrypoints.json`.
- Dokładny Call Graph powstaje niezależnie w Code Indexerze.
