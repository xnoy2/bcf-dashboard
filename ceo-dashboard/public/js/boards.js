/* Boards (Trello-style kanban) — board rendering, drag-and-drop, card modal.
 * Depends on: SortableJS (CDN) and Bootstrap (loaded by the layout).
 * Reads window.__BOARD (see boards/show.blade.php). All writes go through the
 * board endpoints and return either a card summary or full detail. */
(function () {
    'use strict';

    var board = window.__BOARD;
    if (!board) return;

    var canvas = document.getElementById('boardCanvas');
    var CSRF = document.querySelector('meta[name="csrf-token"]').content;
    var dragEndedAt = 0;
    var LABEL_COLORS = ['#4E7C59', '#3B7D6E', '#7D7D1F', '#C8A24B', '#CD8B3C', '#B5495B', '#5E7C99', '#2563EB', '#6A4E86', '#8A7F8E'];

    // ---- tiny helpers ------------------------------------------------------
    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
    function h(html) { var t = document.createElement('template'); t.innerHTML = html.trim(); return t.content.firstElementChild; }

    // Escape text, then turn http(s):// and www. URLs into clickable links.
    function linkify(text) {
        return esc(text).replace(/(https?:\/\/[^\s<]+|www\.[^\s<]+)/g, function (url) {
            var trail = '', m = url.match(/[)\].,;:!?]+$/);
            if (m) { trail = m[0]; url = url.slice(0, -trail.length); }
            var href = /^https?:\/\//i.test(url) ? url : 'https://' + url;
            return '<a href="' + href + '" target="_blank" rel="noopener noreferrer">' + url + '</a>' + trail;
        });
    }

    function api(method, url, body, isForm) {
        var opts = { method: method, headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } };
        if (isForm) { opts.body = body; }
        else if (body !== undefined) { opts.headers['Content-Type'] = 'application/json'; opts.body = JSON.stringify(body); }
        return fetch(url, opts).then(function (r) {
            return r.json().catch(function () { return {}; }).then(function (d) {
                if (!r.ok) { throw (d && (d.message || (d.errors && Object.values(d.errors)[0][0]))) || 'Request failed'; }
                return d;
            });
        });
    }
    function toast(msg) { alert(msg); }

    // In-app confirmation modal (replaces browser confirm()).
    var _confirmModal;
    function askConfirm(opts, onOk) {
        document.getElementById('confirmTitle').textContent = opts.title || 'Please confirm';
        document.getElementById('confirmBody').textContent = opts.body || 'Are you sure?';
        var okBtn = document.getElementById('confirmOk');
        okBtn.textContent = opts.ok || 'Remove';
        okBtn.className = 'btn ' + (opts.variant || 'btn-danger');
        // Replace the node to clear any handler bound by a previous call.
        var fresh = okBtn.cloneNode(true);
        okBtn.parentNode.replaceChild(fresh, okBtn);
        _confirmModal = _confirmModal || new bootstrap.Modal('#confirmModal');
        var m = _confirmModal;
        fresh.addEventListener('click', function () { m.hide(); onOk(); });
        m.show();
    }

    // ---- board render ------------------------------------------------------
    function renderBoard() {
        canvas.innerHTML = '';
        board.lists.forEach(function (list) { canvas.appendChild(renderList(list)); });
        canvas.appendChild(renderAddList());
        initListSortable();
        board.lists.forEach(function (list) { initCardSortable(list.id); });
    }

    function renderList(list) {
        var col = h('<div class="list-col" data-list-id="' + list.id + '"></div>');
        var head = h('<div class="list-head"></div>');
        head.innerHTML =
            '<span class="list-name" contenteditable="true" spellcheck="false">' + esc(list.name) + '</span>' +
            '<span class="list-count">' + list.cards.length + '</span>' +
            '<div class="dropdown list-menu">' +
                '<button class="list-menu-btn" data-bs-toggle="dropdown" data-bs-boundary="viewport" title="List actions" aria-label="List actions"><i class="bi bi-three-dots"></i></button>' +
                '<ul class="dropdown-menu dropdown-menu-end">' +
                    '<li><button type="button" class="dropdown-item list-dup"><i class="bi bi-files"></i> Duplicate</button></li>' +
                    '<li><button type="button" class="dropdown-item text-danger list-remove"><i class="bi bi-trash"></i> Remove</button></li>' +
                '</ul>' +
            '</div>';
        col.appendChild(head);

        var ul = h('<ul class="cards"></ul>');
        list.cards.forEach(function (card) { ul.appendChild(renderCard(card)); });
        col.appendChild(ul);

        col.appendChild(renderAddCard(list.id));

        // rename list
        var nameEl = head.querySelector('.list-name');
        nameEl.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); nameEl.blur(); } });
        nameEl.addEventListener('blur', function () {
            var v = nameEl.textContent.trim();
            if (!v || v === list.name) { nameEl.textContent = list.name; return; }
            api('PATCH', '/lists/' + list.id, { name: v }).then(function () { list.name = v; })
                .catch(function (e) { toast(e); nameEl.textContent = list.name; });
        });
        // duplicate list (with its cards)
        head.querySelector('.list-dup').addEventListener('click', function () {
            api('POST', '/lists/' + list.id + '/duplicate').then(function (d) {
                var idx = board.lists.findIndex(function (l) { return l.id === list.id; });
                board.lists.splice(idx + 1, 0, d.list);
                col.after(renderList(d.list));
                initCardSortable(d.list.id);
            }).catch(toast);
        });
        // remove list
        head.querySelector('.list-remove').addEventListener('click', function () {
            askConfirm({ title: 'Remove list', body: 'Remove “' + list.name + '” and its cards? This cannot be undone.', ok: 'Remove' }, function () {
                api('DELETE', '/lists/' + list.id).then(function () {
                    board.lists = board.lists.filter(function (l) { return l.id !== list.id; });
                    col.remove();
                    refreshAddListLabel();
                }).catch(toast);
            });
        });
        return col;
    }

    function renderCard(card) {
        var li = h('<li class="card-tile" data-card-id="' + card.id + '" title="Open card"></li>');
        var html = '';
        if (card.labels && card.labels.length) {
            html += '<div class="card-labels">' + card.labels.map(function (l) {
                return '<span class="card-label-chip" title="' + esc(l.name || '') + '" style="background:' + esc(l.color) + '"></span>';
            }).join('') + '</div>';
        }
        html += '<div class="card-tile-title">'
            + (card.completed ? '<i class="bi bi-check-circle-fill card-done"></i> ' : '')
            + esc(card.title) + '</div>';

        var badges = [];
        if (card.due) {
            var cls = card.due.completed ? 'done' : (card.due.overdue ? 'overdue' : '');
            badges.push('<span class="due-pill ' + cls + '"><i class="bi bi-clock"></i> ' + esc(card.due.label) + '</span>');
        }
        if (card.has_description) badges.push('<span class="badge-pill"><i class="bi bi-text-left"></i></span>');
        if (card.checklist && card.checklist.total) {
            var done = card.checklist.done === card.checklist.total ? ' style="color:#4E7C59;font-weight:700"' : '';
            badges.push('<span class="badge-pill"' + done + '><i class="bi bi-check2-square"></i> ' + card.checklist.done + '/' + card.checklist.total + '</span>');
        }
        if (card.counts && card.counts.comments) badges.push('<span class="badge-pill"><i class="bi bi-chat"></i> ' + card.counts.comments + '</span>');
        if (card.counts && card.counts.attachments) badges.push('<span class="badge-pill"><i class="bi bi-paperclip"></i> ' + card.counts.attachments + '</span>');

        var assignees = '';
        if (card.members && card.members.length) {
            assignees = '<span class="card-assignees">' + card.members.map(function (m) {
                return '<span class="avatar-xs" title="' + esc(m.name) + '">' + esc(m.initials) + '</span>';
            }).join('') + '</span>';
        }
        if (badges.length || assignees) html += '<div class="card-badges">' + badges.join('') + assignees + '</div>';

        li.innerHTML = html;
        return li;
    }

    // ---- composers ---------------------------------------------------------
    function renderAddCard(listId) {
        var wrap = h('<div class="composer"></div>');
        var btn = h('<button class="add-btn" title="Add a card"><i class="bi bi-plus-lg"></i> Add a card</button>');
        wrap.appendChild(btn);
        btn.addEventListener('click', function () {
            var form = h('<div class="mt-1"></div>');
            form.innerHTML =
                '<textarea class="form-control mb-1" rows="2" placeholder="Enter a title…"></textarea>' +
                '<button class="btn btn-sm btn-primary">Add card</button> ' +
                '<button class="btn btn-sm btn-light">Cancel</button>';
            wrap.replaceChild(form, btn);
            var ta = form.querySelector('textarea'); ta.focus();
            function close() { wrap.replaceChild(btn, form); }
            form.querySelector('.btn-light').addEventListener('click', close);
            ta.addEventListener('keydown', function (e) { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); add(); } });
            form.querySelector('.btn-primary').addEventListener('click', add);
            function add() {
                var v = ta.value.trim(); if (!v) return;
                api('POST', '/lists/' + listId + '/cards', { title: v }).then(function (d) {
                    var ul = canvas.querySelector('.list-col[data-list-id="' + listId + '"] .cards');
                    ul.appendChild(renderCard(d.card));
                    var list = board.lists.find(function (l) { return l.id === listId; });
                    if (list) list.cards.push(d.card);
                    bumpCount(listId);
                    ta.value = ''; ta.focus();
                }).catch(toast);
            }
        });
        return wrap;
    }

    var addListBtn = null;
    function addListLabel() {
        return '<i class="bi bi-plus-lg"></i> ' + (board.lists.length ? 'Add another list' : 'Add a list');
    }
    function refreshAddListLabel() { if (addListBtn) addListBtn.innerHTML = addListLabel(); }

    function renderAddList() {
        var wrap = h('<div class="list-add"></div>');
        var btn = h('<button class="add-btn" title="Add a list"></button>');
        addListBtn = btn;
        btn.innerHTML = addListLabel();
        wrap.appendChild(btn);
        btn.addEventListener('click', function () {
            var form = h('<div class="list-col"></div>');
            form.innerHTML =
                '<input class="form-control form-control-sm mb-1" placeholder="List name…">' +
                '<button class="btn btn-sm btn-primary">Add list</button> ' +
                '<button class="btn btn-sm btn-light">Cancel</button>';
            wrap.replaceChild(form, btn);
            var inp = form.querySelector('input'); inp.focus();
            form.querySelector('.btn-light').addEventListener('click', function () { wrap.replaceChild(btn, form); });
            inp.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); add(); } });
            form.querySelector('.btn-primary').addEventListener('click', add);
            function add() {
                var v = inp.value.trim(); if (!v) return;
                api('POST', '/boards/' + board.id + '/lists', { name: v }).then(function (d) {
                    var list = { id: d.list.id, name: d.list.name, cards: [] };
                    board.lists.push(list);
                    canvas.insertBefore(renderList(list), wrap);
                    initCardSortable(list.id);
                    wrap.replaceChild(btn, form);
                    refreshAddListLabel();
                }).catch(toast);
            }
        });
        return wrap;
    }

    function bumpCount(listId) {
        var col = canvas.querySelector('.list-col[data-list-id="' + listId + '"]');
        if (col) col.querySelector('.list-count').textContent = col.querySelectorAll('.card-tile').length;
    }

    // ---- drag & drop -------------------------------------------------------
    function initListSortable() {
        Sortable.create(canvas, {
            draggable: '.list-col', handle: '.list-head',
            // Don't start a drag when interacting with the name or the ⋯ menu.
            filter: '.list-add, .list-menu, .list-name', preventOnFilter: false, animation: 150,
            onEnd: function () {
                dragEndedAt = Date.now();
                var order = Array.prototype.map.call(canvas.querySelectorAll('.list-col'), function (c) { return +c.dataset.listId; });
                api('POST', '/boards/' + board.id + '/lists/reorder', { order: order }).catch(toast);
            }
        });
    }

    function initCardSortable(listId) {
        var ul = canvas.querySelector('.list-col[data-list-id="' + listId + '"] .cards');
        if (!ul) return;
        Sortable.create(ul, {
            group: 'cards', draggable: '.card-tile', animation: 150,
            onStart: function (e) { e.item.classList.add('dragging'); },
            onEnd: function (e) {
                e.item.classList.remove('dragging');
                dragEndedAt = Date.now();
                var toCol = e.to.closest('.list-col');
                var fromCol = e.from.closest('.list-col');
                var toListId = +toCol.dataset.listId;
                var order = Array.prototype.map.call(e.to.querySelectorAll('.card-tile'), function (c) { return +c.dataset.cardId; });
                api('POST', '/lists/' + toListId + '/cards/reorder', { order: order }).catch(toast);
                bumpCount(toListId);
                if (fromCol && fromCol !== toCol) bumpCount(+fromCol.dataset.listId);
            }
        });
    }

    // ---- card modal --------------------------------------------------------
    // Instantiated lazily so board rendering never depends on Bootstrap having
    // loaded yet (defensive — scripts are also pushed after the Bootstrap CDN).
    var _cardModal;
    function cardModalInstance() { return (_cardModal = _cardModal || new bootstrap.Modal('#cardModal')); }
    var current = null; // detail of the open card

    canvas.addEventListener('click', function (e) {
        var tile = e.target.closest('.card-tile');
        if (!tile) return;
        if (Date.now() - dragEndedAt < 250) return; // ignore click right after a drag
        openCard(+tile.dataset.cardId);
    });

    function openCard(id) {
        api('GET', '/cards/' + id).then(function (d) {
            current = d.card;
            renderModal();
            cardModalInstance().show();
        }).catch(toast);
    }

    function applyUpdate(card) { current = card; renderModal(); replaceTile(card); }

    function replaceTile(card) {
        var tile = canvas.querySelector('.card-tile[data-card-id="' + card.id + '"]');
        if (tile) tile.replaceWith(renderCard(card));
    }

    function g(id) { return document.getElementById(id); }

    function renderModal() {
        var c = current;
        g('cmTitle').textContent = c.title;
        g('cmListName').textContent = c.list_name;
        var comp = g('cmComplete');
        comp.classList.toggle('done', !!c.completed);
        comp.innerHTML = c.completed ? '<i class="bi bi-check-circle-fill"></i>' : '<i class="bi bi-circle"></i>';
        comp.title = c.completed ? 'Completed — click to reopen' : 'Mark complete';
        g('cmDescription').value = c.description || '';
        g('cmDescDisplay').innerHTML = c.description
            ? linkify(c.description)
            : '<span class="text-muted">Add a more detailed description…</span>';
        exitDescEdit();

        // labels row
        g('cmLabelsRow').innerHTML = (c.labels || []).map(function (l) {
            return '<span class="cm-label-chip" style="background:' + esc(l.color) + '">' + esc(l.name || '') + '</span>';
        }).join('');

        // due pill shown under the title
        var due = g('cmDueRow');
        if (c.due) {
            var cls = c.due.overdue ? 'overdue' : '';
            var parts = [];
            if (c.start) parts.push(esc(mdy(c.start)));
            parts.push(esc(c.due.label));
            due.innerHTML = '<span class="due-pill ' + cls + '"><i class="bi bi-clock"></i> '
                + parts.join(' – ') + (c.due.overdue ? ' · overdue' : '') + '</span>';
        } else if (c.start) {
            due.innerHTML = '<span class="due-pill"><i class="bi bi-clock"></i> Start ' + esc(mdy(c.start)) + '</span>';
        } else { due.innerHTML = ''; }

        renderLabelMenu(); renderMemberMenu(); renderDateMenu(); renderChecklists(); renderAttachments(); renderFeed();
    }

    function renderLabelMenu() { labelListView(); }

    function toggleCardLabel(id) {
        api('POST', '/cards/' + current.id + '/labels', { label_id: id }).then(applyUpdateFrom).catch(toast);
    }

    // List view: search + colored label rows (checkbox + name + edit) + create button.
    function labelListView() {
        var attached = {}; (current.labels || []).forEach(function (l) { attached[l.id] = true; });
        var rows = (current.board_labels || []).map(function (l) {
            return '<div class="lbl-row" data-name="' + esc((l.name || '').toLowerCase()) + '">' +
                '<input class="form-check-input cm-lbl" type="checkbox" data-id="' + l.id + '" ' + (attached[l.id] ? 'checked' : '') + '>' +
                '<span class="lbl-bar" data-id="' + l.id + '" style="background:' + esc(l.color) + '">' + esc(l.name || '') + '</span>' +
                '<button class="lbl-edit" data-id="' + l.id + '" title="Edit label"><i class="bi bi-pencil"></i></button>' +
            '</div>';
        }).join('');
        g('cmLabelMenu').innerHTML =
            '<input type="text" class="form-control form-control-sm mb-2" id="cmLabelSearch" placeholder="Search labels…">' +
            '<div class="lbl-section-title">Labels</div>' +
            '<div class="lbl-list" id="cmLabelList">' + (rows || '<div class="text-muted small px-1 py-2">No labels yet.</div>') + '</div>' +
            '<button type="button" class="btn btn-light btn-sm w-100 lbl-create-btn">Create a new label</button>';

        g('cmLabelSearch').addEventListener('input', function () {
            var q = this.value.toLowerCase();
            Array.prototype.forEach.call(g('cmLabelList').querySelectorAll('.lbl-row'), function (r) {
                r.style.display = r.dataset.name.indexOf(q) !== -1 ? '' : 'none';
            });
        });
        Array.prototype.forEach.call(g('cmLabelMenu').querySelectorAll('.cm-lbl'), function (cb) {
            cb.addEventListener('change', function () { toggleCardLabel(+cb.dataset.id); });
        });
        Array.prototype.forEach.call(g('cmLabelMenu').querySelectorAll('.lbl-bar'), function (bar) {
            bar.addEventListener('click', function () { toggleCardLabel(+bar.dataset.id); });
        });
        Array.prototype.forEach.call(g('cmLabelMenu').querySelectorAll('.lbl-edit'), function (b) {
            b.addEventListener('click', function () {
                labelFormView((current.board_labels || []).find(function (x) { return x.id === +b.dataset.id; }));
            });
        });
        g('cmLabelMenu').querySelector('.lbl-create-btn').addEventListener('click', function () { labelFormView(null); });
    }

    // Create / edit form: name, live preview, colour palette, save (+ delete when editing).
    function labelFormView(label) {
        var editing = !!label;
        var chosen = label ? label.color : LABEL_COLORS[0];
        var swatches = LABEL_COLORS.map(function (hex) {
            return '<div class="lbl-swatch' + (hex === chosen ? ' sel' : '') + '" data-color="' + hex + '" style="background:' + hex + '"></div>';
        }).join('');
        g('cmLabelMenu').innerHTML =
            '<div class="lbl-head"><button type="button" class="lbl-back" title="Back"><i class="bi bi-arrow-left"></i></button>' +
                '<strong>' + (editing ? 'Edit label' : 'Create label') + '</strong></div>' +
            '<div class="lbl-preview" id="lblPreview" style="background:' + chosen + '">' + esc(label ? (label.name || '') : 'Preview') + '</div>' +
            '<input type="text" class="form-control form-control-sm mb-2" id="lblName" placeholder="Label name" value="' + esc(label ? (label.name || '') : '') + '">' +
            '<div class="lbl-swatches" id="lblSwatches">' + swatches + '</div>' +
            (editing
                ? '<div class="d-flex gap-2"><button type="button" class="btn btn-primary btn-sm flex-grow-1" id="lblSave">Save</button>' +
                  '<button type="button" class="btn btn-outline-danger btn-sm" id="lblDelete" title="Delete label"><i class="bi bi-trash"></i></button></div>'
                : '<button type="button" class="btn btn-primary btn-sm w-100" id="lblSave">Create</button>');

        var preview = g('lblPreview'), nameInput = g('lblName');
        function paint() { preview.style.background = chosen; preview.textContent = nameInput.value || 'Preview'; }
        nameInput.addEventListener('input', paint);
        Array.prototype.forEach.call(g('lblSwatches').querySelectorAll('.lbl-swatch'), function (s) {
            s.addEventListener('click', function () {
                chosen = s.dataset.color;
                Array.prototype.forEach.call(g('lblSwatches').querySelectorAll('.lbl-swatch'), function (x) { x.classList.toggle('sel', x === s); });
                paint();
            });
        });
        g('cmLabelMenu').querySelector('.lbl-back').addEventListener('click', labelListView);
        g('lblSave').addEventListener('click', function () {
            var payload = { name: nameInput.value.trim(), color: chosen };
            var p = editing ? api('PATCH', '/labels/' + label.id, payload) : api('POST', '/boards/' + board.id + '/labels', payload);
            p.then(refreshCard).catch(toast);
        });
        if (editing) {
            g('lblDelete').addEventListener('click', function () {
                askConfirm({ title: 'Delete label', body: 'Delete this label? It will be removed from every card on this board.', ok: 'Delete' }, function () {
                    api('DELETE', '/labels/' + label.id).then(refreshCard).catch(toast);
                });
            });
        }
    }

    function renderMemberMenu() {
        var c = current;
        var on = {}; (c.members || []).forEach(function (m) { on[m.id] = true; });
        g('cmMemberMenu').innerHTML = (c.board_members || []).map(function (m) {
            return '<div class="form-check d-flex align-items-center gap-2 mb-1">' +
                '<input class="form-check-input cm-mbr" type="checkbox" data-id="' + m.id + '" ' + (on[m.id] ? 'checked' : '') + '>' +
                '<span class="avatar-xs">' + esc(m.initials) + '</span><span>' + esc(m.name) + '</span></div>';
        }).join('') || '<div class="text-muted small">No workspace members.</div>';
        Array.prototype.forEach.call(g('cmMemberMenu').querySelectorAll('.cm-mbr'), function (cb) {
            cb.addEventListener('change', function () {
                api('POST', '/cards/' + current.id + '/members', { user_id: +cb.dataset.id }).then(applyUpdateFrom).catch(toast);
            });
        });
    }

    // ---- Dates popover (Trello-style calendar) -----------------------------
    var dp = null; // { start:'YYYY-MM-DD'|null, due:'YYYY-MM-DD'|null, time:'HH:MM', active, view:Date }

    function pad2(n) { return (n < 10 ? '0' : '') + n; }
    function ymd(d) { return d.getFullYear() + '-' + pad2(d.getMonth() + 1) + '-' + pad2(d.getDate()); }
    function parseYmd(s) { var p = s.split('-'); return new Date(+p[0], +p[1] - 1, +p[2]); }
    function mdy(s) { if (!s) return ''; var p = s.split('-'); return (+p[1]) + '/' + (+p[2]) + '/' + p[0]; }
    function todayStr() { return ymd(new Date()); }

    function renderDateMenu() {
        var dueIso = current.due ? current.due.iso : null; // 'YYYY-MM-DDTHH:MM'
        dp = {
            start: current.start || null,
            due: dueIso ? dueIso.slice(0, 10) : null,
            time: dueIso ? dueIso.slice(11, 16) : '09:00',
            active: 'due',
            view: parseYmd((dueIso ? dueIso.slice(0, 10) : null) || current.start || todayStr())
        };
        dp.view = new Date(dp.view.getFullYear(), dp.view.getMonth(), 1);
        paintDateMenu();
    }

    function paintDateMenu() {
        var months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        var view = dp.view;
        var gridStart = new Date(view.getFullYear(), view.getMonth(), 1);
        gridStart.setDate(1 - gridStart.getDay()); // back to the Sunday
        var dows = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].map(function (d) { return '<div class="cal-dow">' + d + '</div>'; }).join('');
        var cells = '';
        for (var i = 0; i < 42; i++) {
            var d = new Date(gridStart); d.setDate(gridStart.getDate() + i);
            var s = ymd(d), cls = 'cal-day';
            if (d.getMonth() !== view.getMonth()) cls += ' other';
            if (s === todayStr()) cls += ' today';
            if (dp.start && dp.due && s > dp.start && s < dp.due) cls += ' in-range';
            if (dp.start && s === dp.start) cls += ' sel-start';
            if (dp.due && s === dp.due) cls += ' sel-due';
            cells += '<div class="' + cls + '" data-date="' + s + '">' + d.getDate() + '</div>';
        }
        g('cmDateMenu').innerHTML =
            '<div class="cal-head">' +
                '<button type="button" class="cal-nav" data-nav="-12" title="Previous year">&laquo;</button>' +
                '<button type="button" class="cal-nav" data-nav="-1" title="Previous month">&lsaquo;</button>' +
                '<div class="cal-title">' + months[view.getMonth()] + ' ' + view.getFullYear() + '</div>' +
                '<button type="button" class="cal-nav" data-nav="1" title="Next month">&rsaquo;</button>' +
                '<button type="button" class="cal-nav" data-nav="12" title="Next year">&raquo;</button>' +
            '</div>' +
            '<div class="cal-grid">' + dows + '</div>' +
            '<div class="cal-grid" id="cmCalGrid">' + cells + '</div>' +
            '<div class="df-label">Start date</div>' +
            '<div class="date-field' + (dp.active === 'start' ? ' active' : '') + '" data-field="start">' +
                '<input type="checkbox" class="form-check-input" id="dpStartChk" ' + (dp.start ? 'checked' : '') + '>' +
                '<input type="text" class="form-control form-control-sm" id="dpStartTxt" readonly placeholder="M/D/YYYY" value="' + mdy(dp.start) + '">' +
            '</div>' +
            '<div class="df-label">Due date</div>' +
            '<div class="date-field' + (dp.active === 'due' ? ' active' : '') + '" data-field="due">' +
                '<input type="checkbox" class="form-check-input" id="dpDueChk" ' + (dp.due ? 'checked' : '') + '>' +
                '<input type="text" class="form-control form-control-sm" id="dpDueTxt" readonly placeholder="M/D/YYYY" value="' + mdy(dp.due) + '">' +
                '<input type="time" class="form-control form-control-sm dp-time" id="dpDueTime" value="' + dp.time + '">' +
            '</div>' +
            '<div class="d-grid gap-2 mt-3">' +
                '<button type="button" class="btn btn-primary btn-sm" id="dpSave">Save</button>' +
                '<button type="button" class="btn btn-light btn-sm" id="dpRemove">Remove</button>' +
            '</div>';

        Array.prototype.forEach.call(g('cmDateMenu').querySelectorAll('.cal-nav'), function (b) {
            b.addEventListener('click', function () {
                dp.view = new Date(dp.view.getFullYear(), dp.view.getMonth() + (+b.dataset.nav), 1);
                paintDateMenu();
            });
        });
        Array.prototype.forEach.call(g('cmCalGrid').querySelectorAll('.cal-day'), function (cell) {
            cell.addEventListener('click', function () {
                syncDpTime();
                if (dp.active === 'start') dp.start = cell.dataset.date; else dp.due = cell.dataset.date;
                paintDateMenu();
            });
        });
        // Focusing a field makes the calendar edit it (no full repaint, so the
        // native time picker isn't interrupted).
        Array.prototype.forEach.call(g('cmDateMenu').querySelectorAll('.date-field'), function (f) {
            f.addEventListener('click', function () {
                dp.active = f.dataset.field;
                Array.prototype.forEach.call(g('cmDateMenu').querySelectorAll('.date-field'), function (x) { x.classList.toggle('active', x === f); });
            });
        });
        g('dpStartChk').addEventListener('change', function () {
            dp.start = this.checked ? (dp.start || todayStr()) : null;
            dp.active = 'start'; paintDateMenu();
        });
        g('dpDueChk').addEventListener('change', function () {
            dp.due = this.checked ? (dp.due || todayStr()) : null;
            dp.active = 'due'; paintDateMenu();
        });
        g('dpDueTime').addEventListener('change', syncDpTime);
        g('dpSave').addEventListener('click', function () {
            syncDpTime();
            api('PATCH', '/cards/' + current.id, {
                start_date: dp.start || null,
                due_date: dp.due ? (dp.due + 'T' + dp.time) : null
            }).then(function (d) { applyUpdateFrom(d); closeDropdown('cmDateMenu'); }).catch(toast);
        });
        g('dpRemove').addEventListener('click', function () {
            api('PATCH', '/cards/' + current.id, { start_date: null, due_date: null })
                .then(function (d) { applyUpdateFrom(d); closeDropdown('cmDateMenu'); }).catch(toast);
        });
    }

    function syncDpTime() { var t = g('dpDueTime'); if (t) dp.time = t.value || dp.time; }

    function closeDropdown(menuId) {
        var menu = document.getElementById(menuId);
        var toggle = menu ? menu.parentElement.querySelector('[data-bs-toggle="dropdown"]') : null;
        if (toggle && window.bootstrap) bootstrap.Dropdown.getOrCreateInstance(toggle).hide();
    }

    function renderChecklists() {
        var wrap = g('cmChecklists'); wrap.innerHTML = '';
        (current.checklists || []).forEach(function (cl) {
            var pct = cl.progress.total ? Math.round(cl.progress.done / cl.progress.total * 100) : 0;
            var box = h('<div class="mb-3"></div>');
            box.innerHTML =
                '<div class="d-flex align-items-center justify-content-between">' +
                '<strong class="small">' + esc(cl.title) + '</strong>' +
                '<button class="btn btn-sm btn-link text-danger text-decoration-none cm-del-cl" data-id="' + cl.id + '">Delete</button></div>' +
                '<div class="d-flex align-items-center gap-2 my-1"><small class="text-muted">' + cl.progress.done + '/' + cl.progress.total +
                '</small><div class="chk-progress flex-grow-1"><span style="width:' + pct + '%"></span></div></div>';
            (cl.items || []).forEach(function (it) {
                var row = h('<div class="chk-item' + (it.is_done ? ' done' : '') + '"></div>');
                row.innerHTML = '<input class="form-check-input cm-item" type="checkbox" data-id="' + it.id + '" ' + (it.is_done ? 'checked' : '') + '>' +
                    '<label class="flex-grow-1 small mb-0">' + linkify(it.content) + '</label>' +
                    '<button class="btn btn-sm btn-link text-muted p-0 cm-del-item" data-id="' + it.id + '"><i class="bi bi-x"></i></button>';
                box.appendChild(row);
            });
            var add = h('<div class="d-flex gap-1 mt-1"><input class="form-control form-control-sm cm-new-item" placeholder="Add an item…"><button class="btn btn-sm btn-light cm-add-item" data-id="' + cl.id + '">Add</button></div>');
            box.appendChild(add);
            wrap.appendChild(box);
        });
        Array.prototype.forEach.call(wrap.querySelectorAll('.cm-item'), function (cb) {
            cb.addEventListener('change', function () {
                api('PATCH', '/checklist-items/' + cb.dataset.id, { is_done: cb.checked }).then(applyUpdateFrom).catch(toast);
            });
        });
        Array.prototype.forEach.call(wrap.querySelectorAll('.cm-del-item'), function (b) {
            b.addEventListener('click', function () { api('DELETE', '/checklist-items/' + b.dataset.id).then(applyUpdateFrom).catch(toast); });
        });
        Array.prototype.forEach.call(wrap.querySelectorAll('.cm-del-cl'), function (b) {
            b.addEventListener('click', function () { askConfirm({ title: 'Delete checklist', body: 'Delete this checklist and its items?', ok: 'Delete' }, function () { api('DELETE', '/checklists/' + b.dataset.id).then(applyUpdateFrom).catch(toast); }); });
        });
        Array.prototype.forEach.call(wrap.querySelectorAll('.cm-add-item'), function (b) {
            var input = b.previousElementSibling;
            function addItem() { var v = input.value.trim(); if (!v) return; api('POST', '/checklists/' + b.dataset.id + '/items', { content: v }).then(applyUpdateFrom).catch(toast); }
            b.addEventListener('click', addItem);
            input.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); addItem(); } });
        });
    }

    var EXT_COLORS = { PDF: '#B5495B', DOC: '#5E7C99', DOCX: '#5E7C99', XLS: '#4E7C59', XLSX: '#4E7C59', CSV: '#4E7C59', TXT: '#8A7F8E', ZIP: '#CD8B3C' };
    function fileExt(name) { var m = /\.([a-z0-9]+)$/i.exec(name || ''); return m ? m[1].toUpperCase() : 'FILE'; }
    function humanSize(bytes) {
        if (!bytes) return '';
        var u = ['B', 'KB', 'MB', 'GB'], i = 0, n = bytes;
        while (n >= 1024 && i < u.length - 1) { n /= 1024; i++; }
        return (i === 0 || n >= 10 ? Math.round(n) : n.toFixed(1)) + ' ' + u[i];
    }

    function renderAttachments() {
        var rows = (current.attachments || []).map(function (a) {
            var ext = fileExt(a.name);
            var preview = a.image
                ? '<img src="' + esc(a.url) + '" alt="">'
                : '<span class="att-ext" style="background:' + (EXT_COLORS[ext] || 'var(--ceo-aubergine)') + '">' + esc(ext) + '</span>';
            var sub = humanSize(a.size); sub = (sub ? esc(sub) + ' · ' : '') + esc(ext);
            return '<div class="att-row">' +
                '<a class="att-preview" href="' + esc(a.url) + '" target="_blank" rel="noopener">' + preview + '</a>' +
                '<div class="att-meta">' +
                    '<a class="att-name" href="' + esc(a.url) + '" target="_blank" rel="noopener" title="' + esc(a.name) + '">' + esc(a.name) + '</a>' +
                    '<div class="att-sub">' + sub + '</div>' +
                '</div>' +
                '<div class="att-actions">' +
                    '<a class="att-btn" href="' + esc(a.url) + '" target="_blank" rel="noopener" title="Open"><i class="bi bi-box-arrow-up-right"></i></a>' +
                    '<button type="button" class="att-btn del cm-del-att" data-id="' + a.id + '" title="Remove"><i class="bi bi-trash"></i></button>' +
                '</div>' +
            '</div>';
        }).join('');
        g('cmAttachments').innerHTML = rows || '<div class="text-muted small">No attachments yet.</div>';
        Array.prototype.forEach.call(g('cmAttachments').querySelectorAll('.cm-del-att'), function (b) {
            b.addEventListener('click', function () {
                askConfirm({ title: 'Remove attachment', body: 'Remove this attachment?', ok: 'Remove' }, function () {
                    api('DELETE', '/card-attachments/' + b.dataset.id).then(applyUpdateFrom).catch(toast);
                });
            });
        });
    }

    function renderFeed() {
        var items = [];
        (current.comments || []).forEach(function (cm) {
            items.push('<div class="activity-item"><span class="avatar-xs">' + esc(cm.user ? cm.user.initials : '?') + '</span>' +
                '<div class="flex-grow-1"><div><strong class="small">' + esc(cm.user ? cm.user.name : 'Unknown') + '</strong> ' +
                '<span class="text-muted" style="font-size:.72rem">' + esc(cm.created) + '</span></div>' +
                '<div class="small cm-comment-body">' + linkify(cm.body) + '</div></div>' +
                ((cm.user && cm.user.id === board.currentUserId) ? '<button class="btn btn-sm btn-link text-muted p-0 cm-del-comment" data-id="' + cm.id + '"><i class="bi bi-x"></i></button>' : '') +
                '</div>');
        });
        (current.activities || []).forEach(function (a) {
            var txt = activityText(a);
            items.push('<div class="activity-item"><span class="avatar-xs" style="background:var(--bs-secondary-color)">' +
                esc(a.user ? a.user.initials : '·') + '</span><div class="small text-muted flex-grow-1">' +
                '<strong>' + esc(a.user ? a.user.name : 'Someone') + '</strong> ' + txt +
                ' <span style="font-size:.72rem">· ' + esc(a.created) + '</span></div></div>');
        });
        g('cmFeed').innerHTML = items.join('') || '<div class="text-muted small">No activity yet.</div>';
        Array.prototype.forEach.call(g('cmFeed').querySelectorAll('.cm-del-comment'), function (b) {
            b.addEventListener('click', function () { api('DELETE', '/comments/' + b.dataset.id).then(applyUpdateFrom).catch(toast); });
        });
    }

    function activityText(a) {
        if (a.action === 'created') return 'added this card to ' + esc(a.meta && a.meta.list ? a.meta.list : 'the board');
        if (a.action === 'moved') return 'moved this card from ' + esc(a.meta.from) + ' to ' + esc(a.meta.to);
        return esc(a.action);
    }

    function applyUpdateFrom(d) { if (d && d.card) applyUpdate(d.card); }
    function refreshCard() { return api('GET', '/cards/' + current.id).then(applyUpdateFrom); }

    // static modal controls (wired once)
    g('cmTitle').addEventListener('blur', function () {
        var v = g('cmTitle').textContent.trim();
        if (!v || v === current.title) { g('cmTitle').textContent = current.title; return; }
        api('PATCH', '/cards/' + current.id, { title: v }).then(applyUpdateFrom).catch(toast);
    });
    g('cmTitle').addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); g('cmTitle').blur(); } });

    function enterDescEdit() {
        g('cmDescDisplay').classList.add('d-none');
        g('cmDescription').classList.remove('d-none');
        g('cmDescEditBtns').classList.remove('d-none');
        g('cmDescription').focus();
    }
    function exitDescEdit() {
        g('cmDescription').classList.add('d-none');
        g('cmDescEditBtns').classList.add('d-none');
        g('cmDescDisplay').classList.remove('d-none');
    }
    // Click the rendered description to edit — but let links open normally.
    g('cmDescDisplay').addEventListener('click', function (e) {
        if (e.target.closest('a')) return;
        enterDescEdit();
    });
    g('cmCancelDesc').addEventListener('click', function () { g('cmDescription').value = current.description || ''; exitDescEdit(); });
    g('cmSaveDesc').addEventListener('click', function () {
        api('PATCH', '/cards/' + current.id, { description: g('cmDescription').value }).then(applyUpdateFrom).catch(toast);
    });

    g('cmComplete').addEventListener('click', function () {
        api('PATCH', '/cards/' + current.id, { completed: !current.completed }).then(applyUpdateFrom).catch(toast);
    });

    g('cmPostComment').addEventListener('click', function () {
        var v = g('cmCommentBody').value.trim(); if (!v) return;
        api('POST', '/cards/' + current.id + '/comments', { body: v }).then(function (d) { g('cmCommentBody').value = ''; applyUpdateFrom(d); }).catch(toast);
    });

    function addChecklistFromInput() {
        var title = g('cmChecklistTitle').value.trim() || 'Checklist';
        api('POST', '/cards/' + current.id + '/checklists', { title: title }).then(function (d) {
            applyUpdateFrom(d);
            g('cmChecklistTitle').value = 'Checklist';
            closeDropdown('cmChecklistMenu');
        }).catch(toast);
    }
    g('cmAddChecklistBtn').addEventListener('click', addChecklistFromInput);
    g('cmChecklistTitle').addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); addChecklistFromInput(); } });
    // Focus + select the title when the Checklist popover opens.
    var chkMenu = document.getElementById('cmChecklistMenu');
    if (chkMenu && chkMenu.parentElement) {
        chkMenu.parentElement.addEventListener('shown.bs.dropdown', function () {
            var i = g('cmChecklistTitle'); if (i) { i.focus(); i.select(); }
        });
    }

    g('cmAttachBtn').addEventListener('click', function () { g('cmFileInput').click(); });
    g('cmFileInput').addEventListener('change', function () {
        var files = Array.prototype.slice.call(g('cmFileInput').files);
        g('cmFileInput').value = '';
        if (!files.length) return;
        if (board.uploadsDirect) uploadDirect(files); else uploadMultipart(files);
    });

    // Local fallback: POST all files through the app as multipart.
    function uploadMultipart(files) {
        var fd = new FormData();
        files.forEach(function (f) { fd.append('files[]', f); });
        setUploadProgress('Uploading…', null);
        api('POST', '/cards/' + current.id + '/attachments', fd, true)
            .then(function (d) { clearUploadProgress(); applyUpdateFrom(d); })
            .catch(function (e) { clearUploadProgress(); toast(e); });
    }

    // R2: presign → PUT straight to R2 (with progress) → record. One at a time.
    function uploadDirect(files) {
        var maxBytes = (board.maxUploadMb || 500) * 1024 * 1024, cardId = current.id;
        (function next(i) {
            if (i >= files.length) { clearUploadProgress(); return; }
            var file = files[i];
            if (file.size > maxBytes) { toast('“' + file.name + '” is larger than ' + board.maxUploadMb + ' MB.'); return next(i + 1); }
            setUploadProgress(file.name, 0);
            api('POST', '/cards/' + cardId + '/attachments/presign', { name: file.name, size: file.size })
                .then(function (p) {
                    return xhrPut(p.url, file, p.headers, function (pct) { setUploadProgress(file.name, pct); })
                        .then(function () {
                            return api('POST', '/cards/' + cardId + '/attachments/record', {
                                key: p.key, name: file.name, size: file.size, mime: file.type || null
                            });
                        });
                })
                .then(function (d) { applyUpdateFrom(d); next(i + 1); })
                .catch(function (e) { clearUploadProgress(); toast(e); });
        })(0);
    }

    // PUT a file to a presigned URL with upload-progress callback.
    function xhrPut(url, file, headers, onProgress) {
        return new Promise(function (resolve, reject) {
            var xhr = new XMLHttpRequest();
            xhr.open('PUT', url, true);
            Object.keys(headers || {}).forEach(function (k) {
                if (/^(host|content-length)$/i.test(k)) return; // browser-managed / forbidden
                var v = headers[k]; if (Array.isArray(v)) v = v.join(',');
                try { xhr.setRequestHeader(k, v); } catch (e) {}
            });
            xhr.upload.onprogress = function (e) { if (e.lengthComputable && onProgress) onProgress(Math.round(e.loaded / e.total * 100)); };
            xhr.onload = function () { (xhr.status >= 200 && xhr.status < 300) ? resolve() : reject('Upload failed (' + xhr.status + ')'); };
            xhr.onerror = function () { reject('Upload failed — check the bucket CORS policy.'); };
            xhr.send(file);
        });
    }

    function setUploadProgress(name, pct) {
        var el = g('cmUploadProgress');
        el.classList.remove('d-none');
        el.innerHTML = '<div class="up-name text-truncate">' + esc(name) + (pct === null ? '' : ' · ' + pct + '%') + '</div>' +
            '<div class="up-bar"><span style="width:' + (pct === null ? 100 : pct) + '%"></span></div>';
    }
    function clearUploadProgress() { var el = g('cmUploadProgress'); el.classList.add('d-none'); el.innerHTML = ''; }

    g('cmDelete').addEventListener('click', function () {
        askConfirm({ title: 'Delete card', body: 'Delete this card and all its contents?', ok: 'Delete' }, function () {
            api('DELETE', '/cards/' + current.id).then(function () {
                var tile = canvas.querySelector('.card-tile[data-card-id="' + current.id + '"]');
                var listId = tile ? +tile.closest('.list-col').dataset.listId : null;
                if (tile) tile.remove();
                if (listId) bumpCount(listId);
                cardModalInstance().hide();
            }).catch(toast);
        });
    });

    // ---- delete board (top ⋯ menu) -----------------------------------------
    var delBoardBtn = document.getElementById('deleteBoardBtn');
    if (delBoardBtn) {
        delBoardBtn.addEventListener('click', function () {
            askConfirm({ title: 'Delete board', body: 'Delete this board and everything on it? This cannot be undone.', ok: 'Delete board' }, function () {
                document.getElementById('deleteBoardForm').submit();
            });
        });
    }

    // ---- board title rename ------------------------------------------------
    var boardTitle = document.getElementById('boardTitle');
    boardTitle.addEventListener('click', function () {
        boardTitle.setAttribute('contenteditable', 'true'); boardTitle.focus();
    });
    boardTitle.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); boardTitle.blur(); } });
    boardTitle.addEventListener('blur', function () {
        boardTitle.removeAttribute('contenteditable');
        var v = boardTitle.textContent.trim();
        if (!v || v === board.name) { boardTitle.textContent = board.name; return; }
        api('PATCH', '/boards/' + board.id, { name: v }).then(function () { board.name = v; document.title = v + ' | CEO Dashboard'; }).catch(function (e) { toast(e); boardTitle.textContent = board.name; });
    });

    renderBoard();
})();
