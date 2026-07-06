@extends('layouts.app')

@section('title', 'Boards')

@php
    $palette = ['#2563EB', '#3B2A4A', '#4E7C59', '#B5495B', '#CD8B3C', '#5E7C99', '#6A4E86', '#0F766E'];
    $isOwner = $current && $current->owner_id === auth()->id();
@endphp

@push('styles')
<style>
    .wsp-shell { display: grid; grid-template-columns: 260px 1fr; gap: 1.25rem; align-items: start; }
    @media (max-width: 768px) { .wsp-shell { grid-template-columns: 1fr; } }
    .wsp-rail { background: var(--bs-body-bg); border: 1px solid var(--ceo-border); border-radius: 14px; padding: .75rem; }
    .wsp-rail-head { font-size: .72rem; letter-spacing: .08em; color: var(--bs-secondary-color); font-weight: 700; padding: .25rem .5rem .5rem; }
    .wsp-list { list-style: none; margin: 0; padding: 0; }
    .wsp-item { display: flex; align-items: center; gap: .6rem; padding: .5rem; border-radius: 10px; color: inherit; text-decoration: none; font-weight: 600; }
    .wsp-item:hover { background: var(--bs-tertiary-bg); color: inherit; }
    .wsp-item.active { background: rgba(200,162,75,.14); color: var(--ceo-aubergine); }
    html[data-theme="dark"] .wsp-item.active { color: var(--ceo-gold); }
    .wsp-badge, .board-badge { flex: 0 0 auto; width: 34px; height: 34px; border-radius: 8px; color: #fff; font-weight: 800; display: inline-flex; align-items: center; justify-content: center; }
    .btn-add-wsp { width: 100%; margin-top: .5rem; border: 1px dashed var(--ceo-border); background: transparent; color: var(--bs-secondary-color); border-radius: 10px; padding: .55rem; font-weight: 600; }
    .btn-add-wsp:hover { border-color: var(--ceo-gold); color: var(--ceo-aubergine); }

    .wsp-head { display: flex; align-items: center; gap: .8rem; margin-bottom: .35rem; }
    .wsp-head .board-badge { width: 44px; height: 44px; border-radius: 10px; font-size: 1.1rem; }
    .wsp-title { margin: 0; font-family: var(--ceo-font-head); }
    .wsp-sub { color: var(--bs-secondary-color); font-size: .85rem; }
    .wsp-tabs { display: flex; gap: .25rem; border-bottom: 1px solid var(--ceo-border); margin: .75rem 0 1.1rem; }
    .wsp-tab { padding: .5rem .85rem; border-radius: 8px 8px 0 0; color: var(--bs-secondary-color); text-decoration: none; font-weight: 600; font-size: .9rem; border-bottom: 2px solid transparent; }
    .wsp-tab.active { color: var(--ceo-aubergine); border-bottom-color: var(--ceo-gold); }
    html[data-theme="dark"] .wsp-tab.active { color: var(--ceo-gold); }
    .wsp-tab:hover { background: var(--bs-tertiary-bg); }

    .board-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 1rem; }
    .board-tile { position: relative; min-height: 118px; border-radius: 12px; padding: .9rem; color: #fff; text-decoration: none; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 2px 6px rgba(0,0,0,.12); transition: transform .12s ease, box-shadow .12s ease; }
    .board-tile:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,.2); color: #fff; }
    .board-tile-name { font-weight: 800; font-size: 1.05rem; text-shadow: 0 1px 2px rgba(0,0,0,.25); }
    .board-tile-meta { font-size: .78rem; opacity: .9; }
    .board-create { background: var(--bs-tertiary-bg); color: var(--bs-secondary-color); border: 1px dashed var(--ceo-border); align-items: center; justify-content: center; flex-direction: row; gap: .4rem; font-weight: 600; box-shadow: none; }
    .board-create:hover { color: var(--ceo-aubergine); border-color: var(--ceo-gold); background: var(--bs-tertiary-bg); }

    .swatches { display: flex; flex-wrap: wrap; gap: .4rem; }
    .swatches input { position: absolute; opacity: 0; }
    .swatches label { width: 30px; height: 30px; border-radius: 7px; cursor: pointer; border: 2px solid transparent; }
    .swatches input:checked + label { border-color: var(--bs-body-color); box-shadow: 0 0 0 2px var(--bs-body-bg), 0 0 0 4px var(--ceo-gold); }
</style>
@endpush

