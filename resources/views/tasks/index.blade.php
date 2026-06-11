@extends('layouts.app')
@section('title', 'My Tasks | EaseTask')

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
    .task-table { width: 100%; border-collapse: collapse; }
    .task-table th {
        padding: 0.6rem 1.25rem;
        font-size: 0.58rem;
        font-weight: 600;
        color: var(--text-muted);
        letter-spacing: 0.13em;
        text-transform: uppercase;
        text-align: left;
        background: var(--surface);
        border-bottom: 1px solid var(--border);
    }
    .task-table td {
        padding: 0.7rem 1.25rem;
        font-size: 0.75rem;
        color: var(--text);
        vertical-align: middle;
        border-bottom: 1px solid var(--border);
    }
    .task-table tr:last-child td { border-bottom: none; }
    .task-table tr:hover td { background: var(--surface2); }
    .task-title { font-weight: 500; }
    .task-desc {
        font-size: 0.68rem;
        color: var(--text-muted);
        opacity: 0.7;
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
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
    .badge-low { background: rgba(110,231,183,0.12); color: #6ee7b7; border: 1px solid rgba(110,231,183,0.2); }
    .badge-medium { background: rgba(251,191,36,0.12); color: #fbbf24; border: 1px solid rgba(251,191,36,0.2); }
    .badge-high { background: rgba(248,113,113,0.12); color: #f87171; border: 1px solid rgba(248,113,113,0.2); }
    .badge-pending { background: rgba(251,191,36,0.1); color: #fbbf24; border: 1px solid rgba(251,191,36,0.15); }
    .badge-in_progress { background: rgba(110,231,183,0.1); color: #6ee7b7; border: 1px solid rgba(110,231,183,0.15); }
    .badge-completed { background: rgba(142,125,255,0.1); color: #8e7dff; border: 1px solid rgba(142,125,255,0.15); }
    .card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 20px;
        overflow: hidden;
    }
    .subject-color {
        display: inline-block;
        width: 8px; height: 8px;
        border-radius: 50%;
        margin-right: 0.3rem;
    }
    .collab-avatars {
        display: inline-flex;
        align-items: center;
        gap: 2px;
    }
    .collab-avatar {
        width: 22px; height: 22px;
        border-radius: 50%;
        background: var(--accent);
        color: #fff;
        font-size: 0.5rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 2px solid var(--surface);
    }
    .collab-count {
        font-size: 0.6rem;
        color: var(--text-muted);
        margin-left: 0.2rem;
    }
    .due-date { font-size: 0.65rem; color: var(--text-muted); white-space: nowrap; }
    .no-tasks {
        text-align: center;
        padding: 3rem;
        font-size: 0.8rem;
        color: var(--text-muted);
        opacity: 0.6;
    }

    .task-row-click { cursor: pointer; }
    .task-row-click:hover td { background: var(--surface2); }

    .view-modal { max-width: 520px; }
    .view-detail { display: flex; flex-direction: column; gap: 0.85rem; }
    .view-detail-row { display: flex; align-items: flex-start; gap: 0.75rem; }
    .view-detail-label { font-size: 0.62rem; letter-spacing: 0.09em; text-transform: uppercase; color: var(--text-muted); min-width: 80px; flex-shrink: 0; padding-top: 2px; }
    .view-detail-value { font-size: 0.82rem; color: var(--text); }
    .view-detail-value .subject-color { width: 10px; height: 10px; vertical-align: middle; }
    .view-title { font-family: 'Playfair Display', serif; font-size: 1.15rem; font-weight: 600; color: var(--text); margin-bottom: 0.15rem; }
    .view-desc { font-size: 0.78rem; color: var(--text-muted); line-height: 1.6; }
    .view-actions { display: flex; gap: 0.75rem; margin-top: 0.5rem; }
    .view-actions .btn { flex: 1; justify-content: center; }
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

    @media (max-width: 640px) {
        .container { padding: 1rem; }
        .page-title { font-size: 1.3rem; }
        .table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .task-table { min-width: 600px; }
        .task-table th { padding: .5rem .75rem; }
        .task-table td { padding: .55rem .75rem; }
    }
    @media (min-width: 641px) and (max-width: 1024px) {
        .task-table th { padding: .5rem 1rem; }
        .task-table td { padding: .55rem 1rem; }
    }
</style>
@endpush

@section('content')
<div class="container">
    <div class="page-title">📋 My Tasks</div>
    <div class="page-subtitle">All tasks across your subjects and collaborations ✦</div>

    @if ($tasks->isEmpty())
    <div class="card">
        <div class="no-tasks">No tasks yet. Create one to get started ✦</div>
    </div>
    @else
    <div class="card">
        <div class="table-wrap">
        <table class="task-table">
            <thead>
                <tr>
                    <th>Task</th>
                    <th>Subject</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Due</th>
                    <th>Team</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tasks as $task)
                <tr class="task-row-click" onclick="toggleModal('viewTaskModal-{{ $task->id }}')">
                    <td>
                        <div class="task-title">{{ $task->title }}</div>
                        @if ($task->description)
                        <div class="task-desc">{{ $task->description }}</div>
                        @endif
                    </td>
                    <td>
                        @if ($task->subject)
                        <span class="subject-color" style="background:{{ $task->subject->color ?? '#8e7dff' }}"></span>
                        {{ $task->subject->name }}
                        @endif
                    </td>
                    <td><span class="badge badge-{{ $task->priority }}">{{ ucfirst($task->priority) }}</span></td>
                    <td><span class="badge badge-{{ $task->status }}">{{ str_replace('_', ' ', ucfirst($task->status)) }}</span></td>
                    <td>
                        @if ($task->status === 'completed')
                        <span class="due-date" style="color:#6ee7b7;">✔ Done</span>
                        @elseif ($task->due_date)
                        <span class="due-date">{{ \Carbon\Carbon::parse($task->due_date)->format('M j, Y') }}</span>
                        @else
                        <span style="color:var(--text-muted);opacity:0.4;font-size:0.62rem;">—</span>
                        @endif
                    </td>
                    <td>
                        @php
                            $collabs = $task->collaborators ?? collect();
                        @endphp
                        @if ($collabs->isNotEmpty())
                        <div class="collab-avatars">
                            @foreach ($collabs->take(3) as $collab)
                            <span class="collab-avatar" title="{{ $collab->name }}">{{ strtoupper(substr($collab->name, 0, 1)) }}</span>
                            @endforeach
                            @if ($collabs->count() > 3)
                            <span class="collab-count">+{{ $collabs->count() - 3 }}</span>
                            @endif
                        </div>
                        @else
                        <span style="color:var(--text-muted);opacity:0.4;font-size:0.62rem;">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        @if ($tasks->hasPages())
        <div style="padding: 1rem 1.25rem; border-top: 1px solid var(--border);">
            {{ $tasks->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
    @endif
</div>

@foreach ($tasks as $task)

{{-- View Task Modal --}}
<div id="viewTaskModal-{{ $task->id }}" class="modal-overlay">
    <div class="modal view-modal">
        <div class="modal-header">
            <span class="modal-title">✦ Task Details</span>
            <button class="modal-close" onclick="toggleModal('viewTaskModal-{{ $task->id }}')">&times;</button>
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
                    <span class="view-detail-value"><span class="badge badge-category" style="background:rgba(129,140,248,0.08);color:#818cf8;border:1px solid rgba(129,140,248,0.15);">{{ $task->category->name }}</span></span>
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
                    <button type="submit" class="btn btn-success" style="padding:0.5rem 1rem;font-size:0.72rem;">✔ Done</button>
                </form>
                @endif
                <button onclick="event.stopPropagation();toggleModal('viewTaskModal-{{ $task->id }}');toggleModal('editTaskModal-{{ $task->id }}')" class="btn btn-blue" style="padding:0.5rem 1rem;font-size:0.72rem;">Edit</button>
                <button onclick="event.stopPropagation();toggleModal('viewTaskModal-{{ $task->id }}');toggleModal('deleteTaskModal-{{ $task->id }}')" class="btn btn-danger" style="padding:0.5rem 1rem;font-size:0.72rem;">Delete</button>
            </div>
        </div>
    </div>
</div>

{{-- Edit Task Modal --}}
<div id="editTaskModal-{{ $task->id }}" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Edit Task ✦</span>
            <button class="modal-close" onclick="toggleModal('editTaskModal-{{ $task->id }}')">&times;</button>
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
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-blue btn-full" style="background:rgba(129,140,248,0.12);color:#818cf8;border:1px solid rgba(129,140,248,0.25);">Update Task ✦</button>
                <button type="button"
                    onclick="toggleModal('editTaskModal-{{ $task->id }}');toggleModal('deleteTaskModal-{{ $task->id }}')"
                    class="btn btn-danger btn-full">Delete Task</button>
            </div>
        </form>
    </div>
</div>

{{-- Delete Task Modal --}}
<div id="deleteTaskModal-{{ $task->id }}" class="modal-overlay">
    <div class="modal">
        <div class="modal-body" style="padding-top:2rem;">
            <div class="delete-icon">🌑</div>
            <div class="delete-text">Delete task <strong>{{ $task->title }}</strong>?</div>
            <div class="delete-actions">
                <button type="button"
                    onclick="toggleModal('deleteTaskModal-{{ $task->id }}');toggleModal('editTaskModal-{{ $task->id }}')"
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

<style>
    .btn-blue {
        background: rgba(129,140,248,0.12);
        color: #818cf8;
        border: 1px solid rgba(129,140,248,0.25);
    }
    .btn-blue:hover { background: rgba(129,140,248,0.2); }
    .badge-category { background: rgba(129,140,248,0.08); color: #818cf8; border: 1px solid rgba(129,140,248,0.15); }
</style>
@endsection
