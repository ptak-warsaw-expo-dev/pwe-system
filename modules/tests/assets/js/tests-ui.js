(() => {
    'use strict';
    const labels = { success: 'OK', warning: 'Ostrzeżenie', error: 'Błąd', skipped: 'Pominięty', pending: 'Nieuruchomiony', running: 'W trakcie' };
    const el = (tag, text, cls) => { const node = document.createElement(tag); if (text !== undefined) node.textContent = text; if (cls) node.className = cls; return node; };
    const svgIcon = name => {
        const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        for (const [key, value] of Object.entries({ class: 'diag-icon', viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': '1.8', 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'aria-hidden': 'true', focusable: 'false' })) svg.setAttribute(key, value);
        const use = document.createElementNS('http://www.w3.org/2000/svg', 'use');
        use.setAttribute('href', `#pwe-tests-icon-${name}`); svg.append(use); return svg;
    };
    const badge = (state, text) => {
        const node = el('span', undefined, `diag-badge diag-${state}`);
        node.append(svgIcon(({ success: 'check', warning: 'warning', error: 'close', running: 'sync', pending: 'clock', skipped: 'info' })[state] || 'info'), el('span', text || labels[state]));
        return node;
    };
    window.PWETestsUI = { el, svgIcon, badge, labels };
})();
