@extends('layouts.app')

@section('title', $workspace->name . ' · Members')

@php $isOwner = $workspace->owner_id === auth()->id(); @endphp

@section('content')
<div class="app-content"><div class="container-fluid py-3" style="max-width: 820px;">
    <a href="{{ route('workspaces.index', ['workspace' => $workspace->id]) }}" class="text-decoration-none small">
        <i class="bi bi-arrow-left"></i> Back to boards
    </a>

    <div class="d-flex align-items-center gap-2 mt-2 mb-1">
        <span class="d-inline-flex align-items-center justify-content-center text-white fw-bold"
              style="width:44px;height:44px;border-radius:10px;background: {{ $workspace->color }}">
            {{ mb_strtoupper(mb_substr($workspace->name, 0, 1)) }}
        </span>
        <h3 class="mb-0" style="font-family: var(--ceo-font-head);">{{ $workspace->name }}</h3>
    </div>

    @if(session('refreshed'))
        <div class="alert alert-success py-2 mt-2">{{ session('refreshed') }}</div>
    @endif
    @error('email')<div class="alert alert-danger py-2 mt-2">{{ $message }}</div>@enderror

    @if($isOwner)
        <div class="card mt-3">
            <div class="card-body">
                <h6 class="mb-2">Add a member</h6>
                <form method="POST" action="{{ route('members.store', $workspace) }}" class="row g-2 align-items-end">
                    @csrf
                    <div class="col-sm">
                        <label class="form-label small text-muted mb-1">Dashboard user email</label>
                        <input list="candidateEmails" name="email" type="email" class="form-control" required
                               placeholder="name@bcf.com">
                        <datalist id="candidateEmails">
                            @foreach($candidates as $c)
                                <option value="{{ $c->email }}">{{ $c->name }}</option>
                            @endforeach
                        </datalist>
                    </div>
                    <div class="col-sm-auto">
                        <button class="btn btn-primary"><i class="bi bi-person-plus"></i> Add</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <h6 class="mt-4 mb-2 text-uppercase small text-secondary">{{ $workspace->members->count() }} members</h6>
    <ul class="list-group">
        @foreach($workspace->members as $member)
            <li class="list-group-item d-flex align-items-center gap-3">
                <span class="d-inline-flex align-items-center justify-content-center text-white fw-bold"
                      style="width:36px;height:36px;border-radius:50%;background: var(--ceo-aubergine);">
                    {{ \App\Support\CardPresenter::initials($member->name) }}
                </span>
                <div class="flex-grow-1">
                    <div class="fw-semibold">{{ $member->name }}</div>
                    <div class="small text-muted">{{ $member->email }}</div>
                </div>
                @if($member->id === $workspace->owner_id)
                    <span class="badge text-bg-secondary">Owner</span>
                @elseif($isOwner)
                    <form method="POST" action="{{ route('members.destroy', [$workspace, $member]) }}"
                          onsubmit="return confirm('Remove {{ $member->name }} from this workspace?');">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-x-lg"></i></button>
                    </form>
                @endif
            </li>
        @endforeach
    </ul>
</div></div>
@endsection
