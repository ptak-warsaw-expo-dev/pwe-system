(function($) {
    'use strict';

    if (typeof PWEFormsAudit === 'undefined') {
        return;
    }

    const $target = $('#pwe-forms-audit-async');

    if (!$target.length) {
        return;
    }

    let currentRequest = null;

    function requestFromUrl(url, forceRefresh) {
        const parsed = new URL(url, window.location.href);
        const params = parsed.searchParams;
        const request = {
            action: 'pwe_system_forms_audit_load',
            nonce: PWEFormsAudit.nonce
        };

        [
            'audit_form_id',
            'audit_search',
            'audit_status',
            'audit_notification',
            'audit_per_page',
            'audit_paged'
        ].forEach(function(key) {
            if (params.has(key)) {
                request[key] = params.get(key);
            }
        });

        if (forceRefresh) {
            request.audit_refresh = '1';
        }

        return request;
    }

    function renderLoader(text) {
        $target.html(
            '<div class="pwe-forms-audit-loader">' +
                '<span class="pwe-forms-audit-loader__spinner" aria-hidden="true"></span>' +
                '<div><strong>' + $('<div>').text(text || 'Ładowanie audytu…').html() + '</strong>' +
                '<p>Dane są pobierane w tle. Po pierwszym przeliczeniu w tej sesji filtry korzystają już z pamięci podręcznej.</p></div>' +
            '</div>'
        );
    }

    function showError(message) {
        $target.html(
            '<div class="pwe-system-message is-error">' +
                '<span class="dashicons dashicons-warning"></span>' +
                '<div><strong>Nie udało się załadować audytu</strong><p>' + $('<div>').text(message || 'Nieznany błąd').html() + '</p></div>' +
            '</div>'
        );
    }

    function injectHtml(html) {
        const wrapper = $('<div>').html(html || '');
        const scripts = [];

        wrapper.find('script').each(function() {
            scripts.push(this.text || this.textContent || '');
            $(this).remove();
        });

        $target.empty().append(wrapper.contents());

        scripts.forEach(function(code) {
            if (code.trim() !== '') {
                $.globalEval(code);
            }
        });
    }

    function loadAudit(url, options) {
        options = options || {};

        if (currentRequest && currentRequest.readyState !== 4) {
            currentRequest.abort();
        }

        renderLoader(options.forceRefresh ? 'Odświeżanie danych audytu…' : 'Ładowanie audytu…');

        currentRequest = $.ajax({
            url: PWEFormsAudit.ajaxUrl,
            method: 'GET',
            dataType: 'json',
            data: requestFromUrl(url, !!options.forceRefresh),
            timeout: 0
        }).done(function(response) {
            if (!response || !response.success) {
                showError(response && response.data && response.data.message ? response.data.message : 'Nieznany błąd.');
                return;
            }

            injectHtml(response.data && response.data.html ? response.data.html : '');

            if (options.pushState) {
                window.history.pushState({ pweFormsAudit: true }, '', url);
            } else if (options.replaceState) {
                window.history.replaceState({ pweFormsAudit: true }, '', url);
            }
        }).fail(function(xhr, status) {
            if (status === 'abort') {
                return;
            }

            let message = 'Błąd połączenia: HTTP ' + xhr.status;

            if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                message = xhr.responseJSON.data.message;
            }

            showError(message);
        });
    }

    function formUrl($form) {
        const params = new URLSearchParams();

        $form.serializeArray().forEach(function(item) {
            if (item.name === 'audit_paged') {
                return;
            }

            if (item.value !== '' && !(item.name === 'audit_form_id' && item.value === '0')) {
                params.set(item.name, item.value);
            }
        });

        params.set('page', 'pwe-system-forms-audit');
        params.set('audit_paged', '1');

        return window.location.pathname + '?' + params.toString();
    }

    $(document).on('submit.pweFormsAudit', '#pwe-forms-audit-async .pwe-qr-filters', function(event) {
        event.preventDefault();
        loadAudit(formUrl($(this)), { pushState: true });
    });

    $(document).on('click.pweFormsAudit', '#pwe-forms-audit-async .pagination-links a', function(event) {
        event.preventDefault();
        loadAudit(this.href, { pushState: true });
    });

    $(document).on('click.pweFormsAudit', '#pwe-forms-audit-async .pwe-qr-filters a.button', function(event) {
        const href = $(this).attr('href');

        if (!href) {
            return;
        }

        event.preventDefault();
        loadAudit(href, { pushState: true });
    });

    $(document).on('click.pweFormsAudit', '#pwe-forms-audit-async .pwe-forms-audit-refresh', function(event) {
        event.preventDefault();
        loadAudit(window.location.href, { forceRefresh: true });
    });

    window.addEventListener('popstate', function() {
        loadAudit(window.location.href);
    });

    loadAudit(window.location.href, { replaceState: true });
})(jQuery);
