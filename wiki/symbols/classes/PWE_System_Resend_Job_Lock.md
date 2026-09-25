# Class `PWE_System_Resend_Job_Lock`

**Źródło:** `modules/resend/core/job-lock.php:9`  
**Typ:** `class`  
**Metody:** 11

## Rola

Symbol jest zdefiniowany w `modules/resend/core/job-lock.php`. Status runtime pliku można sprawdzić w `inventory/load-graph.json`.

## Metody

- [`public static acquire()`](../methods/PWE_System_Resend_Job_Lock/acquire.md) — linia 17
- [`public static refresh(string $token)`](../methods/PWE_System_Resend_Job_Lock/refresh.md) — linia 61
- [`public static release(string $token)`](../methods/PWE_System_Resend_Job_Lock/release.md) — linia 94
- [`private static key()`](../methods/PWE_System_Resend_Job_Lock/key.md) — linia 132
- [`private static value(string $token)`](../methods/PWE_System_Resend_Job_Lock/value.md) — linia 137
- [`private static is_valid($value)`](../methods/PWE_System_Resend_Job_Lock/is_valid.md) — linia 142
- [`private static is_owned_by($value, string $token)`](../methods/PWE_System_Resend_Job_Lock/is_owned_by.md) — linia 151
- [`private static read_raw(string $key)`](../methods/PWE_System_Resend_Job_Lock/read_raw.md) — linia 157
- [`private static compare_and_delete(string $key, string $expected_raw)`](../methods/PWE_System_Resend_Job_Lock/compare_and_delete.md) — linia 171
- [`private static compare_and_update(string $key, string $expected_raw, string $next_raw)`](../methods/PWE_System_Resend_Job_Lock/compare_and_update.md) — linia 189
- [`private static clear_option_cache(string $key)`](../methods/PWE_System_Resend_Job_Lock/clear_option_cache.md) — linia 209

## Dokument pliku

- [Otwórz dokumentację `modules/resend/core/job-lock.php`](../../files/modules/resend/core/job-lock.php.md)
