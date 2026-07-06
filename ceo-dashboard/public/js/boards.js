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

    // ---- tiny helpers ------------------------------------------------------
    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
    function h(html) { var t = document.createElement('template'); t.innerHTML = html.trim(); return t.content.firstElementChild; }

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
            '<button class="list-del" title="Delete list"><i class="bi bi-trash"></i></button>';
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
        // delete list
        head.querySelector('.list-del').addEventListener('click', function () {
            if (!confirm('Delete "' + list.name + '" and its cards?')) return;
            api('DELETE', '/lists/' + list.id).then(function () {
                board.lists = board.lists.filter(function (l) { return l.id !== list.id; });
                col.remove();
            }).catch(toast);
        });
        return col;
    }

    function renderCard(card) {
        var li = h('<li class="card-tile" data-card-id="' + card.id + '"></li>');
        var html = '';
        if (card.labels && card.labels.length) {
            html += '<div class="card-labels">' + card.labels.map(function (l) {
                return '<span class="card-label-chip" title="' + esc(l.name || '') + '" style="background:' + esc(l.color) + '"></span>';
            }).join('') + '</div>';
        }
        html += '<div class="card-tile-title">' + esc(card.title) + '</div>';

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
        var btn = h('<button class="add-btn"><i class="bi bi-plus-lg"></i> Add a card</button>');
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

    function renderAddList() {
        var wrap = h('<div class="list-add"></div>');
        var btn = h('<button class="add-btn"><i class="bi bi-plus-lg"></i> Add another list</button>');
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
            draggable: '.list-col', handle: '.list-head', filter: '.list-add', animation: 150,
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
    var cardModal = new bootstrap.Modal('#cardModal');
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
            cardModal.show();
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
        g('cmDescription').value = c.description || '';
        g('cmSaveDesc').classList.add('d-none'); g('cmCancelDesc').classList.add('d-none');

        // labels row
        g('cmLabelsRow').innerHTML = (c.labels || []).map(function (l) {
            return '<span class="cm-label-chip" style="background:' + esc(l.color) + '">' + esc(l.name || '') + '</span>';
        }).join('');

        // due row
        var due = g('cmDueRow');
        if (c.due) {
            var cls = c.due.completed ? 'done' : (c.due.overdue ? 'overdue' : '');
            due.innerHTML = '<span class="due-pill ' + cls + '"><i class="bi bi-clock"></i> Due ' + esc(c.due.label) +
                (c.due.completed ? ' · complete' : (c.due.overdue ? ' · overdue' : '')) + '</span>';
            g('cmDueInput').value = c.due.iso || '';
            g('cmCompleted').checked = !!c.due.completed;
        } else { due.innerHTML = ''; g('cmDueInput').value = ''; g('cmCompleted').checked = false; }

        renderLabelMenu(); renderMemberMenu(); renderChecklists(); renderAttachments(); renderFeed();
    }

    function renderLabelMenu() {
        var c = current;
        var attached = {}; (c.labels || []).forEach(function (l) { attached[l.id] = true; });
        var html = (c.board_labels || []).map(function (l) {
            return '<div class="form-check d-flex align-items-center gap-2 mb-1">' +
                '<input class="form-check-input cm-lbl" type="checkbox" data-id="' + l.id + '" ' + (attached[l.id] ? 'checked' : '') + '>' +
                '<span class="cm-label-chip flex-grow-1" style="background:' + esc(l.color) + '">' + esc(l.name || '') + '</span></div>';
        }).join('');
        html += '<hr class="my-2"><div class="d-flex gap-1">' +
            '<input id="cmNewLabelName" class="form-control form-control-sm" placeholder="New label">' +
            '<input id="cmNewLabelColor" type="color" class="form-control form-control-sm form-control-color" value="#4E7C59">' +
            '<button id="cmAddLabel" class="btn btn-sm btn-primary">Add</button></div>';
        g('cmLabelMenu').innerHTML = html;
        Array.prototype.forEach.call(g('cmLabelMenu').querySelectorAll('.cm-lbl'), function (cb) {
            cb.addEventListener('change', function () {
                api('POST', '/cards/' + current.id + '/labels', { label_id: +cb.dataset.id }).then(applyUpdateFrom).catch(toast);
            });
        });
        g('cmAddLabel').addEventListener('click', function () {
            var name = g('cmNewLabelName').value.trim();
            var color = g('cmNewLabelColor').value;
            api('POST', '/boards/' + board.id + '/labels', { name: name, color: color }).then(function (d) {
                current.board_labels.push(d.label);
                // attach immediately
                return api('POST', '/cards/' + current.id + '/labels', { label_id: d.label.id }).then(applyUpdateFrom);
            }).catch(toast);
        });
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
                    '<label class="flex-grow-1 small mb-0">' + esc(it.content) + '</label>' +
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
            b.addEventListener('click', function () { if (confirm('Delete this checklist?')) api('DELETE', '/checklists/' + b.dataset.id).then(applyUpdateFrom).catch(toast); });
        });
        Array.prototype.forEach.call(wrap.querySelectorAll('.cm-add-item'), function (b) {
            var input = b.previousElementSibling;
            function addItem() { var v = input.value.trim(); if (!v) return; api('POST', '/checklists/' + b.dataset.id + '/items', { content: v }).then(applyUpdateFrom).catch(toast); }
            b.addEventListener('click', addItem);
            input.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); addItem(); } });
        });
    }

    function renderAttachments() {
        g('cmAttachments').innerHTML = (current.attachments || []).map(function (a) {
            var thumb = a.image
                ? '<img src="' + esc(a.url) + '" style="width:86px;height:64px;object-fit:cover;border-radius:8px;border:1px solid var(--ceo-border)">'
                : '<div style="width:86px;height:64px;display:flex;align-items:center;justify-content:center;border:1px solid var(--ceo-border);border-radius:8px;background:var(--bs-tertiary-bg)"><i class="bi bi-file-earmark"></i></div>';
            return '<div style="width:86px">' +
                '<a href="' + esc(a.url) + '" target="_blank" rel="noopener">' + thumb + '</a>' +
                '<div class="d-flex justify-content-between align-items-center"><small class="text-truncate" style="max-width:64px">' + esc(a.name) + '</small>' +
                '<button class="btn btn-sm btn-link text-danger p-0 cm-del-att" data-id="' + a.id + '"><i class="bi bi-x"></i></button></div></div>';
        }).join('');
        Array.prototype.forEach.call(g('cmAttachments').querySelectorAll('.cm-del-att'), function (b) {
            b.addEventListener('click', function () { if (confirm('Remove attachment?')) api('DELETE', '/card-attachments/' + b.dataset.id).then(applyUpdateFrom).catch(toast); });
        });
    }

    function renderFeed() {
        var items = [];
        (current.comments || []).forEach(function (cm) {
            items.push('<div class="activity-item"><span class="avatar-xs">' + esc(cm.user ? cm.user.initials : '?') + '</span>' +
                '<div class="flex-grow-1"><div><strong class="small">' + esc(cm.user ? cm.user.name : 'Unknown') + '</strong> ' +
                '<span class="text-muted" style="font-size:.72rem">' + esc(cm.created) + '</span></div>' +
                '<div class="small">' + esc(cm.body) + '</div></div>' +
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

    // static modal controls (wired once)
    g('cmTitle').addEventListener('blur', function () {
        var v = g('cmTitle').textContent.trim();
        if (!v || v === current.title) { g('cmTitle').textContent = current.title; return; }
        api('PATCH', '/cards/' + current.id, { title: v }).then(applyUpdateFrom).catch(toast);
    });
    g('cmTitle').addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); g('cmTitle').blur(); } });

    g('cmDescription').addEventListener('input', function () {
        var changed = g('cmDescription').value !== (current.description || '');
        g('cmSaveDesc').classList.toggle('d-none', !changed);
        g('cmCancelDesc').classList.toggle('d-none', !changed);
    });
    g('cmCancelDesc').addEventListener('click', function () { g('cmDescription').value = current.description || ''; g('cmDescription').dispatchEvent(new Event('input')); });
    g('cmSaveDesc').addEventListener('click', function () {
        api('PATCH', '/cards/' + current.id, { description: g('cmDescription').value }).then(applyUpdateFrom).catch(toast);
    });

    g('cmSaveDue').addEventListener('click', function () {
        api('PATCH', '/cards/' + current.id, { due_date: g('cmDueInput').value || null, completed: g('cmCompleted').checked }).then(applyUpdateFrom).catch(toast);
    });
    g('cmClearDue').addEventListener('click', function () {
        api('PATCH', '/cards/' + current.id, { due_date: null, completed: false }).then(applyUpdateFrom).catch(toast);
    });

    g('cmPostComment').addEventListener('click', function () {
        var v = g('cmCommentBody').value.trim(); if (!v) return;
        api('POST', '/cards/' + current.id + '/comments', { body: v }).then(function (d) { g('cmCommentBody').value = ''; applyUpdateFrom(d); }).catch(toast);
    });

    g('cmAddChecklist').addEventListener('click', function () {
        api('POST', '/cards/' + current.id + '/checklists', {}).then(applyUpdateFrom).catch(toast);
    });

    g('cmAttachBtn').addEventListener('click', function () { g('cmFileInput').click(); });
    g('cmFileInput').addEventListener('change', function () {
        if (!g('cmFileInput').files.length) return;
        var fd = new FormData();
        Array.prototype.forEach.call(g('cmFileInput').files, function (f) { fd.append('files[]', f); });
        api('POST', '/cards/' + current.id + '/attachments', fd, true).then(function (d) { g('cmFileInput').value = ''; applyUpdateFrom(d); }).catch(toast);
    });

    g('cmDelete').addEventListener('click', function () {
        if (!confirm('Delete this card?')) return;
        api('DELETE', '/cards/' + current.id).then(function () {
            var tile = canvas.querySelector('.card-tile[data-card-id="' + current.id + '"]');
            var listId = tile ? +tile.closest('.list-col').dataset.listId : null;
            if (tile) tile.remove();
            if (listId) bumpCount(listId);
            cardModal.hide();
        }).catch(toast);
    });

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
