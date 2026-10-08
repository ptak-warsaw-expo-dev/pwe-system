(() => {
    'use strict';
    const { el, svgIcon, badge } = window.PWETestsUI;
    window.PWETestModal = { create };
    function create(dialog, onRun) {
        const $ = id => dialog.querySelector(`#${id}`);
        let current = null;
        const formatValue = value => value == null ? 'Brak' : typeof value === 'object' ? JSON.stringify(value, null, 2) : typeof value === 'boolean' ? (value ? 'Tak' : 'Nie') : String(value);
        const detailName = key => ({ expected: 'Oczekiwany wynik', actual: 'Wynik rzeczywisty', value: 'Wartość', ...current.view.fieldLabels })[key] || key;
        const hasData = value => value && (typeof value !== 'object' || Object.keys(value).length);
        function disclosure(label, content) {
            const fold = el('details', undefined, 'diag-value-fold'), summary = el('summary', label);
            summary.append(svgIcon('chevron')); fold.append(summary, content); return fold;
        }
        // A language-code array paired with a flag URL map renders as flag icons instead of raw text.
        function flagIcon(code, url) {
            if (!url) return el('span', String(code).toUpperCase(), 'diag-flag-fallback');
            const img = document.createElement('img');
            img.src = url; img.alt = ''; img.loading = 'lazy'; img.title = String(code).toUpperCase(); img.className = 'diag-flag-icon';
            return img;
        }
        function savedValuesList(values) {
            const labels = { name: 'Nazwa', _pwe_lang: 'Język', conditionalLogic: 'Warunki wyświetlania', subject: 'Temat', message: 'Treść', type: 'Typ', url: 'Adres przekierowania', pageId: 'ID strony', queryString: 'Parametry adresu', actionType: 'Działanie', logicType: 'Łączenie warunków', rules: 'Reguły', field: 'Pole', fieldId: 'ID pola', operator: 'Operator', value: 'Wartość' };
            const list = el('dl', undefined, 'diag-saved-values');
            Object.entries(values).forEach(([key, value]) => {
                const row = el('div'), content = el('dd');
                if (value && typeof value === 'object' && Object.keys(value).length) {
                    row.className = 'diag-saved-group';
                    content.append(savedValuesList(value));
                } else {
                    content.className = 'diag-saved-text';
                    content.textContent = value == null || value === '' || (typeof value === 'object' && !Object.keys(value).length) ? 'Brak' : formatValue(value);
                }
                row.append(el('dt', Array.isArray(values) ? `Reguła ${Number(key) + 1}` : labels[key] || key), content);
                list.append(row);
            });
            return list;
        }
        function evidenceCard(item) {
            const card = el('div', undefined, 'diag-row-details');
            if (!item || typeof item !== 'object') { card.append(el('span', formatValue(item))); return card; }
            if (Object.hasOwn(item, 'expected') || Object.hasOwn(item, 'actual')) {
                const pair = el('div', undefined, 'diag-row-comparison');
                ['expected', 'actual'].forEach(key => {
                    const state = key === 'expected' ? 'expected' : item.status || 'pending';
                    const panel = el('section', undefined, `diag-compare-panel diag-compare-${state}`);
                    const heading = el('div', undefined, 'diag-compare-heading');
                    heading.append(svgIcon(key === 'expected' ? 'forms' : ({ success: 'check', error: 'close', warning: 'warning' })[item.status] || 'info'), el('strong', detailName(key)));
                    panel.append(heading);
                    const values = item[key] && typeof item[key] === 'object' ? Object.entries(item[key]) : [['value', item[key]]];
                    const list = el('dl', undefined, 'diag-compare-values');
                    values.forEach(([name, value]) => {
                        const row = el('div'), dd = el('dd');
                        if (Array.isArray(value) && item.flags) {
                            dd.className = 'diag-flag-row';
                            dd.append(...(value.length ? value.map(code => flagIcon(code, item.flags[code])) : [el('span', '—', 'diag-muted')]));
                        } else {
                            dd.append(el('code', value === null || value === undefined ? 'Brak' : typeof value === 'object' ? JSON.stringify(value) : String(value)));
                        }
                        row.append(el('dt', detailName(name)), dd); list.append(row);
                    });
                    panel.append(list); pair.append(panel);
                });
                card.append(pair);
            }
            if (item.message) {
                const description = el('div', undefined, 'diag-row-description');
                description.append(svgIcon('log'), el('strong', 'Opis'), el('span', item.message)); card.append(description);
            }
            if (item.savedValues && typeof item.savedValues === 'object') {
                const section = el('section', undefined, 'diag-saved-section');
                const heading = el('div', undefined, 'diag-compare-heading');
                heading.append(svgIcon('forms'), el('strong', 'Zapisane wartości'));
                section.append(heading, savedValuesList(item.savedValues));
                card.append(section);
            }
            return card;
        }
        function meaningfulSuggestions(result) {
            return (Array.isArray(result.suggestions) ? result.suggestions : []).filter(s => typeof s === 'string' && s.trim() && s.trim() !== 'Brak dodatkowych sugestii.');
        }
        function renderDetails() {
            const visual = $('diag-detail-visual'); visual.replaceChildren();
            const details = current.result.details || {};
            const records = current.view.rows;
            const query = $('diag-row-search').value.trim().toLocaleLowerCase('pl');
            const filter = $('diag-row-status').value;
            const matches = records.filter(item => (!filter || item.status === filter) && (!query || JSON.stringify(item).toLocaleLowerCase('pl').includes(query)));
            $('diag-row-count').textContent = `Widoczne: ${matches.length} z ${records.length}`;
            const wrap = el('div', undefined, 'diag-results-scroll');
            const table = el('table', undefined, 'diag-results-table');
            const head = el('thead'), header = el('tr');
            ['#', current.view.columnLabel || 'Element', 'Oczekiwany wynik', 'Wynik rzeczywisty', 'Status', 'Szczegóły'].forEach((text, index) => { const th = el('th', text); th.scope = 'col'; if (index === 5) th.className = 'diag-toggle-heading'; header.append(th); });
            head.append(header); table.append(head);
            const body = el('tbody');
            function valueCell(value) {
                const cell = el('td');
                if (value !== undefined && value !== null) cell.append(el('code', formatValue(value), 'diag-code-chip'));
                else cell.append(el('span', '—', 'diag-muted'));
                return cell;
            }
            matches.forEach((item, index) => {
                const title = String(item.title || item.case || item.file || 'Element');
                const row = el('tr'); row.append(el('td', String(item.id ?? '—'), 'diag-row-id'));
                const name = el('td'); name.append(el('strong', title));
                if (item.subtitle) name.append(el('small', item.subtitle));
                if (item.url) {
                    try {
                        const url = new URL(item.url);
                        if (['http:', 'https:'].includes(url.protocol)) {
                            const link = el('a', item.url);
                            link.href = url.href; link.target = '_blank'; link.rel = 'noopener noreferrer';
                            const line = el('small'); line.append(link); name.append(line);
                        }
                    } catch (_) { /* Ignore malformed report URLs. */ }
                }
                row.append(name, valueCell(item.expectedText ?? item.expected), valueCell(item.actualText ?? item.actual));
                const state = el('td'); state.append(badge(item.status || 'pending')); row.append(state);
                const toggleCell = el('td'), toggle = el('button'); toggle.type = 'button'; toggle.className = 'diag-row-toggle'; toggle.append(svgIcon('chevron'));
                toggle.setAttribute('aria-label', `Szczegóły: ${title}`); toggle.setAttribute('aria-expanded', 'false');
                const expanded = el('tr', undefined, 'diag-expanded-row'); expanded.hidden = true; expanded.id = `diag-expanded-${index}`;
                toggle.setAttribute('aria-controls', expanded.id);
                const full = el('td'); full.colSpan = 6; expanded.append(full);
                toggle.addEventListener('click', () => {
                    expanded.hidden = !expanded.hidden; toggle.setAttribute('aria-expanded', String(!expanded.hidden));
                    if (!expanded.hidden && !full.children.length) full.append(evidenceCard(item, index));
                });
                toggleCell.append(toggle); row.append(toggleCell); body.append(row, expanded);
            });
            if (!matches.length) { const row = el('tr'), cell = el('td', records.length ? 'Brak wyników pasujących do filtrów.' : 'Brak szczegółowych wyników. Uruchom test lub sprawdź komunikat powyżej.', 'diag-table-empty'); cell.colSpan = 6; row.append(cell); body.append(row); }
            table.append(body); wrap.append(table); visual.append(wrap);
            ['errors', 'warnings'].forEach(key => {
                if (!hasData(details[key])) return;
                visual.append(disclosure(`${key === 'errors' ? 'Komunikaty błędów' : 'Ostrzeżenia'} (${Object.keys(details[key]).length})`, el('pre', formatValue(details[key]), 'diag-message-data')));
            });
        }
        function update(test, result = {}, busy = false) {
            const details = result.details || {};
            const custom = window.PWETestsViews?.[test.id]?.(result) || result.view || {};
            const count = key => details[key] ? Object.keys(details[key]).length : result.status === (key === 'errors' ? 'error' : 'warning') ? 1 : 0;
            const rows = Array.isArray(custom.rows) ? custom.rows : Object.values(details.checked || {});
            current = { test, result, view: { rows, columnLabel: 'Element', fieldLabels: {}, ...custom } };
            const view = current.view, state = result.status || 'pending';
            $('diag-dialog-title').textContent = test.name;
            $('diag-detail-description').textContent = test.description;
            $('diag-modal-symbol').replaceChildren(svgIcon(view.icon || 'tests'));
            $('diag-detail-meta').replaceChildren(svgIcon('clock'), el('span', `${result.timestamp ? new Date(result.timestamp).toLocaleString('pl-PL') : 'Jeszcze nie uruchomiono'} · ${result.execution_time ?? '—'} ms`));
            $('diag-result-banner').className = `diag-result-banner diag-banner-${state}`;
            $('diag-result-symbol').replaceChildren(svgIcon(({ success: 'check', error: 'close', warning: 'warning', running: 'sync' })[state] || 'info'));
            $('diag-detail-message').textContent = view.message || result.message || 'Uruchom test, aby zobaczyć wynik.';
            const metrics = view.metrics || [{ label: 'Elementy', value: result.status && state !== 'running' ? rows.length : null, icon: 'forms' }, { label: 'Problemy', value: result.status && state !== 'running' ? count('errors') : null, icon: 'warning', tone: 'error' }, { label: 'Ostrzeżenia', value: result.status && state !== 'running' ? count('warnings') : null, icon: 'info', tone: 'warning' }];
            $('diag-metrics').replaceChildren(...metrics.map(metric => {
                const card = el('div', undefined, 'diag-metric'), icon = el('span', undefined, `diag-metric-icon${['error', 'warning'].includes(metric.tone) ? ` diag-metric-${metric.tone}` : ''}`), text = el('div');
                icon.append(svgIcon(metric.icon || 'info')); text.append(el('small', metric.label), el('strong', String(metric.value ?? '—'))); card.append(icon, text); return card;
            }));
            $('diag-duration').textContent = `Czas wykonania: ${result.execution_time ?? '—'} ms`;
            $('diag-detail-data').textContent = JSON.stringify(result, null, 2);
            const suggestions = meaningfulSuggestions(result);
            $('diag-next-step').hidden = !suggestions.length;
            $('diag-detail-suggestions').replaceChildren(...suggestions.map(s => el('li', s)));
            setBusy(busy); renderDetails();
        }
        function setBusy(busy) { $('diag-single').disabled = busy; }
        function open(test, result, busy) {
            $('diag-row-search').value = ''; $('diag-row-status').value = ''; $('diag-raw').open = false;
            update(test, result, busy); if (!dialog.open) dialog.showModal();
        }
        ['diag-row-search', 'diag-row-status'].forEach(id => $(id).addEventListener(id === 'diag-row-search' ? 'input' : 'change', renderDetails));
        $('diag-close').addEventListener('click', () => dialog.close());
        $('diag-single').addEventListener('click', () => { if (current) onRun(current.test.id); });
        return { open, update, setBusy, isOpen: () => dialog.open };
    }
})();