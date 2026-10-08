(() => {
    'use strict';
    const root = document.getElementById('pwe-diagnostics');
    if (!root) return;
    const config = JSON.parse(document.getElementById('pwe-diagnostics-config').textContent);
    const tests = config.tests;
    let results = config.results || {}, busy = false, selected = null;
    const $ = id => document.getElementById(id);
    const labels = { success: 'OK', warning: 'Ostrzeżenie', error: 'Błąd', skipped: 'Pominięty', pending: 'Nieuruchomiony', running: 'W trakcie' };
    const rank = { error: 0, warning: 1, running: 2, pending: 3, skipped: 4, success: 5 };
    const status = test => (results[test.id] || {}).status || 'pending';
    const { el, svgIcon, badge } = window.PWETestsUI;
    const modal = window.PWETestModal.create($('diag-dialog'), id => run([id]));
    const categories = [...new Set(tests.map(t => t.category))];
    const opened = new Map(categories.map((c, i) => [c, i === 0]));
    function options(id, values) { values.forEach(([value, text]) => { const option = el('option', text); option.value = value; $(id).append(option); }); }
    options('diag-category', categories.map(v => [v, v]));
    options('diag-component', [...new Set(tests.map(t => t.component))].sort().map(v => [v, v]));
    options('diag-status', Object.entries(labels));
    function render() {
        const query = $('diag-search').value.trim().toLocaleLowerCase('pl');
        const filtered = tests.filter(t => (!query || `${t.name} ${t.description} ${t.component}`.toLocaleLowerCase('pl').includes(query)) &&
            (!$('diag-category').value || t.category === $('diag-category').value) &&
            (!$('diag-component').value || t.component === $('diag-component').value) &&
            (!$('diag-status').value || status(t) === $('diag-status').value));
        const sort = $('diag-sort').value;
        filtered.sort((a, b) => (sort === 'status' ? rank[status(a)] - rank[status(b)] : sort === 'critical' ? Number(b.critical) - Number(a.critical) : 0) || a.name.localeCompare(b.name, 'pl'));
        const counts = $('diag-counts'); counts.replaceChildren(el('strong', `Wszystkie testy (${tests.length})`));
        Object.keys(labels).forEach(s => { const count = tests.filter(t => status(t) === s).length; if (count || ['success', 'warning', 'error'].includes(s)) counts.append(badge(s, `${count} ${labels[s]}`)); });
        counts.append(el('small', `Widoczne: ${filtered.length}`));
        const container = $('diag-results'); container.replaceChildren();
        categories.forEach(category => {
            const members = filtered.filter(t => t.category === category);
            if (!members.length) return;
            const group = el('details', undefined, 'diag-category'); group.open = opened.get(category) || !!query || !!$('diag-status').value;
            group.addEventListener('toggle', () => opened.set(category, group.open));
            const heading = el('summary');
            const groupIcon = el('span', undefined, 'diag-section-icon');
            groupIcon.append(svgIcon(({ 'Rdzeń wtyczki': 'tests', 'Testy formularzy': 'forms', 'QR i feedy': 'grid', 'Integracja z Gravity Forms': 'gf_addon', 'Tłumaczenia i języki': 'language' })[category] || 'tests'));
            heading.append(groupIcon, el('strong', `${category} (${members.length})`));
            const tally = el('span', undefined, 'diag-category-counts');
            Object.keys(labels).forEach(s => { const count = members.filter(t => status(t) === s).length; if (count) tally.append(badge(s, `${count} ${labels[s]}`)); });
            heading.append(tally, svgIcon('chevron')); group.append(heading);
            const cards = el('div', undefined, 'diag-cards');
            members.forEach(test => {
                const card = el('article', undefined, 'diag-card');
                const icon = el('span', undefined, 'diag-card-icon'); icon.append(svgIcon(({ QR: 'grid', Translations: 'language', Notifications: 'mail', Snapshots: 'log', HTML: 'code', Forms: 'forms', Pages: 'language', 'Conditional logic': 'gf_addon' })[test.component] || 'tests'));
                const content = el('div', undefined, 'diag-card-content');
                content.append(el('h3', test.name), el('p', test.description));
                const details = el('button', 'Szczegóły'); details.append(svgIcon('arrow')); details.type = 'button'; details.addEventListener('click', () => show(test.id));
                content.append(details); card.append(icon, content, badge(status(test))); cards.append(card);
            });
            group.append(cards); container.append(group);
        });
        if (!filtered.length) container.append(el('p', 'Brak testów pasujących do filtrów.', 'diag-empty'));
    }
    async function request(operation, id) {
        const controller = new AbortController(); const timer = setTimeout(() => controller.abort(), 120000);
        try {
            const response = await fetch(config.url, { method: 'POST', credentials: 'same-origin', signal: controller.signal,
                body: new URLSearchParams({ action: 'pwe_system_tests_diagnostic', nonce: config.nonce, operation, id: id || '' }) });
            const json = await response.json();
            if (!response.ok || !json.success) throw new Error(json.data?.message || 'Błąd żądania. Odśwież stronę i sprawdź uprawnienia.');
            return json.data;
        } finally { clearTimeout(timer); }
    }
    function lock(value) {
        busy = value; modal.setBusy(value);
        root.querySelectorAll('[data-run], #diag-clear, #diag-single').forEach(button => button.disabled = value);
    }
    async function run(ids) {
        if (busy) return;
        lock(true);
        let done = 0;
        try {
            for (const id of ids) {
                results[id] = { status: 'running', message: 'Test jest wykonywany.' }; render();
                if (selected === id && $('diag-dialog').open) fillDetails();
                $('diag-progress').textContent = `Wykonywanie ${done + 1}/${ids.length}: ${tests.find(t => t.id === id).name}`;
                try {
                    let operation = 'run';
                    do {
                        results[id] = await request(operation, id);
                        render();
                        if (selected === id && $('diag-dialog').open) fillDetails();
                        $('diag-progress').textContent = results[id].message || '';
                        operation = 'continue';
                    } while (results[id].continue);
                }
                catch (error) { results[id] = { status: 'error', message: `Nie udało się pobrać wyniku: ${error.message}`, details: { transport_error: true }, timestamp: new Date().toISOString() }; }
                done++; render();
                if (selected === id && $('diag-dialog').open) fillDetails();
            }
            $('diag-progress').textContent = `Zakończono: ${done}/${ids.length}. Szczegóły zawierają wyniki i sugestie.`;
        } finally { lock(false); }
    }
    function fillDetails() { modal.update(tests.find(t => t.id === selected), results[selected] || {}, busy); }
    function show(id) { selected = id; modal.open(tests.find(t => t.id === id), results[id] || {}, busy); }
    root.querySelectorAll('[data-run]').forEach(button => button.addEventListener('click', () => {
        const group = button.dataset.run;
        run(tests.filter(t => group === 'all' || (group === 'critical' && t.critical) ||
            (group === 'forms' && t.category === 'Testy formularzy') ||
            (group === 'languages' && t.category === 'Tłumaczenia i języki') ||
            (group === 'integrations' && ['Integracja z Gravity Forms', 'QR i feedy'].includes(t.category))).map(t => t.id));
    }));
    $('diag-clear').addEventListener('click', async () => {
        if (busy) return; lock(true);
        try { await request('clear'); results = {}; render(); $('diag-progress').textContent = 'Wyniki wyczyszczone.'; }
        catch (error) { $('diag-progress').textContent = error.message; }
        finally { lock(false); }
    });
    ['diag-search', 'diag-category', 'diag-component', 'diag-status', 'diag-sort'].forEach(id => $(id).addEventListener(id === 'diag-search' ? 'input' : 'change', render));
    $('diag-reset').addEventListener('click', () => { ['diag-search', 'diag-category', 'diag-component', 'diag-status'].forEach(id => $(id).value = ''); render(); });
    root.querySelectorAll('[data-view]').forEach(button => button.addEventListener('click', () => {
        root.classList.toggle('diag-list', button.dataset.view === 'list');
        root.querySelectorAll('[data-view]').forEach(b => b.setAttribute('aria-pressed', String(b === button)));
    }));
    render();
})();