@section('content')
<div class="app-content"><div class="container-fluid py-3">
    <div class="wsp-shell">
        {{-- Workspace rail --}}
        <aside class="wsp-rail">
            <div class="wsp-rail-head">WORKSPACES</div>
            <ul class="wsp-list">
                @forelse($workspaces as $ws)
                    <li>
                        <a href="{{ route('workspaces.index', ['workspace' => $ws->id]) }}"
                           class="wsp-item {{ $current && $current->id === $ws->id ? 'active' : '' }}">
                            <span class="wsp-badge" style="background: {{ $ws->color }}">{{ mb_strtoupper(mb_substr($ws->name, 0, 1)) }}</span>
                            <span class="wsp-name text-truncate">{{ $ws->name }}</span>
                        </a>
                    </li>
                @empty
                    <li class="text-muted small px-2 py-1">No workspaces yet.</li>
                @endforelse
            </ul>
            <button class="btn-add-wsp" data-bs-toggle="modal" data-bs-target="#wsCreate">
                <i class="bi bi-plus-lg"></i> Create workspace
            </button>
        </aside>

        {{-- Selected workspace --}}
        <section class="wsp-main">
            @if($current)
                <div class="wsp-head">
                    <span class="board-badge" style="background: {{ $current->color }}">{{ mb_strtoupper(mb_substr($current->name, 0, 1)) }}</span>
                    <div>
                        <h3 class="wsp-title">{{ $current->name }}</h3>
                        <div class="wsp-sub">{{ $current->members_count }} {{ Str::plural('member', $current->members_count) }}</div>
                    </div>
                </div>

                <nav class="wsp-tabs">
                    <span class="wsp-tab active">Boards</span>
                    <a class="wsp-tab" href="{{ route('members.index', $current) }}">Members</a>
                    <button type="button" class="wsp-tab border-0 bg-transparent" data-bs-toggle="modal" data-bs-target="#wsSettings">Settings</button>
                </nav>

                <div class="board-grid">
                    @foreach($current->boards as $board)
                        <a href="{{ route('boards.show', $board) }}" class="board-tile" style="background: {{ $board->color }}">
                            <span class="board-tile-name">{{ $board->name }}</span>
                            <span class="board-tile-meta">{{ $board->cards_count }} {{ Str::plural('card', $board->cards_count) }}</span>
                        </a>
                    @endforeach
                    <button type="button" class="board-tile board-create" data-bs-toggle="modal" data-bs-target="#boardCreate">
                        <i class="bi bi-plus-lg"></i> Create board
                    </button>
                </div>
            @else
                <div class="text-center text-muted py-5">
                    <i class="bi bi-columns-gap" style="font-size: 2.5rem;"></i>
                    <p class="mt-3 mb-3">You don't have any workspaces yet.</p>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#wsCreate">
                        <i class="bi bi-plus-lg"></i> Create your first workspace
                    </button>
                </div>
            @endif
        </section>
    </div>
</div></div>

{{-- Create workspace --}}
<div class="modal fade" id="wsCreate" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="{{ route('workspaces.store') }}">
        @csrf
        <div class="modal-header"><h5 class="modal-title">Create workspace</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <label class="form-label">Name</label>
            <input name="name" class="form-control" maxlength="255" required autofocus placeholder="e.g. IT/Dev">
            <label class="form-label mt-3">Colour</label>
            @include('boards.partials.swatches', ['field' => 'color', 'palette' => $palette, 'selected' => $palette[1]])
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
            <button class="btn btn-primary">Create</button>
        </div>
    </form>
</div></div></div>

@if($current)
    {{-- Create board --}}
    <div class="modal fade" id="boardCreate" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
        <form method="POST" action="{{ route('boards.store', $current) }}">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Create board in {{ $current->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <label class="form-label">Board name</label>
                <input name="name" class="form-control" maxlength="255" required placeholder="e.g. Portals / System">
                <label class="form-label mt-3">Colour</label>
                @include('boards.partials.swatches', ['field' => 'color', 'palette' => $palette, 'selected' => $palette[0]])
                <p class="text-muted small mt-3 mb-0">Starts with <strong>Not Started</strong>, <strong>In-Progress</strong> and <strong>Completed</strong> lists.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary">Create board</button>
            </div>
        </form>
    </div></div></div>

    {{-- Workspace settings --}}
    <div class="modal fade" id="wsSettings" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Workspace settings</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            @if($isOwner)
                <form method="POST" action="{{ route('workspaces.update', $current) }}">
                    @csrf @method('PATCH')
                    <label class="form-label">Name</label>
                    <input name="name" class="form-control" maxlength="255" value="{{ $current->name }}" required>
                    <label class="form-label mt-3">Colour</label>
                    @include('boards.partials.swatches', ['field' => 'color', 'palette' => $palette, 'selected' => $current->color])
                    <button class="btn btn-primary mt-3">Save changes</button>
                </form>
                <hr>
                <form method="POST" action="{{ route('workspaces.destroy', $current) }}"
                      onsubmit="return confirm('Delete this workspace and all its boards? This cannot be undone.');">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i> Delete workspace</button>
                </form>
            @else
                <p class="text-muted mb-0">Only the workspace owner can change these settings.</p>
            @endif
        </div>
    </div></div></div>
@endif
@endsection
