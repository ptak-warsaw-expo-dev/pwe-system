document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('pwe-shortcode-search');
    const count = document.getElementById('pwe-shortcode-search-count');

    if (!input) {
        return;
    }

    const normalize = function (value) {
        return String(value || '')
            .toLocaleLowerCase('pl')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim();
    };

    const items = Array.from(document.querySelectorAll('.pwe-shortcode-item'));
    const groups = Array.from(document.querySelectorAll('details.shortcodes-box'));

    const refresh = function () {
        const query = normalize(input.value);
        let visible = 0;

        items.forEach(function (item) {
            const haystack = normalize(item.dataset.shortcodeSearch || '');
            const matches = query === '' || haystack.includes(query);
            item.hidden = !matches;
            if (matches) {
                visible += 1;
            }
        });

        groups.forEach(function (group) {
            const groupItems = Array.from(group.querySelectorAll('.pwe-shortcode-item'));
            if (!groupItems.length) {
                return;
            }

            const hasMatch = groupItems.some(function (item) {
                return !item.hidden;
            });

            group.hidden = query !== '' && !hasMatch;
            if (query !== '' && hasMatch) {
                group.open = true;
            }
        });

        if (count) {
            count.textContent = query === ''
                ? items.length + ' shortcodów'
                : visible + ' z ' + items.length;
        }
    };

    input.addEventListener('input', refresh);
    refresh();
});
