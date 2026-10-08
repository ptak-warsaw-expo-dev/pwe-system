(() => {
    'use strict';
    function resultMetrics(result) {
        const details = result.details || {};
        // Stored results from before the structured counters were introduced remain readable.
        const legacy = (result.message || '').match(/^Sprawdzono formularze Multilang:\s*(\d+);\s*feedy QR:\s*(\d+)\.\s*Problemy:\s*(\d+)\.\s*Ostrzeżenia:\s*(\d+)\./);
        const summary = details.summary || {};
        return {
            forms: summary.forms ?? (legacy ? Number(legacy[1]) : null),
            feeds: summary.feeds ?? (legacy ? Number(legacy[2]) : null),
            errors: details.errors ? Object.keys(details.errors).length : legacy ? Number(legacy[3]) : result.status === 'error' ? 1 : null,
            warnings: details.warnings ? Object.keys(details.warnings).length : legacy ? Number(legacy[4]) : result.status === 'warning' ? 1 : null,
        };
    }
    window.PWETestsViews = window.PWETestsViews || {};
    window.PWETestsViews['qr-feeds'] = result => {
        const metrics = resultMetrics(result);
        return {
            icon: 'grid', columnLabel: 'Formularz / feed',
            fieldLabels: { prefix_id: 'Prefiks + ID formularza', random_part: 'Część losowa' },
            message: metrics.forms !== null ? `Sprawdzono formularze Multilang: ${metrics.forms}` : result.message,
            metrics: [{ label: 'Feedy QR', value: metrics.feeds, icon: 'forms' }, { label: 'Problemy', value: metrics.errors, icon: 'warning', tone: 'error' }, { label: 'Ostrzeżenia', value: metrics.warnings, icon: 'info', tone: 'warning' }],
            rows: Object.values(result.details?.checked || {}).map(item => {
                const parts = String(item.title || '').match(/^#(\d+) · (.*?)(?: \/ (feed #.*))?$/);
                const compact = value => value && typeof value === 'object' ? `${value.prefix_id ?? 'Brak'} + ${value.random_part ?? 'Brak'}` : value;
                return { ...item, id: parts ? `#${parts[1]}` : '—', title: parts ? parts[2] : item.title,
                    subtitle: parts?.[3] ? `/ ${parts[3]}` : item.status === 'skipped' ? item.message : '',
                    expectedText: compact(item.expected), actualText: compact(item.actual) };
            }),
        };
    };
})();
