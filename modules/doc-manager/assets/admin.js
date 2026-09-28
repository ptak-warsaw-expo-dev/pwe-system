(() => {
    'use strict';

    const cfg = window.PWESystemDoc;
    const app = document.getElementById('pwe-doc-app');
    if (!cfg || !app) return;

    const state = { dir: '', items: [], draggedPath: null };
    const list = document.getElementById('pwe-doc-list');
    const crumbs = document.getElementById('pwe-doc-breadcrumbs');
    const filter = document.getElementById('pwe-doc-filter');
    const dropzone = document.getElementById('pwe-doc-dropzone');
    const filesInput = document.getElementById('pwe-doc-files');
    const folderInput = document.getElementById('pwe-doc-folder');
    const progress = document.getElementById('pwe-doc-progress');
    const progressBar = progress.querySelector('.pwe-doc-progress-bar span');
    const progressLabel = progress.querySelector('.pwe-doc-progress-label');
    const toastBox = document.getElementById('pwe-doc-toast');
    const dialog = document.getElementById('pwe-doc-dialog');
    const dialogTitle = document.getElementById('pwe-doc-dialog-title');
    const dialogBody = document.getElementById('pwe-doc-dialog-body');
    const dialogConfirm = document.getElementById('pwe-doc-dialog-confirm');
    let dialogHandler = null;

    const esc = (value) => String(value ?? '').replace(/[&<>'"]/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[ch]));
    const ext = (name) => name.includes('.') ? name.split('.').pop().toLowerCase() : '';
    
    function toast(message, error = false) {
        const el = document.createElement('div');
        el.className = `pwe-doc-toast-item${error ? ' is-error' : ''}`;
        el.textContent = message;
        toastBox.appendChild(el);
        setTimeout(() => el.remove(), error ? 6500 : 3500);
    }

    async function request(action, data = {}, file = null) {
        const body = new FormData();
        body.append('action', `pwe_system_doc_${action}`);
        body.append('nonce', cfg.nonce);
        Object.entries(data).forEach(([key, value]) => body.append(key, value ?? ''));
        if (file) body.append('file', file, file.name);

        const res = await fetch(cfg.ajaxUrl, { method: 'POST', credentials: 'same-origin', body });
        let json;
        try { json = await res.json(); } catch (_) { throw new Error(`Błąd serwera (${res.status}).`); }
        if (!res.ok || !json.success) throw new Error(json?.data?.message || `Błąd (${res.status}).`);
        return json.data;
    }

    async function load(dir = state.dir) {
        list.innerHTML = '<tr><td colspan="6" class="pwe-doc-loading">Wczytywanie…</td></tr>';
        try {
            const data = await request('list', { dir });
            state.dir = data.dir || '';
            state.items = data.items || [];
            renderBreadcrumbs(data.breadcrumbs || []);
            renderList();
        } catch (e) {
            list.innerHTML = `<tr><td colspan="6" class="pwe-doc-empty">${esc(e.message)}</td></tr>`;
            toast(e.message, true);
        }
    }

    function renderBreadcrumbs(items) {
        crumbs.innerHTML = items.map(item => `<button type="button" class="pwe-doc-breadcrumb" data-dir="${esc(item.dir)}">${esc(item.label)}</button>`).join('');
        crumbs.querySelectorAll('[data-dir]').forEach(btn => btn.addEventListener('click', () => load(btn.dataset.dir || '')));
    }

    function fileIcon(item) {
        if (item.type === 'folder') return '<span class="dashicons dashicons-category"></span>';
        if (item.isImage && item.url) return `<img class="pwe-doc-file-preview" src="${esc(item.url)}?v=${Date.now()}" alt="">`;
        if (item.extension === 'pdf') return '<span class="dashicons dashicons-pdf"></span>';
        if (['mp4','webm','mov'].includes(item.extension)) return '<span class="dashicons dashicons-video-alt3"></span>';
        if (['zip','rar','7z','gz','tar'].includes(item.extension)) return '<span class="dashicons dashicons-archive"></span>';
        return '<span class="dashicons dashicons-media-default"></span>';
    }

    function renderList() {
        const q = (filter.value || '').trim().toLowerCase();
        const items = state.items.filter(item => !q || item.name.toLowerCase().includes(q));
        if (!items.length) {
            list.innerHTML = `<tr><td colspan="6" class="pwe-doc-empty">${q ? 'Brak pasujących elementów.' : 'Ten folder jest pusty.'}</td></tr>`;
            return;
        }

        list.innerHTML = items.map(item => {
            const name = item.type === 'folder'
                ? `<button type="button" data-open-dir="${esc(item.path)}">${esc(item.name)}</button>`
                : `<a href="${esc(item.url || '#')}" target="_blank" rel="noopener">${esc(item.name)}</a>`;
            const replace = item.type === 'file' ? `<button type="button" class="button" data-replace="${esc(item.path)}" title="Podmień"><span class="dashicons dashicons-update-alt"></span></button>` : '';
            const open = item.type === 'file' ? `<a class="button" href="${esc(item.url || '#')}" target="_blank" rel="noopener" title="Otwórz"><span class="dashicons dashicons-external"></span></a>` : '';
            const unzip = item.type === 'file' && item.extension === 'zip' ? `<button type="button" class="button" data-unzip="${esc(item.path)}" title="Rozpakuj ZIP"><span class="dashicons dashicons-editor-expand"></span></button>` : '';
            const dimensions = item.isImage && item.width && item.height ? `${esc(item.width)} × ${esc(item.height)} px` : '—';
            return `<tr class="pwe-doc-row" data-row-path="${esc(item.path)}" data-row-type="${esc(item.type)}">
                <td>
                    <div class="pwe-doc-name is-draggable" draggable="true" data-drag-path="${esc(item.path)}">
                        ${fileIcon(item)} ${name}
                    </div>
                </td>
                <td><span class="pwe-doc-type">${item.type === 'folder' ? 'Folder' : (item.extension ? item.extension.toUpperCase() : 'Plik')}</span></td>
                <td>${esc(item.sizeLabel || '—')}</td>
                <td class="pwe-doc-dimensions-cell">${dimensions}</td>
                <td>${esc(item.modified || '—')}</td>
                <td class="pwe-doc-actions-cell">
                    ${open}${replace}${unzip}
                    <button type="button" class="button" data-rename="${esc(item.path)}" title="Zmień nazwę"><span class="dashicons dashicons-edit"></span></button>
                    <button type="button" class="button" data-move="${esc(item.path)}" title="Przenieś"><span class="dashicons dashicons-move"></span></button>
                    <button type="button" class="button" data-delete="${esc(item.path)}" data-name="${esc(item.name)}" title="Usuń"><span class="dashicons dashicons-trash"></span></button>
                </td>
            </tr>`;
        }).join('');

        bindRows();
    }

    function bindRows() {
        list.querySelectorAll('[data-open-dir]').forEach(el => el.addEventListener('click', () => load(el.dataset.openDir)));
        list.querySelectorAll('[data-rename]').forEach(el => el.addEventListener('click', () => renameItem(el.dataset.rename)));
        list.querySelectorAll('[data-delete]').forEach(el => el.addEventListener('click', () => deleteItem(el.dataset.delete, el.dataset.name)));
        list.querySelectorAll('[data-move]').forEach(el => el.addEventListener('click', () => moveItem(el.dataset.move)));
        list.querySelectorAll('[data-replace]').forEach(el => el.addEventListener('click', () => replaceItem(el.dataset.replace)));
        list.querySelectorAll('[data-unzip]').forEach(el => el.addEventListener('click', () => unzipItem(el.dataset.unzip)));

        list.querySelectorAll('[data-drag-path]').forEach(el => {
            el.addEventListener('dragstart', event => {
                state.draggedPath = el.dataset.dragPath;
                event.dataTransfer.effectAllowed = 'move';
                event.dataTransfer.setData('text/pwe-doc-path', state.draggedPath);
            });
            el.addEventListener('dragend', () => {
                state.draggedPath = null;
                list.querySelectorAll('.is-drop-target').forEach(r => r.classList.remove('is-drop-target'));
            });
        });

        list.querySelectorAll('[data-row-type="folder"]').forEach(row => {
            row.addEventListener('dragover', event => {
                if (!state.draggedPath) return;
                event.preventDefault();
                event.dataTransfer.dropEffect = 'move';
                row.classList.add('is-drop-target');
            });
            row.addEventListener('dragleave', () => row.classList.remove('is-drop-target'));
            row.addEventListener('drop', async event => {
                if (!state.draggedPath) return;
                event.preventDefault();
                row.classList.remove('is-drop-target');
                const targetDir = row.dataset.rowPath;
                const source = state.draggedPath;
                if (!source || source === targetDir) return;
                try {
                    const data = await request('move', { source, targetDir });
                    toast(data.message);
                    await load();
                } catch (e) { toast(e.message, true); }
            });
        });
    }

    function openDialog(title, html, confirmLabel, handler) {
        dialogTitle.textContent = title;
        dialogBody.innerHTML = html;
        dialogConfirm.textContent = confirmLabel || 'Zapisz';
        dialogHandler = handler;
        dialog.showModal();
        const input = dialogBody.querySelector('input');
        if (input) { setTimeout(() => { input.focus(); input.select(); }, 30); }
    }

    dialogConfirm.addEventListener('click', async () => {
        if (!dialogHandler) return;
        dialogConfirm.disabled = true;
        try {
            const close = await dialogHandler();
            if (close !== false) dialog.close();
        } catch (e) { toast(e.message, true); }
        finally { dialogConfirm.disabled = false; }
    });

    async function newFolder() {
        openDialog('Nowy folder', '<label>Nazwa folderu</label><input type="text" id="pwe-doc-dialog-input" autocomplete="off">', 'Utwórz', async () => {
            const name = document.getElementById('pwe-doc-dialog-input').value.trim();
            const data = await request('mkdir', { dir: state.dir, name });
            toast(data.message); await load();
        });
    }

    function renameItem(path) {
        const item = state.items.find(x => x.path === path);
        openDialog('Zmień nazwę', `<label>Nowa nazwa</label><input type="text" id="pwe-doc-dialog-input" value="${esc(item?.name || '')}" autocomplete="off">`, 'Zmień nazwę', async () => {
            const name = document.getElementById('pwe-doc-dialog-input').value.trim();
            const data = await request('rename', { path, name });
            toast(data.message); await load();
        });
    }

    function deleteItem(path, name) {
        openDialog('Usuń element', `<p>Usunąć <strong>${esc(name)}</strong>?</p><p>Jeżeli to folder, zostanie usunięty razem z całą zawartością. Tej operacji nie można cofnąć.</p>`, 'Usuń', async () => {
            const data = await request('delete', { path });
            toast(data.message); await load();
        });
    }

    function moveItem(path) {
        openDialog('Przenieś element', `<label>Folder docelowy względem /doc</label><input type="text" id="pwe-doc-dialog-input" value="${esc(state.dir)}" placeholder="np. Logotypy/2027"><p class="description">Puste pole oznacza główny katalog /doc.</p>`, 'Przenieś', async () => {
            const targetDir = document.getElementById('pwe-doc-dialog-input').value.trim();
            const data = await request('move', { source: path, targetDir });
            toast(data.message); await load();
        });
    }

    function unzipItem(path) {
        const item = state.items.find(x => x.path === path);
        const parentDir = path.includes('/') ? path.split('/').slice(0, -1).join('/') : '';
        openDialog(
            'Rozpakuj ZIP',
            `<p>Rozpakuj <strong>${esc(item?.name || path)}</strong>.</p>
             <label>Katalog docelowy względem /doc</label>
             <input type="text" id="pwe-doc-dialog-input" value="${esc(parentDir)}" placeholder="np. katalog/obrazy" autocomplete="off">
             <label class="pwe-doc-checkbox"><input type="checkbox" id="pwe-doc-unzip-overwrite"> Nadpisz istniejące pliki</label>
             <p class="description">Puste pole oznacza główny katalog /doc. Struktura folderów z archiwum zostanie zachowana.</p>`,
            'Rozpakuj',
            async () => {
                const targetDir = document.getElementById('pwe-doc-dialog-input').value.trim();
                const overwrite = document.getElementById('pwe-doc-unzip-overwrite').checked ? '1' : '0';
                const data = await request('unzip', { path, targetDir, overwrite });
                toast(data.message);
                await load();
            }
        );
    }

    function replaceItem(path) {
        const picker = document.createElement('input');
        picker.type = 'file';
        picker.hidden = true;
        document.body.appendChild(picker);
        picker.addEventListener('change', async () => {
            const file = picker.files?.[0];
            picker.remove();
            if (!file) return;
            showProgress(0, `Podmienianie ${file.name}…`);
            try {
                const data = await request('replace', { dir: state.dir, replacePath: path }, file);
                toast(data.message); await load();
            } catch (e) { toast(e.message, true); }
            finally { hideProgressSoon(); }
        }, { once: true });
        picker.click();
    }

    function showProgress(percent, label) {
        progress.hidden = false;
        progressBar.style.width = `${Math.max(0, Math.min(100, percent))}%`;
        progressLabel.textContent = label || '';
    }
    function hideProgressSoon() { setTimeout(() => { progress.hidden = true; progressBar.style.width = '0%'; }, 700); }

    async function uploadFiles(entries) {
        if (!entries.length) return;
        progress.hidden = false;
        let done = 0;
        let errors = 0;
        for (const entry of entries) {
            const relativePath = (entry.relativePath || entry.file.webkitRelativePath || entry.file.name).replace(/^\/+/, '');
            showProgress(Math.round((done / entries.length) * 100), `${done + 1}/${entries.length}: ${relativePath}`);
            try {
                await request('upload', { dir: state.dir, relativePath }, entry.file);
            } catch (e) {
                errors++;
                toast(`${relativePath}: ${e.message}`, true);
            }
            done++;
        }
        showProgress(100, errors ? `Zakończono. Błędy: ${errors}.` : `Gotowe. Wgrano ${done} plików.`);
        if (!errors) toast(`Wgrano ${done} plików.`);
        await load();
        hideProgressSoon();
    }

    function fromFileList(fileList) {
        return Array.from(fileList || []).map(file => ({ file, relativePath: file.webkitRelativePath || file.name }));
    }

    async function readDroppedItems(dataTransfer) {
        const items = Array.from(dataTransfer.items || []);
        const out = [];
        if (!items.length || !items.some(item => typeof item.webkitGetAsEntry === 'function')) return fromFileList(dataTransfer.files);

        async function readEntry(entry, prefix = '') {
            if (entry.isFile) {
                const file = await new Promise((resolve, reject) => entry.file(resolve, reject));
                out.push({ file, relativePath: `${prefix}${file.name}` });
                return;
            }
            if (entry.isDirectory) {
                const reader = entry.createReader();
                const children = [];
                while (true) {
                    const batch = await new Promise((resolve, reject) => reader.readEntries(resolve, reject));
                    if (!batch.length) break;
                    children.push(...batch);
                }
                for (const child of children) await readEntry(child, `${prefix}${entry.name}/`);
            }
        }

        for (const item of items) {
            const entry = item.webkitGetAsEntry?.();
            if (entry) await readEntry(entry, '');
        }
        return out;
    }

    app.querySelector('[data-action="new-folder"]').addEventListener('click', newFolder);
    app.querySelector('[data-action="pick-files"]').addEventListener('click', () => filesInput.click());
    app.querySelector('[data-action="pick-folder"]').addEventListener('click', () => folderInput.click());
    app.querySelector('[data-action="refresh"]').addEventListener('click', () => load());
    filesInput.addEventListener('change', () => { uploadFiles(fromFileList(filesInput.files)); filesInput.value = ''; });
    folderInput.addEventListener('change', () => { uploadFiles(fromFileList(folderInput.files)); folderInput.value = ''; });
    filter.addEventListener('input', renderList);

    ['dragenter','dragover'].forEach(type => dropzone.addEventListener(type, event => { event.preventDefault(); dropzone.classList.add('is-dragover'); }));
    ['dragleave','drop'].forEach(type => dropzone.addEventListener(type, event => { event.preventDefault(); dropzone.classList.remove('is-dragover'); }));
    dropzone.addEventListener('drop', async event => {
        try { await uploadFiles(await readDroppedItems(event.dataTransfer)); }
        catch (e) { toast(e.message || 'Nie udało się odczytać upuszczonych plików.', true); }
    });

    load('');
})();
