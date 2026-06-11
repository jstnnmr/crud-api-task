@extends('layouts.app')
@section('title', 'Team | EaseTask')

@push('styles')
<style>
    .container {
        max-width: 1280px;
        margin: 0 auto;
        padding: 2rem;
        position: relative;
        z-index: 1;
    }
    .page-title {
        font-family: 'Playfair Display', serif;
        font-size: 1.65rem;
        font-weight: 600;
        color: var(--text);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.25rem;
    }
    .page-subtitle {
        font-size: 0.68rem;
        color: var(--text-muted);
        letter-spacing: 0.1em;
        text-transform: uppercase;
        opacity: 0.7;
        margin-bottom: 1.75rem;
    }
    .card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 20px;
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    .card-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--border);
        background: var(--surface2);
        font-family: 'Playfair Display', serif;
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--text);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
    }
    .card-body {
        padding: 1.25rem;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .invite-form {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    .invite-form select,
    .invite-form input {
        background: var(--surface2);
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 0.5rem 0.8rem;
        color: var(--text);
        font-family: 'DM Sans', sans-serif;
        font-size: 0.78rem;
        outline: none;
        flex: 1;
        min-width: 140px;
    }
    .invite-form select:focus,
    .invite-form input:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(142,125,255,0.12);
    }
    .invite-form select option { background: var(--surface2); color: var(--text); }
    .btn-sm { padding: 0.28rem 0.75rem; font-size: 0.68rem; }
    .flash {
        background: rgba(110,231,183,0.1);
        border: 1px solid rgba(110,231,183,0.25);
        color: #6ee7b7;
        padding: 0.75rem 1.25rem;
        border-radius: 12px;
        font-size: 0.8rem;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .alert {
        background: rgba(248,113,113,0.1);
        border: 1px solid rgba(248,113,113,0.25);
        color: #f87171;
        padding: 0.6rem 1rem;
        border-radius: 10px;
        font-size: 0.75rem;
        margin-bottom: 1rem;
        text-align: center;
    }
    .invitation-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--border);
        flex-wrap: wrap;
    }
    .invitation-item:last-child { border-bottom: none; }
    .invitation-info { flex: 1; min-width: 0; }
    .invitation-task { font-weight: 500; font-size: 0.82rem; color: var(--text); }
    .invitation-from { font-size: 0.68rem; color: var(--text-muted); }
    .invitation-actions { display: flex; gap: 0.4rem; }
    .field label {
        display: block;
        font-size: 0.64rem;
        letter-spacing: 0.09em;
        text-transform: uppercase;
        color: var(--text-muted);
        margin-bottom: 0.38rem;
    }
    .no-data {
        text-align: center;
        padding: 1.5rem;
        font-size: 0.72rem;
        color: var(--text-muted);
        font-style: italic;
        opacity: 0.6;
    }
    .badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 999px;
        font-size: 0.6rem;
        font-weight: 500;
        letter-spacing: 0.04em;
        white-space: nowrap;
    }
    .badge-pending { background: rgba(251,191,36,0.1); color: #fbbf24; border: 1px solid rgba(251,191,36,0.15); }
    .collab-list {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
    }
    .collab-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        background: var(--surface2);
        border: 1px solid var(--border);
        border-radius: 999px;
        padding: 0.2rem 0.6rem;
        font-size: 0.7rem;
        color: var(--text);
    }
    .collab-chip .remove {
        color: #f87171;
        cursor: pointer;
        font-size: 0.8rem;
        line-height: 1;
        background: none;
        border: none;
        padding: 0;
    }
    .task-group {
        margin-bottom: 0.5rem;
    }
    .task-group-name {
        font-size: 0.72rem;
        font-weight: 500;
        color: var(--text);
        margin-bottom: 0.25rem;
    }
    .task-group-name .subject-color {
        display: inline-block;
        width: 8px; height: 8px;
        border-radius: 50%;
        margin-right: 0.3rem;
    }

    @media (max-width: 640px) {
        .container { padding: 1rem; }
        .page-title { font-size: 1.3rem; }
        .card-header { flex-wrap: wrap; gap: .5rem; }
        .task-table th { padding: .4rem .6rem; font-size: .5rem; }
        .task-table td { padding: .45rem .6rem; font-size: .65rem; }
        .task-table td:nth-child(3) { display: none; }
        .section-title { font-size: .9rem; }
    }
    @media (min-width: 641px) and (max-width: 1024px) {
        .task-table th { padding: .5rem .85rem; }
        .task-table td { padding: .55rem .85rem; }
    }
