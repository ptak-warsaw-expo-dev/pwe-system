(function($) {
    'use strict';

    if (typeof PWEFormsBackfill === 'undefined') {
        return;
    }

    const $scan = $('#pwe-forms-backfill-scan');
    const $generate = $('#pwe-forms-backfill-generate');

    if (!$scan.length || !$generate.length) {
        return;
    }

    const $status = $('#pwe-forms-backfill-status');
    const $progress = $('#pwe-forms-backfill-progress');
    const $bar = $('#pwe-forms-backfill-bar');
    const $progressText = $('#pwe-forms-backfill-progress-text');

    let forms = [];
    let totalMissing = 0;
    let generated = 0;
    let currentFormIndex = 0;

    function escapeHtml(value) {
        return $('<div>').text(value == null ? '' : value).html();
    }

    function renderScanResult(data) {
        forms = data.forms || [];
        totalMissing = Number(data.total_missing || 0);
        generated = 0;
        currentFormIndex = 0;

        if (!forms.length) {
            $status.html('<div class="pwe-system-message is-info"><span class="dashicons dashicons-info-outline"></span><div><strong>Brak formularzy do sprawdzenia</strong><p>Nie znaleziono formularzy z aktywnym feedem pwe_qr.</p></div></div>');
            $generate.prop('disabled', true);
            return;
        }

        let html = '<div class="pwe-forms-backfill__table-wrap"><table class="widefat striped"><thead><tr>' +
            '<th>Formularz</th><th>Aktywne wpisy</th><th>Brakujące QR</th><th>Aktywne feedy</th>' +
            '</tr></thead><tbody>';

        forms.forEach(function(form) {
            html += '<tr>' +
                '<td><strong>' + escapeHtml(form.title) + '</strong><small>ID ' + Number(form.id) + '</small></td>' +
                '<td>' + Number(form.total_entries) + '</td>' +
                '<td><strong>' + Number(form.missing_entries) + '</strong></td>' +
                '<td>' + Number(form.active_feeds) + '</td>' +
                '</tr>';
        });

        html += '</tbody></table></div>' +
            '<div class="pwe-forms-backfill__total"><span>Łącznie brakujących QR</span><strong>' + totalMissing + '</strong></div>';

        $status.html(html);
        $generate.prop('disabled', totalMissing < 1);
    }

    function errorMessage(message) {
        $status.prepend('<div class="pwe-system-message is-error"><span class="dashicons dashicons-warning"></span><div><strong>Błąd</strong><p>' + escapeHtml(message) + '</p></div></div>');
    }

    $scan.on('click', function() {
        const $button = $(this);
        $button.prop('disabled', true).text('Sprawdzanie…');
        $generate.prop('disabled', true);
        $status.html('<p class="pwe-forms-backfill__working">Analizowanie formularzy i wpisów…</p>');
        $progress.prop('hidden', true);

        $.post(PWEFormsBackfill.ajaxUrl, {
            action: 'pwe_system_forms_backfill_scan',
            nonce: PWEFormsBackfill.nonce
        }).done(function(response) {
            if (!response.success) {
                errorMessage(response.data && response.data.message ? response.data.message : 'Nieznany błąd');
                return;
            }
            renderScanResult(response.data);
        }).fail(function(xhr) {
            errorMessage('Błąd połączenia: HTTP ' + xhr.status);
        }).always(function() {
            $button.prop('disabled', false).text('1. Sprawdź formularze i brakujące QR');
        });
    });

    function updateProgress(message) {
        const percent = totalMissing > 0 ? Math.min(100, Math.round((generated / totalMissing) * 100)) : 100;
        $progress.prop('hidden', false);
        $bar.css('width', percent + '%');
        $progressText.text(message + ' (' + generated + ' / ' + totalMissing + ')');
    }

    function processNextForm() {
        while (currentFormIndex < forms.length && Number(forms[currentFormIndex].missing_entries) < 1) {
            currentFormIndex++;
        }

        if (currentFormIndex >= forms.length) {
            updateProgress('Zakończono generowanie');
            $bar.css('width', '100%');
            $generate.prop('disabled', true).text('Uzupełnianie zakończone');
            $scan.prop('disabled', false);
            $status.prepend('<div class="pwe-system-message is-success"><span class="dashicons dashicons-yes-alt"></span><div><strong>Gotowe</strong><p>Wygenerowano: ' + generated + ' kodów QR.</p></div></div>');
            return;
        }

        const form = forms[currentFormIndex];
        updateProgress('Przetwarzanie: ' + form.title);

        $.post(PWEFormsBackfill.ajaxUrl, {
            action: 'pwe_system_forms_backfill_generate',
            nonce: PWEFormsBackfill.nonce,
            form_id: form.id
        }).done(function(response) {
            if (!response.success) {
                errorMessage('Formularz ' + form.title + ': ' + (response.data && response.data.message ? response.data.message : 'Nieznany błąd'));
                currentFormIndex++;
                processNextForm();
                return;
            }

            generated += Number(response.data.generated || 0);

            if (Number(response.data.remaining || 0) > 0) {
                processNextForm();
            } else {
                currentFormIndex++;
                processNextForm();
            }
        }).fail(function(xhr) {
            errorMessage('Błąd HTTP ' + xhr.status + ' przy formularzu ' + form.title + '.');
            $generate.prop('disabled', false).text('Wznów generowanie');
            $scan.prop('disabled', false);
        });
    }

    $generate.on('click', function() {
        if (!forms.length || totalMissing < 1) {
            return;
        }

        $(this).prop('disabled', true).text('Generowanie…');
        $scan.prop('disabled', true);
        generated = 0;
        currentFormIndex = 0;
        processNextForm();
    });
})(jQuery);
