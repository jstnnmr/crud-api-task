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
    .task-group-click { cursor: pointer; transition: background .15s; border-radius: 10px; padding: 0.25rem 0.5rem; margin: 0 -0.5rem; }
    .task-group-click:hover { background: var(--surface2); }

    .view-modal { max-width: 520px; }
    .view-detail { display: flex; flex-direction: column; gap: 0.85rem; }
    .view-detail-row { display: flex; align-items: flex-start; gap: 0.75rem; }
    .view-detail-label { font-size: 0.62rem; letter-spacing: 0.09em; text-transform: uppercase; color: var(--text-muted); min-width: 80px; flex-shrink: 0; padding-top: 2px; }
    .view-detail-value { font-size: 0.82rem; color: var(--text); }
    .view-detail-value .subject-color { width: 10px; height: 10px; vertical-align: middle; }
    .view-title { font-family: 'Playfair Display', serif; font-size: 1.15rem; font-weight: 600; color: var(--text); margin-bottom: 0.15rem; }
    .view-desc { font-size: 0.78rem; color: var(--text-muted); line-height: 1.6; }
    .view-actions { display: flex; gap: 0.75rem; margin-top: 0.5rem; }
    .view-actions .btn { flex: 1; justify-content: center; padding: 0.5rem 1rem; font-size: 0.72rem; }
    .view-divider { height: 1px; background: var(--border); margin: 0.25rem 0; }

    .modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 200;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        background: rgba(15,12,41,0.75);
        backdrop-filter: blur(6px);
    }
    .modal-overlay.active { display: flex; }
    .modal {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 20px;
        width: 100%;
        max-width: 420px;
        margin: 1rem;
        box-shadow: 0 24px 60px rgba(0,0,0,0.5);
        animation: modalIn 0.2s ease;
    }
    @keyframes modalIn {
        from { opacity: 0; transform: translateY(10px) scale(0.97); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }
    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1.1rem 1.4rem;
        border-bottom: 1px solid var(--border);
        background: var(--surface2);
        border-radius: 18px 18px 0 0;
    }
    .modal-title {
        font-family: 'Playfair Display', serif;
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--text);
    }
    .modal-close {
        background: none;
        border: none;
        color: var(--text-muted);
        font-size: 1.2rem;
        cursor: pointer;
        line-height: 1;
        padding: 2px 6px;
        border-radius: 4px;
        transition: color 0.1s;
    }
    .modal-close:hover { color: var(--text); }
    .modal-body { padding: 1.4rem; display: flex; flex-direction: column; gap: 1rem; }
    .modal-footer { padding: 0 1.4rem 1.4rem; display: flex; flex-direction: column; gap: 0.55rem; }

    .field label {
        display: block;
        font-size: 0.64rem;
        letter-spacing: 0.09em;
        text-transform: uppercase;
        color: var(--text-muted);
        margin-bottom: 0.38rem;
    }
    .field input,
    .field select {
        width: 100%;
        background: var(--surface2);
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 0.58rem 0.9rem;
        color: var(--text);
        font-family: 'DM Sans', sans-serif;
        font-size: 0.8rem;
        outline: none;
        transition: border-color 0.15s;
    }
    .field input:focus,
    .field select:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(142,125,255,0.12);
    }
    .field input::placeholder { color: var(--text-muted); opacity: 0.5; }
    .field select option { background: var(--surface2); color: var(--text); }

    .delete-icon {
        width: 52px; height: 52px;
        background: rgba(248,113,113,0.1);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 1rem;
        font-size: 1.5rem;
    }
    .delete-text {
        text-align: center;
        font-size: 0.82rem;
        color: var(--text-muted);
        line-height: 1.65;
        margin-bottom: 1.25rem;
    }
    .delete-text strong { color: var(--text); }
    .delete-actions { display: flex; gap: 0.75rem; }
    .delete-actions .btn {
        flex: 1; justify-content: center;
        padding: 0.6rem; font-size: 0.78rem; border-radius: 12px;
    }
    .btn-blue {
        background: rgba(129,140,248,0.12);
        color: #818cf8;
        border: 1px solid rgba(129,140,248,0.25);
    }
    .btn-blue:hover { background: rgba(129,140,248,0.2); }
    .badge-category { background: rgba(129,140,248,0.08); color: #818cf8; border: 1px solid rgba(129,140,248,0.15); }

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
                <div class="task-group task-group-click" onclick="toggleModal('viewTaskModalTeam-{{ $task->id }}')">
                    <div class="task-group-name">
                        @if ($task->subject)
                        <span class="subject-color" style="background:{{ $task->subject->color ?? '#8e7dff' }}"></span>
                        @endif
                        {{ $task->title }}
                    </div>
                    <div class="collab-list" onclick="event.stopPropagation()">
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

@foreach ($tasks as $task)

{{-- View Task Modal --}}
<div id="viewTaskModalTeam-{{ $task->id }}" class="modal-overlay">
    <div class="modal view-modal">
        <div class="modal-header">
            <span class="modal-title">✦ Task Details</span>
            <button class="modal-close" onclick="toggleModal('viewTaskModalTeam-{{ $task->id }}')">&times;</button>
        </div>
        <div class="modal-body">
            <div>
                <div class="view-title">{{ $task->title }}</div>
                @if ($task->description)
                <div class="view-desc">{{ $task->description }}</div>
                @endif
            </div>
            <div class="view-divider"></div>
            <div class="view-detail">
                @if ($task->subject)
                <div class="view-detail-row">
                    <span class="view-detail-label">Subject</span>
                    <span class="view-detail-value">
                        <span class="subject-color" style="background:{{ $task->subject->color ?? '#8e7dff' }}"></span>
                        {{ $task->subject->name }}
                    </span>
                </div>
                @endif
                @if ($task->category)
                <div class="view-detail-row">
                    <span class="view-detail-label">Category</span>
                    <span class="view-detail-value"><span class="badge badge-category">{{ $task->category->name }}</span></span>
                </div>
                @endif
                <div class="view-detail-row">
                    <span class="view-detail-label">Priority</span>
                    <span class="view-detail-value"><span class="badge badge-{{ $task->priority }}">{{ ucfirst($task->priority) }}</span></span>
                </div>
                <div class="view-detail-row">
                    <span class="view-detail-label">Status</span>
                    <span class="view-detail-value"><span class="badge badge-{{ $task->status }}">{{ str_replace('_', ' ', ucfirst($task->status)) }}</span></span>
                </div>
                @if ($task->due_date)
                <div class="view-detail-row">
                    <span class="view-detail-label">Due Date</span>
                    <span class="view-detail-value">{{ \Carbon\Carbon::parse($task->due_date)->format('M j, Y') }}</span>
                </div>
                @endif
                <div class="view-detail-row">
                    <span class="view-detail-label">Points</span>
                    <span class="view-detail-value">{{ $task->points_earned ?? \App\Models\Task::POINTS[$task->priority] ?? 0 }} pts</span>
                </div>
                <div class="view-detail-row">
                    <span class="view-detail-label">Created</span>
                    <span class="view-detail-value">{{ $task->created_at->format('M j, Y') }}</span>
                </div>
            </div>
            <div class="view-divider"></div>
            <div class="view-actions">
                @if ($task->status !== 'completed')
                <form action="{{ route('tasks.complete', $task->id) }}" method="POST" style="display:inline;" onsubmit="triggerConfetti()">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-success">✔ Done</button>
                </form>
                @endif
                <button onclick="event.stopPropagation();toggleModal('viewTaskModalTeam-{{ $task->id }}');toggleModal('editTaskModalTeam-{{ $task->id }}')" class="btn btn-blue">Edit</button>
                <button onclick="event.stopPropagation();toggleModal('viewTaskModalTeam-{{ $task->id }}');toggleModal('deleteTaskModalTeam-{{ $task->id }}')" class="btn btn-danger">Delete</button>
            </div>
        </div>
    </div>
</div>

{{-- Edit Task Modal --}}
<div id="editTaskModalTeam-{{ $task->id }}" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Edit Task ✦</span>
            <button class="modal-close" onclick="toggleModal('editTaskModalTeam-{{ $task->id }}')">&times;</button>
        </div>
        <form action="{{ route('tasks.update', $task->id) }}" method="POST">
            @csrf @method('PUT')
            <div class="modal-body">
                <div class="field">
                    <label>Title</label>
                    <input type="text" name="title" value="{{ $task->title }}" required />
                </div>
                <div class="field">
                    <label>Description</label>
                    <input type="text" name="description" value="{{ $task->description }}" />
                </div>
                <div class="field">
                    <label>Category</label>
                    <select name="category_id">
                        <option value="">— None —</option>
                        @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $task->category_id === $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>Priority</label>
                    <select name="priority">
                        <option value="low"    {{ $task->priority === 'low'    ? 'selected' : '' }}>Low (5 pts) 🌙</option>
                        <option value="medium" {{ $task->priority === 'medium' ? 'selected' : '' }}>Medium (10 pts) ⭐</option>
                        <option value="high"   {{ $task->priority === 'high'   ? 'selected' : '' }}>High (20 pts) ✦</option>
                    </select>
                </div>
                <div class="field">
                    <label>Status</label>
                    <select name="status">
                        <option value="pending"     {{ $task->status === 'pending'     ? 'selected' : '' }}>Pending 🌙</option>
                        <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>In Progress ⭐</option>
                        <option value="completed"   {{ $task->status === 'completed'   ? 'selected' : '' }}>Completed ✦</option>
                    </select>
                </div>
                <div class="field">
                    <label>Due Date</label>
                    <input type="date" name="due_date" value="{{ $task->due_date ? $task->due_date->format('Y-m-d') : '' }}" />
                </div>
                <div class="field">
                    <label>Invite Collaborator (optional)</label>
                    <input type="email" name="invited_email" placeholder="colleague@cosmos.io" />
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-blue btn-full">Update Task ✦</button>
                <button type="button"
                    onclick="toggleModal('editTaskModalTeam-{{ $task->id }}');toggleModal('deleteTaskModalTeam-{{ $task->id }}')"
                    class="btn btn-danger btn-full">Delete Task</button>
            </div>
        </form>
    </div>
</div>

{{-- Delete Task Modal --}}
<div id="deleteTaskModalTeam-{{ $task->id }}" class="modal-overlay">
    <div class="modal">
        <div class="modal-body" style="padding-top:2rem;">
            <div class="delete-icon">🌑</div>
            <div class="delete-text">Delete task <strong>{{ $task->title }}</strong>?</div>
            <div class="delete-actions">
                <button type="button"
                    onclick="toggleModal('deleteTaskModalTeam-{{ $task->id }}');toggleModal('editTaskModalTeam-{{ $task->id }}')"
                    class="btn btn-success">Cancel</button>
                <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" style="flex:1;">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger" style="width:100%;justify-content:center;">Delete ✦</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endforeach

<script>
    function toggleModal(id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.toggle('active');
    }

    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) this.classList.remove('active');
        });
    });
</script>
@endsection
