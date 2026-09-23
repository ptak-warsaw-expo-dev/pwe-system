# PWE System

Centralna wtyczka systemowa dla stron PWE. Zbiera wspólne narzędzia administracyjne, API, shortcody i moduły techniczne, które wcześniej były rozproszone pomiędzy kilka wtyczek.

## Wersja

**1.2.0**

## Wymagania

- WordPress
- PHP 7.4 lub nowszy
- Gravity Forms dla modułów Resend i Audyt formularzy
- PWE QR Gravity Forms dla funkcji audytu związanych z generatorem i danymi QR
- WPML tylko dla funkcji Replace Content dotyczących wersji językowych

## Moduły

### Start

Główny dashboard PWE System z szybkim dostępem do modułów i informacją o wersji wtyczki.

### DOC Manager

Zarządzanie katalogiem `/doc` bezpośrednio z panelu WordPress:

- dodawanie pojedynczych i wielu plików,
- dodawanie całych katalogów z zachowaniem struktury,
- tworzenie katalogów,
- zmiana nazw plików i katalogów,
- przenoszenie plików i całych katalogów,
- drag & drop,
- podmiana istniejących plików,
- usuwanie,
- rozpakowywanie archiwów ZIP,
- podgląd grafik,
- wyświetlanie wymiarów obrazów w pikselach,
- log operacji.

Operacje są ograniczone do `/doc` i zabezpieczone nonce oraz capability `pwe_manage_doc`.

### Shortcody

Warstwa shortcode przeniesiona z wcześniejszych wtyczek PWE:

- backendowe shortcody `[pwe_*]`,
- shortcody wyższego poziomu `PWE_Shortcodes`,
- wyszukiwarka po nazwach i opisach,
- grupowanie shortcode'ów w zakładkach,
- główny plik danych URL: `wp-content/plugins/pwe-multilang/website-translation.json`,
- zapasowy plik danych URL: `data/website-translation.json` w PWE System; używany tylko, gdy plik główny nie istnieje, jest nieczytelny albo zawiera nieprawidłowy JSON.

`PWE_Functions` pozostaje aliasem `PWE_System_Functions`, dzięki czemu istniejący kod może działać bez jednoczesnego przepisywania wszystkich elementów.

### Replace Content

Natywny moduł PWE System. Nie wymaga już modułu Replace Content w PWE Multilang.

Pozwala:

- analizować mapę stron i shortcode'ów,
- wykrywać strony wymagające zmiany,
- obsługiwać tłumaczenia WPML,
- seryjnie podmieniać treść stron,
- ustawiać wymagane metadane Uncode,
- wykonywać operację etapami przez AJAX z paskiem postępu.

Główna klasa modułu:

```php
PWE_System_Replace_Content
```

### Resend

Natywny moduł PWE System. Nie wymaga już modułu Resend w PWE Multilang.

Obsługuje:

- wyszukiwanie powiadomień resend w Gravity Forms,
- filtrowanie wpisów,
- dopasowanie języka i conditional logic,
- wysyłkę partiami,
- przerwę pomiędzy partiami,
- pauzę, wznowienie i reset procesu,
- blokadę równoległego przetwarzania,
- historię wysyłki i błędów.

Główna klasa modułu:

```php
PWE_System_Resend
```

Dane procesu są przechowywane pod systemowym kluczem `pwe_system_resend_job`.

### Audyt formularzy

Audyt jest utrzymywany wyłącznie w PWE System. Kod audytu nie należy już do PWE QR Gravity Forms.

Moduł obejmuje kontrolę:

- formularzy Gravity Forms,
- feedów i konfiguracji QR,
- rejestracji,
- zapisanych danych QR,
- języków,
- powiadomień,
- historii wysyłki,
- rozbieżności danych i eksportów CSV.

Nazewnictwo modułu zostało ujednolicone pod audyt całych formularzy:

```php
PWE_System_Forms_Audit_Module
PWE_System_Forms_Audit_Tool
```

Kod znajduje się w:

```text
modules/forms-audit/
```

### API

Endpointy systemowe znajdują się w:

```text
api/cap/doc.php
api/news/index.php
```

Stare wtyczki mogą zachowywać własne bridge'e do tych endpointów podczas migracji instalacji.

## Struktura

```text
pwe-system/
├── pwe-system.php
├── README.md
├── api/
│   ├── cap/
│   └── news/
├── assets/
│   ├── css/
│   ├── images/
│   └── js/
├── core/
│   ├── class-pwe-system.php
│   ├── class-pwe-system-admin.php
│   ├── class-pwe-system-admin-access.php
│   ├── class-pwe-system-admin-ui.php
│   ├── class-pwe-system-functions.php
│   └── class-pwe-system-updater.php
├── data/
│   └── website-translation.json
├── modules/
│   ├── doc-manager/
│   ├── forms-audit/
│   ├── replace-content/
│   ├── resend/
│   └── shortcodes/
└── plugin-update-checker/
```

## Nazewnictwo klas

Kod przeniesiony do PWE System nie używa już nazw klas i stałych należących do dawnych modułów PWE Multilang ani dawnego audytu QR.

Przykłady:

```php
PWE_System_Resend
PWE_System_Resend_Job
PWE_System_Resend_Actions
PWE_System_Replace_Content
PWE_System_Replace_Content_Service
PWE_System_Forms_Audit_Module
PWE_System_Forms_Audit_Tool
```

## Aktualizacje z GitHub

Wtyczka korzysta z dołączonego Plugin Update Checker i repozytorium:

```text
https://github.com/ptak-warsaw-expo-dev/pwe-system
```

Update Checker jest inicjalizowany przez:

```text
core/class-pwe-system-updater.php
```

Jeżeli w bazie CAP dostępny jest `github_secret`, zostaje użyty do autoryzacji GitHub API. Release assets są włączone, dlatego zalecane jest dodawanie gotowego ZIP-a wtyczki do GitHub Release.

## Dane wtyczki

- **Author:** PWE Web Developers
- **Co-author:** Anton Melnychuk, Piotr Krupniewski, Jakub Choła
- **Repository:** https://github.com/ptak-warsaw-expo-dev/pwe-system
- **License:** GPL v2 or later


### Uzupełnianie brakujących QR

W module Forms Audit dostępne jest narzędzie do skanowania formularzy z aktywnym feedem `pwe_qr` i uzupełniania brakujących metadanych `pwe_qr_code_url` partiami.
