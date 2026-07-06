@extends('layouts.app')

@section('title', $board->name)

@php
    $boardData = [
        'id'    => $board->id,
        'name'  => $board->name,
        'color' => $board->color,
        'lists' => $lists,
        'labels' => $board->labels->map(fn ($l) => [
            'id' => $l->id, 'name' => $l->name, 'color' => $l->color,
        ])->values(),
        'members' => $board->workspace->members->map(fn ($u) => [
            'id' => $u->id, 'name' => $u->name, 'initials' => \App\Support\CardPresenter::initials($u->name),
        ])->values(),
        'workspace'     => ['id' => $board->workspace->id, 'name' => $board->workspace->name],
        'currentUserId' => auth()->id(),
        'uploadsDirect' => config('integrations.attachments.disk') === 'r2',
        'maxUploadMb'   => (int) config('integrations.attachments.max_mb', 500),
    ];
@endphp

@push('styles')
<style>
    .board-topbar { display: flex; align-items: center; gap: .6rem; flex-wrap: wrap; margin-bottom: .75rem; }
    .board-topbar .crumb { color: var(--bs-secondary-color); text-decoration: none; font-weight: 600; }
    .board-title { font-family: var(--ceo-font-head); font-size: 1.35rem; font-weight: 800; margin: 0; cursor: text; padding: .1rem .3rem; border-radius: 6px; }
    .board-title:hover { background: var(--bs-tertiary-bg); }

    /* Subtle board-colour tint behind the columns for a friendlier, board-specific feel. */
    .board-panel { border-radius: 14px; padding: 1rem 1rem .5rem; }
    .board-canvas { display: flex; align-items: flex-start; gap: .85rem; overflow-x: auto; padding-bottom: 1rem; min-height: 58vh; }
    .list-col { flex: 0 0 288px; max-width: 288px; background: var(--bs-tertiary-bg); border: 1px solid var(--ceo-border); border-radius: 12px; padding: .6rem; display: flex; flex-direction: column; max-height: calc(100vh - 210px); box-shadow: 0 1px 3px rgba(0,0,0,.06); }
    .list-head { display: flex; align-items: center; gap: .4rem; padding: .15rem .3rem .5rem; }
    .list-name { font-weight: 700; flex-grow: 1; border-radius: 6px; padding: .15rem .3rem; cursor: text; }
    .list-name:hover { background: var(--bs-body-bg); }
    .list-name:focus { background: var(--bs-body-bg); outline: 2px solid var(--ceo-gold); }
    .list-count { font-size: .72rem; color: var(--bs-secondary-color); background: var(--bs-body-bg); border-radius: 20px; padding: .05rem .5rem; }
    /* Kebab (⋯) list-actions menu reveals on list hover to reduce clutter. */
    .list-menu-btn { border: 0; background: transparent; color: var(--bs-secondary-color); opacity: 0; transition: opacity .12s ease; padding: 0 .25rem; line-height: 1; border-radius: 6px; }
    .list-col:hover .list-menu-btn, .list-menu.show .list-menu-btn { opacity: .6; }
    .list-menu-btn:hover, .list-menu.show .list-menu-btn { opacity: 1; color: var(--ceo-aubergine); }
    html[data-theme="dark"] .list-menu-btn:hover, html[data-theme="dark"] .list-menu.show .list-menu-btn { color: var(--ceo-gold); }
    .cards { list-style: none; margin: 0; padding: .15rem; display: flex; flex-direction: column; gap: .5rem; overflow-y: auto; min-height: 8px; }

    .card-tile { background: var(--bs-body-bg); border: 1px solid var(--ceo-border); border-radius: 9px; padding: .5rem .6rem; box-shadow: 0 1px 2px rgba(0,0,0,.06); cursor: pointer; transition: transform .1s ease, box-shadow .1s ease, border-color .1s ease; }
    .card-tile:hover { border-color: var(--ceo-gold); transform: translateY(-1px); box-shadow: 0 4px 10px rgba(0,0,0,.13); }
    .card-tile.dragging { opacity: .5; }
    .card-labels { display: flex; flex-wrap: wrap; gap: .25rem; margin-bottom: .35rem; }
    .card-label-chip { height: 8px; width: 34px; border-radius: 4px; }
    .card-tile-title { font-size: .9rem; line-height: 1.3; }
    .card-tile-title .card-done { color: #4E7C59; }
    /* Card modal: mark-complete toggle by the title */
    .cm-complete { border: 0; background: transparent; color: var(--bs-secondary-color); font-size: 1.35rem; line-height: 1; padding: .1rem .5rem 0 0; }
    .cm-complete:hover { color: #4E7C59; }
    .cm-complete.done { color: #4E7C59; }
    .card-badges { display: flex; align-items: center; flex-wrap: wrap; gap: .5rem; margin-top: .4rem; color: var(--bs-secondary-color); font-size: .74rem; }
    .card-badges .badge-pill { display: inline-flex; align-items: center; gap: .25rem; }
    .due-pill { padding: .05rem .4rem; border-radius: 5px; background: var(--bs-tertiary-bg); }
    .due-pill.overdue { background: #B5495B; color: #fff; }
    .due-pill.done { background: #4E7C59; color: #fff; }
    .card-assignees { margin-left: auto; display: flex; gap: .15rem; }
    .avatar-xs { width: 22px; height: 22px; border-radius: 50%; background: var(--ceo-aubergine); color: #fff; font-size: .62rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; }

    .composer input, .composer textarea { font-size: .88rem; }
    .composer { margin-top: .35rem; }
    .add-btn { border: 0; background: transparent; color: var(--bs-secondary-color); text-align: left; width: 100%; padding: .45rem .55rem; border-radius: 8px; font-weight: 600; display: flex; align-items: center; gap: .4rem; transition: background .12s ease, color .12s ease; }
    .add-btn:hover { background: var(--bs-body-bg); color: var(--ceo-aubergine); }
    .list-add { flex: 0 0 288px; }
    .list-add .add-btn { padding: .7rem .75rem; background: color-mix(in srgb, var(--bs-body-bg) 55%, transparent); border: 1px dashed var(--ceo-border); }
    .list-add .add-btn:hover { border-color: var(--ceo-gold); }

    /* Card modal */
    #cardModal .modal-lg { max-width: 960px; }
    #cardModal .modal-content { font-size: .82rem; }
    #cardModal .modal-title { font-size: 1.1rem; }
    #cardModal .modal-body { padding: 1rem 1.25rem; }
    #cardModal .btn-sm, #cardModal .form-control-sm, #cardModal .form-control { font-size: .8rem; }
    .cm-grid { display: grid; grid-template-columns: 1fr 320px; gap: 1.5rem; }
    @media (max-width: 760px) { .cm-grid { grid-template-columns: 1fr; } }
    .cm-section-title { font-size: .68rem; letter-spacing: .06em; text-transform: uppercase; color: var(--bs-secondary-color); font-weight: 700; margin: .9rem 0 .35rem; }
    .chk-progress { height: 8px; border-radius: 5px; background: var(--bs-tertiary-bg); overflow: hidden; }
    .chk-progress > span { display: block; height: 100%; background: #4E7C59; transition: width .2s ease; }
    .chk-item { display: flex; align-items: center; gap: .5rem; padding: .2rem 0; }
    .chk-item.done label { text-decoration: line-through; color: var(--bs-secondary-color); }
    .activity-item { display: flex; gap: .5rem; padding: .5rem 0; border-top: 1px solid var(--ceo-border); font-size: .85rem; }
    .cm-comment-body { white-space: pre-wrap; word-break: break-word; }
    /* Clickable description (view mode); click to edit. */
    .cm-desc-display { min-height: 46px; padding: .5rem .6rem; border-radius: 8px; cursor: text; white-space: pre-wrap; word-break: break-word; border: 1px solid transparent; }
    .cm-desc-display:hover { background: var(--bs-tertiary-bg); }
    .cm-desc-display a, .cm-comment-body a, .chk-item a { color: var(--ceo-aubergine); text-decoration: underline; word-break: break-all; }
    html[data-theme="dark"] .cm-desc-display a, html[data-theme="dark"] .cm-comment-body a, html[data-theme="dark"] .chk-item a { color: var(--ceo-gold); }
    .cm-label-chip { height: 30px; min-width: 46px; border-radius: 6px; display: inline-flex; align-items: center; padding: 0 .5rem; color: #fff; font-size: .76rem; font-weight: 600; cursor: pointer; }

    /* Attachments (row layout) */
    .att-list { display: flex; flex-direction: column; gap: .5rem; }
    .att-row { display: flex; align-items: center; gap: .6rem; padding: .4rem; border: 1px solid var(--ceo-border); border-radius: 10px; background: var(--bs-body-bg); }
    .att-row:hover { border-color: var(--ceo-gold); }
    .att-preview { flex: 0 0 auto; width: 58px; height: 46px; border-radius: 8px; overflow: hidden; display: flex; align-items: center; justify-content: center; background: var(--bs-tertiary-bg); text-decoration: none; }
    .att-preview img { width: 100%; height: 100%; object-fit: cover; }
    .att-ext { font-size: .6rem; font-weight: 800; color: #fff; padding: .12rem .35rem; border-radius: 4px; }
    .att-meta { flex: 1 1 auto; min-width: 0; }
    .att-name { display: block; font-weight: 600; font-size: .82rem; color: inherit; text-decoration: none; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .att-name:hover { text-decoration: underline; }
    .att-sub { font-size: .7rem; color: var(--bs-secondary-color); text-transform: uppercase; letter-spacing: .02em; }
    .att-actions { flex: 0 0 auto; display: flex; gap: .15rem; }
    .att-btn { border: 0; background: transparent; color: var(--bs-secondary-color); padding: .3rem .4rem; border-radius: 6px; text-decoration: none; line-height: 1; }
    .att-btn:hover { background: var(--bs-tertiary-bg); color: var(--ceo-aubergine); }
    .att-btn.del:hover { color: #B5495B; }
    /* Upload progress */
    .up-wrap { margin-bottom: .5rem; }
    .up-name { font-size: .76rem; color: var(--bs-secondary-color); margin-bottom: .2rem; }
    .up-bar { height: 6px; border-radius: 4px; background: var(--bs-tertiary-bg); overflow: hidden; }
    .up-bar > span { display: block; height: 100%; background: var(--ceo-gold); transition: width .15s ease; }

    /* Compact confirmation modal */
    #confirmModal .modal-dialog { max-width: 330px; }
    #confirmModal .modal-header { padding: .8rem 1rem .2rem; border-bottom: 0; }
    #confirmModal .modal-title { font-size: 1rem; font-weight: 700; }
    #confirmModal .btn-close { padding: .5rem; }
    #confirmModal .modal-body { padding: .1rem 1rem .5rem; font-size: .85rem; color: var(--bs-secondary-color); }
    #confirmModal .modal-footer { padding: .3rem .9rem .9rem; border-top: 0; gap: .35rem; }
    #confirmModal .modal-footer .btn { font-size: .82rem; padding: .35rem .85rem; }

    /* Label popover (Trello-style) */
    #cmLabelMenu { width: 270px; }
    .lbl-section-title { font-size: .72rem; letter-spacing: .04em; color: var(--bs-secondary-color); font-weight: 700; margin: .1rem 0 .4rem; }
    .lbl-list { display: flex; flex-direction: column; gap: .4rem; max-height: 260px; overflow-y: auto; margin-bottom: .6rem; }
    .lbl-row { display: flex; align-items: center; gap: .5rem; }
    .lbl-row .form-check-input { margin: 0; flex: 0 0 auto; }
    .lbl-bar { flex: 1 1 auto; height: 34px; border-radius: 6px; display: flex; align-items: center; padding: 0 .65rem; color: #fff; font-weight: 600; font-size: .82rem; cursor: pointer; text-shadow: 0 1px 1px rgba(0,0,0,.25); overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
    .lbl-edit { flex: 0 0 auto; border: 0; background: transparent; color: var(--bs-secondary-color); padding: .25rem; border-radius: 6px; line-height: 1; }
    .lbl-edit:hover { color: var(--ceo-aubergine); background: var(--bs-tertiary-bg); }
    .lbl-head { display: flex; align-items: center; gap: .4rem; margin-bottom: .5rem; }
    .lbl-back { border: 0; background: transparent; color: var(--bs-secondary-color); padding: .1rem .3rem; }
    .lbl-back:hover { color: var(--ceo-aubergine); }
    .lbl-preview { height: 34px; border-radius: 6px; display: flex; align-items: center; padding: 0 .65rem; color: #fff; font-weight: 600; font-size: .82rem; margin-bottom: .5rem; text-shadow: 0 1px 1px rgba(0,0,0,.25); }
    .lbl-swatches { display: grid; grid-template-columns: repeat(5, 1fr); gap: .35rem; margin-bottom: .6rem; }
    .lbl-swatch { height: 30px; border-radius: 6px; cursor: pointer; border: 2px solid transparent; }
    .lbl-swatch.sel { border-color: var(--bs-body-color); box-shadow: 0 0 0 2px var(--bs-body-bg), 0 0 0 4px var(--ceo-gold); }

    /* Dates popover (calendar) */
    #cmDateMenu { width: 300px; }
    .cal-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: .5rem; }
    .cal-title { font-weight: 700; font-size: .9rem; }
    .cal-nav { border: 0; background: transparent; color: var(--bs-secondary-color); padding: .1rem .35rem; border-radius: 6px; font-size: 1rem; line-height: 1; }
    .cal-nav:hover { color: var(--ceo-aubergine); background: var(--bs-tertiary-bg); }
    .cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; }
    .cal-dow { text-align: center; font-size: .66rem; color: var(--bs-secondary-color); font-weight: 700; padding: .2rem 0; }
    .cal-day { text-align: center; padding: .32rem 0; border-radius: 6px; cursor: pointer; font-size: .82rem; }
    .cal-day:hover { background: var(--bs-tertiary-bg); }
    .cal-day.other { color: var(--bs-secondary-color); opacity: .45; }
    .cal-day.today { font-weight: 800; text-decoration: underline; }
    .cal-day.in-range { background: rgba(94, 124, 153, .18); }
    .cal-day.sel-start { background: var(--ceo-gold); color: #1f1726; font-weight: 700; }
    .cal-day.sel-due { background: var(--ceo-aubergine); color: #fff; font-weight: 700; }
    .df-label { font-size: .72rem; letter-spacing: .04em; color: var(--bs-secondary-color); font-weight: 700; margin: .7rem 0 .25rem; }
    .date-field { display: flex; align-items: center; gap: .5rem; padding: .35rem; border-radius: 8px; border: 1px solid transparent; }
    .date-field.active { border-color: var(--ceo-gold); background: var(--bs-tertiary-bg); }
    .date-field .form-check-input { margin: 0; flex: 0 0 auto; }
    .date-field input[type="text"] { cursor: pointer; }
    .dp-time { max-width: 110px; }
</style>
@endpush

@section('content')
<div class="app-content"><div class="container-fluid py-3">
    <div class="board-topbar">
        <a href="{{ route('workspaces.index', ['workspace' => $board->workspace->id]) }}" class="crumb">{{ $board->workspace->name }}</a>
        <i class="bi bi-chevron-right small text-muted"></i>
        <h1 class="board-title" id="boardTitle" data-id="{{ $board->id }}" title="Click to rename">{{ $board->name }}</h1>
        <div class="ms-auto dropdown">
            <button class="btn btn-sm btn-light" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <form method="POST" action="{{ route('boards.destroy', $board) }}" id="deleteBoardForm">
                        @csrf @method('DELETE')
                        <button type="button" class="dropdown-item text-danger" id="deleteBoardBtn"><i class="bi bi-trash"></i> Delete board</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>

    <div class="board-panel" style="background: {{ $board->color }}12;">
        <div class="board-canvas" id="boardCanvas"><!-- rendered by boards.js --></div>
    </div>
</div></div>

@include('boards.partials.card-modal')

{{-- Reusable confirmation modal (replaces browser confirm()). --}}
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmTitle">Please confirm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="confirmBody">Are you sure?</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmOk">Remove</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Pushed so these run AFTER the layout's Bootstrap/SortableJS at the end of
     the body — boards.js relies on `bootstrap` and the DOM being ready. --}}
<script>
    window.__ceoNoAutoReload = true;
    window.__BOARD = @json($boardData);
</script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js" crossorigin="anonymous"></script>
<script src="{{ asset('js/boards.js') }}?v=15"></script>
@endpush
