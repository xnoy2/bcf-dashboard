@extends('layouts.app')

@section('title', 'Calendar')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css">
<style>
    .fc { --fc-border-color: var(--ceo-border); --fc-today-bg-color: rgba(200,162,75,.10); font-size: .9rem; }
    .fc .fc-toolbar-title { font-family: var(--ceo-font-head); font-size: 1.25rem; }
    .fc .fc-button { background: var(--ceo-aubergine); border-color: var(--ceo-aubergine); text-transform: capitalize; box-shadow: none; }
    .fc .fc-button:hover { background: var(--ceo-aubergine-2); border-color: var(--ceo-aubergine-2); }
    .fc .fc-button-primary:not(:disabled).fc-button-active { background: var(--ceo-gold); border-color: var(--ceo-gold); color: #1f1726; }
    .fc-event { cursor: pointer; font-weight: 600; }
    .fc-day-today { font-weight: 700; }
    html[data-theme="dark"] .fc { --fc-page-bg-color: transparent; --fc-neutral-bg-color: #2A2139; color: #E7E1EE; }
    html[data-theme="dark"] .fc .fc-col-header-cell-cushion,
    html[data-theme="dark"] .fc .fc-daygrid-day-number { color: #C5BBD2; }
    /* Birthday → DOB reveal */
    #dobWrap { transition: opacity .25s ease, transform .25s ease; }
    #dobWrap.dob-hide { opacity: 0; transform: translateX(-12px); }
    /* Attachment thumbnails */
    .att-tile { position: relative; width: 86px; }
    .att-tile .att-thumb { width: 86px; height: 64px; object-fit: cover; border-radius: 8px; border: 1px solid var(--ceo-border); display: block; background: #f1eef5; }
    .att-tile .att-file { width: 86px; height: 64px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 2px; border: 1px solid var(--ceo-border); border-radius: 8px; background: var(--bs-tertiary-bg, #f1eef5); }
    .att-tile .att-name { display: block; max-width: 86px; margin-top: 3px; font-size: .68rem; line-height: 1.1; color: var(--bs-secondary-color, #6c757d); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .att-tile .att-del { position: absolute; top: -7px; right: -7px; width: 20px; height: 20px; line-height: 17px; text-align: center; border-radius: 50%; background: #B5495B; color: #fff; border: 2px solid #fff; font-size: 11px; cursor: pointer; text-decoration: none; }
    .att-tile .att-del:hover { background: #93313f; }
    /* Make the <form> wrapper participate in scrollable-modal flex so the body scrolls
       when the viewport is short instead of the modal overflowing off-screen. */
    #entryModal.modal-dialog-scrollable .modal-content,
    #entryModal .modal-dialog-scrollable .modal-content { overflow: hidden; }
    #entryModal .modal-content > form { display: flex; flex-direction: column; min-height: 0; max-height: 100%; overflow: hidden; }
    #entryModal .modal-body { overflow-y: auto; }
</style>
@endpush

@section('content')
<div class="app-content-header">
    <div class="container-fluid d-flex align-items-center flex-wrap gap-2">
        <h3 class="mb-0">Calendar <small class="text-muted">Jobs &amp; Appointments</small></h3>
        <button class="btn btn-sm btn-primary ms-auto" id="newEntryBtn"><i class="bi bi-plus-lg"></i> New Entry</button>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        {{-- KPIs --}}
        <div class="row g-3">
            <div class="col-6 col-lg-3"><a href="{{ route('calendar', ['from' => now()->toDateString(), 'to' => now()->addDays(7)->toDateString()]) }}" class="card-link">
                <div class="card text-bg-primary clickable-card"><div class="card-body">
                    <div class="fs-3 fw-bold">{{ $stats['upcoming'] }}</div><div class="small text-uppercase opacity-75">Upcoming (7 days)</div>
                </div></div></a></div>
            <div class="col-6 col-lg-3">
                <div class="card text-bg-danger"><div class="card-body">
                    <div class="fs-3 fw-bold">{{ $stats['overdue'] }}</div><div class="small text-uppercase opacity-75">Overdue / Action Needed</div>
                </div></div></div>
            <div class="col-6 col-lg-3"><a href="{{ route('calendar', ['status' => 'scheduled']) }}" class="card-link">
                <div class="card text-bg-info clickable-card"><div class="card-body">
                    <div class="fs-3 fw-bold">{{ $stats['scheduled'] }}</div><div class="small text-uppercase opacity-75">Scheduled</div>
                </div></div></a></div>
            <div class="col-6 col-lg-3"><a href="{{ route('calendar', ['status' => 'completed']) }}" class="card-link">
                <div class="card text-bg-success clickable-card"><div class="card-body">
                    <div class="fs-3 fw-bold">{{ $stats['completed'] }}</div><div class="small text-uppercase opacity-75">Completed</div>
                </div></div></a></div>
        </div>

        {{-- Calendar --}}
        <div class="card mt-3"><div class="card-body"><div id="calendar"></div></div></div>

        {{-- Filter + list --}}
        <div class="card mt-3">
            <div class="card-header">
                <form method="GET" action="{{ route('calendar') }}" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Search client / address</label>
                        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control form-control-sm" placeholder="Search…">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small mb-1">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All</option>
                            @foreach($statuses as $st)
                                <option value="{{ $st }}" @selected(($filters['status'] ?? '') === $st)>{{ ucfirst(str_replace('_',' ',$st)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small mb-1">Installer</label>
                        <input type="text" name="assigned" value="{{ $filters['assigned'] ?? '' }}" list="installerList" class="form-control form-control-sm" placeholder="Anyone">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small mb-1">From</label>
                        <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small mb-1">To</label>
                        <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-1 d-grid">
                        <button class="btn btn-sm btn-primary">Filter</button>
                    </div>
                </form>
            </div>
            <div class="card-body p-0" style="max-height:55vh; overflow:auto;">
                <table class="table table-striped table-hover mb-0">
                    <thead class="sticky-top bg-body">
                        <tr><th>Client</th><th>When</th><th>Status</th><th>Installer</th><th>Address</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse($entries as $e)
                            @php $sc = ['scheduled'=>'info','in_progress'=>'warning','completed'=>'success','cancelled'=>'secondary'][$e->status] ?? 'secondary'; @endphp
                            <tr class="{{ $e->isOverdue() ? 'table-danger' : '' }}">
                                <td>{{ $e->client_name }} @if($e->is_birthday)<span title="Birthday installation">🎂</span>@endif</td>
                                <td>
                                    {{ $e->start_date->format('d M Y') }}@if($e->end_date) → {{ $e->end_date->format('d M Y') }}@endif
                                    @if($e->isOverdue())<span class="badge text-bg-danger ms-1">overdue</span>@endif
                                </td>
                                <td><span class="badge text-bg-{{ $sc }} text-capitalize">{{ str_replace('_',' ',$e->status) }}</span></td>
                                <td>{{ $e->assigned_to ?: '—' }}</td>
                                <td><small>{{ \Illuminate\Support\Str::limit($e->address, 40) }}</small></td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-link p-0 edit-entry" data-id="{{ $e->id }}">edit</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">No entries. Click <strong>New Entry</strong> or a date on the calendar to add one.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<datalist id="installerList">
    @foreach($installers as $i)<option value="{{ $i }}">@endforeach
</datalist>

{{-- Create / Edit modal --}}
<div class="modal fade" id="entryModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <form id="entryForm" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
            <h5 class="modal-title" id="entryModalTitle">New Calendar Entry</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div id="formErrors" class="alert alert-danger d-none"></div>
            <div class="row g-2">
                <div class="col-md-4">
                    <label class="form-label small mb-1">Client Name <span class="text-danger">*</span></label>
                    <input type="text" name="client_name" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label small mb-1">Address <span class="text-danger">*</span></label>
                    <input type="text" name="address" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-1">Phone <span class="text-muted">(opt)</span></label>
                    <input type="text" name="phone" class="form-control form-control-sm">
                </div>

                <div class="col-6 col-md-3">
                    <label class="form-label small mb-1">Target Date <span class="text-danger">*</span></label>
                    <input type="date" name="start_date" id="f_start" class="form-control form-control-sm" required>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small mb-1">Est. Completion <span class="text-muted">(range)</span></label>
                    <input type="date" name="end_date" id="f_end" class="form-control form-control-sm">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        @foreach($statuses as $st)<option value="{{ $st }}">{{ ucfirst(str_replace('_',' ',$st)) }}</option>@endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small mb-1">Remind (days)</label>
                    <input type="number" name="reminder_days" class="form-control form-control-sm" value="3" min="0" max="60">
                </div>

                <div class="col-md-6">
                    <label class="form-label small mb-1">Business</label>
                    <select name="business" class="form-select form-select-sm">
                        <option value="">—</option>
                        @foreach($accounts as $key => $acc)<option value="{{ $key }}">{{ $acc['name'] }}</option>@endforeach
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small mb-1">Installer / Assigned To</label>
                    <input type="text" name="assigned_to" list="installerList" class="form-control form-control-sm" placeholder="Name">
                </div>

                <div class="col-auto d-flex align-items-end pb-1">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_birthday" value="1" id="f_birthday">
                        <label class="form-check-label" for="f_birthday">🎂 Birthday Installation</label>
                    </div>
                </div>
                <div class="col-md-4 d-none dob-hide" id="dobWrap">
                    <label class="form-label small mb-1">Date of Birth <span class="text-danger">*</span></label>
                    <input type="date" name="dob" id="f_dob" class="form-control form-control-sm">
                </div>

                <div class="col-12">
                    <label class="form-label small mb-1">Order Details</label>
                    <textarea name="order_details" class="form-control form-control-sm" rows="2" placeholder="Product, spec, notes…"></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label small mb-1">Attach Photos / Documents</label>
                    <input type="file" id="f_files" name="files[]" class="form-control form-control-sm" multiple accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt">
                    <div id="newFilePreviews" class="d-flex flex-wrap gap-2 mt-2"></div>
                    <div id="existingFiles" class="d-flex flex-wrap gap-2 mt-2"></div>
                </div>
            </div>
        </div>
        <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-outline-danger d-none" id="deleteEntryBtn"><i class="bi bi-trash"></i> Delete</button>
            <div class="ms-auto">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="saveEntryBtn">Save Entry</button>
            </div>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Delete confirmation --}}
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-body text-center p-4">
        <div class="mb-2" style="font-size:2.2rem;">🗑️</div>
        <h6 class="fw-bold mb-1">Delete this entry?</h6>
        <p class="text-muted small mb-3" id="confirmDeleteText">This permanently removes the entry and all attached files. This can’t be undone.</p>
        <div class="d-flex gap-2 justify-content-center">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger" id="confirmDeleteBtn"><i class="bi bi-trash"></i> Delete permanently</button>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script>
    const CSRF = document.querySelector('meta[name=csrf-token]').content;
    const storeUrl = '{{ route('calendar.store') }}';
    const eventsUrl = '{{ route('calendar.events') }}';
    const modalEl = document.getElementById('entryModal');
    const modal = new bootstrap.Modal(modalEl);
    const form = document.getElementById('entryForm');

    // Escape a string for safe insertion into HTML.
    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, c =>
        ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    // ---------- FullCalendar ----------
    const calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
        initialView: 'dayGridMonth',
        headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth' },
        height: 'auto',
        firstDay: 1,
        editable: true,
        eventSources: [eventsUrl],
        dateClick: (info) => openCreate(info.dateStr),
        eventClick: (info) => openEdit(info.event.extendedProps),
        eventDrop: (info) => quickUpdate(info.event.id, {
            _action: 'reschedule',
            start_date: info.event.startStr.substring(0, 10),
            end_date: info.event.end ? new Date(info.event.end.getTime() - 86400000).toISOString().substring(0, 10) : '',
        }, info.revert),
        eventResize: (info) => quickUpdate(info.event.id, {
            _action: 'reschedule',
            start_date: info.event.startStr.substring(0, 10),
            end_date: info.event.end ? new Date(info.event.end.getTime() - 86400000).toISOString().substring(0, 10) : '',
        }, info.revert),
    });
    calendar.render();

    // ---------- Modal helpers ----------
    function resetForm() {
        form.reset();
        form.action = storeUrl;
        document.getElementById('entryModalTitle').textContent = 'New Calendar Entry';
        document.getElementById('deleteEntryBtn').classList.add('d-none');
        document.getElementById('existingFiles').innerHTML = '';
        document.getElementById('formErrors').classList.add('d-none');
        document.getElementById('dobWrap').classList.add('d-none', 'dob-hide');
        form.dataset.id = '';
        clearSelectedFiles();
    }
    function openCreate(dateStr) {
        resetForm();
        if (dateStr) form.querySelector('[name=start_date]').value = dateStr;
        modal.show();
    }
    document.getElementById('newEntryBtn').addEventListener('click', () => openCreate());

    function openEdit(p) {
        resetForm();
        form.action = '{{ url('calendar') }}/' + p.id;
        form.dataset.id = p.id;
        document.getElementById('entryModalTitle').textContent = 'Edit · ' + p.client_name;
        document.getElementById('deleteEntryBtn').classList.remove('d-none');
        const set = (n, v) => { const el = form.querySelector('[name="' + n + '"]'); if (el) el.value = v ?? ''; };
        set('client_name', p.client_name); set('phone', p.phone); set('address', p.address);
        set('start_date', p.start_date); set('end_date', p.end_date); set('status', p.status);
        set('business', p.business); set('assigned_to', p.assigned_to); set('reminder_days', p.reminder_days);
        set('order_details', p.order_details);
        form.querySelector('[name=is_birthday]').checked = !!p.is_birthday;
        set('dob', p.dob);
        toggleDob();
        // existing attachments
        const wrap = document.getElementById('existingFiles');
        wrap.innerHTML = (p.attachments || []).map(a => {
            const name = esc(a.name);
            const url = encodeURI(a.url);
            const inner = a.image
                ? '<a href="' + url + '" target="_blank" title="' + name + '"><img src="' + url + '" class="att-thumb" alt="' + name + '"></a>' +
                  '<span class="att-name" title="' + name + '">' + name + '</span>'
                : '<a href="' + url + '" target="_blank" class="att-file text-decoration-none text-body" title="' + name + '">' +
                  '<span style="font-size:1.5rem">📄</span></a>' +
                  '<span class="att-name" title="' + name + '">' + name + '</span>';
            return '<div class="att-tile">' + inner +
                '<a href="#" class="att-del" data-del-att="' + a.id + '" title="Remove">✕</a></div>';
        }).join('');
        modal.show();
    }

    // birthday → DOB toggle
    const bday = form.querySelector('[name=is_birthday]');
    function toggleDob() {
        const on = bday.checked;
        const wrap = document.getElementById('dobWrap');
        if (on) {
            wrap.classList.remove('d-none');
            requestAnimationFrame(() => wrap.classList.remove('dob-hide'));
        } else {
            wrap.classList.add('dob-hide', 'd-none');
        }
        document.getElementById('f_dob').required = on;
    }
    bday.addEventListener('change', toggleDob);

    // ---------- New-file thumbnails (before upload, with per-file remove) ----------
    const fileInput = document.getElementById('f_files');
    const newPreviews = document.getElementById('newFilePreviews');
    let pickedFiles = [];           // File[] currently staged for upload
    const objectUrls = [];          // track blob URLs to revoke

    function clearSelectedFiles() {
        objectUrls.splice(0).forEach(URL.revokeObjectURL);
        pickedFiles = [];
        if (fileInput) fileInput.value = '';
        if (newPreviews) newPreviews.innerHTML = '';
    }

    function syncInputFiles() {
        const dt = new DataTransfer();
        pickedFiles.forEach(f => dt.items.add(f));
        fileInput.files = dt.files;
    }

    function renderNewPreviews() {
        objectUrls.splice(0).forEach(URL.revokeObjectURL);
        newPreviews.innerHTML = '';
        pickedFiles.forEach((f, i) => {
            const isImg = f.type.startsWith('image/');
            const tile = document.createElement('div');
            tile.className = 'att-tile';
            let inner;
            if (isImg) {
                const url = URL.createObjectURL(f);
                objectUrls.push(url);
                inner = '<img src="' + url + '" class="att-thumb" alt="">';
            } else {
                inner = '<span class="att-file"><span style="font-size:1.5rem">📄</span></span>';
            }
            const name = esc(f.name);
            tile.innerHTML = inner +
                '<span class="att-name" title="' + name + '">' + name + '</span>' +
                '<a href="#" class="att-del" data-rm-new="' + i + '" title="Remove">✕</a>';
            newPreviews.appendChild(tile);
        });
    }

    if (fileInput) {
        fileInput.addEventListener('change', () => {
            // append newly chosen files, skipping exact duplicates (name+size)
            Array.from(fileInput.files).forEach(f => {
                if (!pickedFiles.some(p => p.name === f.name && p.size === f.size)) pickedFiles.push(f);
            });
            syncInputFiles();
            renderNewPreviews();
        });
        newPreviews.addEventListener('click', (e) => {
            const rm = e.target.closest('[data-rm-new]');
            if (!rm) return;
            e.preventDefault();
            pickedFiles.splice(Number(rm.dataset.rmNew), 1);
            syncInputFiles();
            renderNewPreviews();
        });
    }

    // edit buttons in the list
    document.querySelectorAll('.edit-entry').forEach(b => b.addEventListener('click', () => {
        fetch(eventsUrl).then(r => r.json()).then(evts => {
            const ev = evts.find(e => String(e.id) === b.dataset.id);
            if (ev) openEdit(ev.extendedProps);
        });
    }));

    // delete attachment (event delegation)
    document.getElementById('existingFiles').addEventListener('click', (e) => {
        const a = e.target.closest('[data-del-att]');
        if (!a) return;
        e.preventDefault();
        if (!confirm('Remove this attachment?')) return;
        fetch('{{ url('calendar/attachment') }}/' + a.dataset.delAtt, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF } })
            .then(() => a.closest('.att-tile').remove());
    });

    // ---------- Save (create/edit) via fetch + FormData (supports files) ----------
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        document.getElementById('formErrors').classList.add('d-none');
        const btn = document.getElementById('saveEntryBtn');
        btn.disabled = true; btn.textContent = 'Saving…';
        fetch(form.action, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }, body: new FormData(form) })
            .then(async (r) => {
                if (r.ok) { modal.hide(); calendar.refetchEvents(); location.reload(); return; }
                const data = await r.json().catch(() => ({}));
                const box = document.getElementById('formErrors');
                box.innerHTML = data.errors ? Object.values(data.errors).flat().join('<br>') : 'Could not save. Check the fields and try again.';
                box.classList.remove('d-none');
            })
            .finally(() => { btn.disabled = false; btn.textContent = 'Save Entry'; });
    });

    // delete entry — open styled confirmation modal
    const confirmDeleteModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
    document.getElementById('deleteEntryBtn').addEventListener('click', () => {
        if (!form.dataset.id) return;
        const name = (form.querySelector('[name=client_name]').value || 'this entry').trim();
        document.getElementById('confirmDeleteText').innerHTML =
            'This permanently removes <strong>' + name.replace(/</g, '&lt;') + '</strong> and all attached files. This can’t be undone.';
        confirmDeleteModal.show();
    });
    document.getElementById('confirmDeleteBtn').addEventListener('click', () => {
        const id = form.dataset.id;
        if (!id) return;
        const cbtn = document.getElementById('confirmDeleteBtn');
        cbtn.disabled = true; cbtn.textContent = 'Deleting…';
        fetch('{{ url('calendar') }}/' + id, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF } })
            .then(() => { confirmDeleteModal.hide(); modal.hide(); location.reload(); })
            .catch(() => { cbtn.disabled = false; cbtn.innerHTML = '<i class="bi bi-trash"></i> Delete permanently'; });
    });

    // drag/resize reschedule
    function quickUpdate(id, body, revert) {
        const fd = new FormData();
        Object.entries(body).forEach(([k, v]) => fd.append(k, v));
        fetch('{{ url('calendar') }}/' + id, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }, body: fd })
            .then(r => { if (!r.ok) revert(); })
            .catch(() => revert());
    }
</script>
@endpush
