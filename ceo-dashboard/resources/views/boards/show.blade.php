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
    ];
@endphp

@push('styles')
<style>
    .board-topbar { display: flex; align-items: center; gap: .6rem; flex-wrap: wrap; margin-bottom: .75rem; }
    .board-topbar .crumb { color: var(--bs-secondary-color); text-decoration: none; font-weight: 600; }
    .board-title { font-family: var(--ceo-font-head); font-size: 1.35rem; font-weight: 800; margin: 0; cursor: text; padding: .1rem .3rem; border-radius: 6px; }
    .board-title:hover { background: var(--bs-tertiary-bg); }

    .board-canvas { display: flex; align-items: flex-start; gap: .85rem; overflow-x: auto; padding-bottom: 1rem; min-height: 60vh; }
    .list-col { flex: 0 0 288px; max-width: 288px; background: var(--bs-tertiary-bg); border: 1px solid var(--ceo-border); border-radius: 12px; padding: .6rem; display: flex; flex-direction: column; max-height: calc(100vh - 190px); }
    .list-head { display: flex; align-items: center; gap: .4rem; padding: .15rem .3rem .5rem; }
    .list-name { font-weight: 700; flex-grow: 1; border-radius: 6px; padding: .15rem .3rem; }
    .list-name:focus { background: var(--bs-body-bg); outline: 2px solid var(--ceo-gold); }
    .list-count { font-size: .72rem; color: var(--bs-secondary-color); background: var(--bs-body-bg); border-radius: 20px; padding: .05rem .5rem; }
    .list-del { border: 0; background: transparent; color: var(--bs-secondary-color); opacity: .6; }
    .list-del:hover { opacity: 1; color: #B5495B; }
    .cards { list-style: none; margin: 0; padding: .15rem; display: flex; flex-direction: column; gap: .5rem; overflow-y: auto; min-height: 8px; }

    .card-tile { background: var(--bs-body-bg); border: 1px solid var(--ceo-border); border-radius: 9px; padding: .5rem .6rem; box-shadow: 0 1px 2px rgba(0,0,0,.06); cursor: pointer; }
    .card-tile:hover { border-color: var(--ceo-gold); }
    .card-tile.dragging { opacity: .5; }
    .card-labels { display: flex; flex-wrap: wrap; gap: .25rem; margin-bottom: .35rem; }
    .card-label-chip { height: 8px; width: 34px; border-radius: 4px; }
    .card-tile-title { font-size: .9rem; line-height: 1.3; }
    .card-badges { display: flex; align-items: center; flex-wrap: wrap; gap: .5rem; margin-top: .4rem; color: var(--bs-secondary-color); font-size: .74rem; }
    .card-badges .badge-pill { display: inline-flex; align-items: center; gap: .25rem; }
    .due-pill { padding: .05rem .4rem; border-radius: 5px; background: var(--bs-tertiary-bg); }
    .due-pill.overdue { background: #B5495B; color: #fff; }
    .due-pill.done { background: #4E7C59; color: #fff; }
    .card-assignees { margin-left: auto; display: flex; gap: .15rem; }
    .avatar-xs { width: 22px; height: 22px; border-radius: 50%; background: var(--ceo-aubergine); color: #fff; font-size: .62rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; }

    .composer input, .composer textarea { font-size: .88rem; }
    .add-btn { border: 0; background: transparent; color: var(--bs-secondary-color); text-align: left; width: 100%; padding: .4rem .5rem; border-radius: 8px; font-weight: 600; }
    .add-btn:hover { background: var(--bs-body-bg); color: var(--ceo-aubergine); }
    .list-add { flex: 0 0 288px; }
    .list-add .add-btn { background: rgba(255,255,255,.04); border: 1px dashed var(--ceo-border); }

    /* Card modal */
    #cardModal .modal-lg { max-width: 780px; }
    .cm-grid { display: grid; grid-template-columns: 1fr 300px; gap: 1.25rem; }
    @media (max-width: 700px) { .cm-grid { grid-template-columns: 1fr; } }
    .cm-section-title { font-size: .72rem; letter-spacing: .06em; text-transform: uppercase; color: var(--bs-secondary-color); font-weight: 700; margin: 1rem 0 .4rem; }
    .chk-progress { height: 8px; border-radius: 5px; background: var(--bs-tertiary-bg); overflow: hidden; }
    .chk-progress > span { display: block; height: 100%; background: #4E7C59; transition: width .2s ease; }
    .chk-item { display: flex; align-items: center; gap: .5rem; padding: .2rem 0; }
    .chk-item.done label { text-decoration: line-through; color: var(--bs-secondary-color); }
    .activity-item { display: flex; gap: .5rem; padding: .5rem 0; border-top: 1px solid var(--ceo-border); font-size: .85rem; }
    .cm-label-chip { height: 30px; min-width: 46px; border-radius: 6px; display: inline-flex; align-items: center; padding: 0 .5rem; color: #fff; font-size: .76rem; font-weight: 600; cursor: pointer; }
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
                    <form method="POST" action="{{ route('boards.destroy', $board) }}"
                          onsubmit="return confirm('Delete this board and everything on it?');">
                        @csrf @method('DELETE')
                        <button class="dropdown-item text-danger"><i class="bi bi-trash"></i> Delete board</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>

    <div class="board-canvas" id="boardCanvas"><!-- rendered by boards.js --></div>
</div></div>

@include('boards.partials.card-modal')

<script>
    window.__ceoNoAutoReload = true;
    window.__BOARD = @json($boardData);
</script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js" crossorigin="anonymous"></script>
<script src="{{ asset('js/boards.js') }}?v=1"></script>
@endsection
