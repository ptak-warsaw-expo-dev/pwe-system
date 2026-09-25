---
plugin: PWE System
version: 1.0.2
source: uploaded archive
source_commit: null
language: pl
---
# Proces: masowy resend Gravity Forms

1. Administrator wybiera formularze i parametry na stronie `pwe-system-resend`.
2. POST przechodzi przez `PWE_System_Resend_Actions::handle()` na `admin_init`, capability oraz admin nonce.
3. `PWE_System_Resend_Job::create()` zapisuje job `pwe_system_resend_job`.
4. `PWE_System_Resend_Job_Runner` przechodzi po formularzach, stronach entries i powiadomieniach z kursorem.
5. `PWE_System_Resend_Matcher` sprawdza, czy wpis pasuje, czy nie był już przetworzony, czy conditional logic pozwala na wysyłkę i czy e-mail jest poprawny.
6. Wysyłka korzysta z `GFCommon::send_notification()` lub fallback `send_notifications()`.
7. `wp_mail_failed` jest tymczasowo przechwytywany; błąd blokuje job zamiast przesuwać kursor.
8. Wynik jest zapisywany w entry meta i logu joba; lock i checkpoint ograniczają równoległe/zdublowane przetwarzanie.

Dostępne akcje: start, run, pause, resume, reset.