</style>
@endpush

@section('content')
<div class="container">

    <div class="page-title">🤝 Team</div>
    <div class="page-subtitle">Invite collaborators &amp; manage your team ✦</div>

    @if (session('status'))
    <div class="flash">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('status') }} ✨
    </div>
    @endif

    @if($errors->any())
    <div class="alert">{{ $errors->first() }}</div>
    @endif

    {{-- Invitations --}}
    <div class="card">
        <div class="card-header">
            <span>📨 Pending Invitations</span>
        </div>
        <div class="card-body">
            @if ($invitations->isEmpty())
            <div class="no-data">No pending invitations.</div>
            @else
                @foreach ($invitations as $invitation)
                <div class="invitation-item">
                    <div class="invitation-info">
                        <div class="invitation-task">📋 {{ $invitation->task->title }}</div>
                        <div class="invitation-from">
                            From {{ $invitation->inviter->name ?? 'Unknown' }}
                            @if ($invitation->task->subject)
                            · {{ $invitation->task->subject->name }}
                            @endif
                        </div>
                    </div>
                    <div class="invitation-actions">
                        <form action="{{ url('/team/invitations/' . $invitation->token . '/accept') }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm">Accept ✦</button>
                        </form>
                        <form action="{{ url('/team/invitations/' . $invitation->token . '/decline') }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm">Decline</button>
                        </form>
                    </div>
                </div>
                @endforeach
            @endif
        </div>
    </div>

    {{-- Invite Form --}}
    <div class="card">
        <div class="card-header">
            <span>✉️ Invite to Task</span>
        </div>
        <div class="card-body">
            <form action="{{ url('/team/invite') }}" method="POST" class="invite-form">
                @csrf
                <select name="task_id" required>
                    <option value="">— Select Task —</option>
                    @foreach ($tasks as $task)
                    <option value="{{ $task->id }}">{{ $task->title }} @if($task->subject)({{ $task->subject->name }})@endif</option>
                    @endforeach
                </select>
                <input type="email" name="invited_email" placeholder="colleague@cosmos.io" required>
                <button type="submit" class="btn btn-primary">Send Invite ✦</button>
            </form>
        </div>
    </div>

    {{-- My Tasks & Collaborators --}}
    <div class="card">
        <div class="card-header">
            <span>👥 My Tasks &amp; Collaborators</span>
        </div>
        <div class="card-body">
            @if ($tasks->isEmpty())
            <div class="no-data">No tasks yet.</div>
            @else
                @foreach ($tasks as $task)
                @php $collabs = $task->collaborators ?? collect(); @endphp
                <div class="task-group">
                    <div class="task-group-name">
                        @if ($task->subject)
                        <span class="subject-color" style="background:{{ $task->subject->color ?? '#8e7dff' }}"></span>
                        @endif
                        {{ $task->title }}
                    </div>
                    <div class="collab-list">
                        <span class="badge badge-pending">Owner</span>
                        @foreach ($collabs as $collab)
                        <span class="collab-chip">
                            {{ $collab->name }}
                            <form action="{{ url('/team/tasks/' . $task->id . '/collaborators/' . $collab->id . '/remove') }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="remove" title="Remove">&times;</button>
                            </form>
                        </span>
                        @endforeach
                        @if ($collabs->isEmpty())
                        <span style="font-size:0.68rem;color:var(--text-muted);opacity:0.6;">No collaborators yet</span>
                        @endif
                    </div>
                </div>
                @if (!$loop->last)
                <hr style="border:none;border-top:1px solid var(--border);opacity:0.3;">
                @endif
                @endforeach
                @if ($tasks->hasPages())
                <div style="padding: 1rem 0 0; border-top: 1px solid var(--border); margin-top: 1rem;">
                    {{ $tasks->links() }}
                </div>
                @endif
            @endif
        </div>
    </div>

</div>
@endsection
