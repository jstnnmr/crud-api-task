@extends('layouts.app')
@section('title', 'Subjects | EaseTask')

@push('styles')
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    .container {
        max-width: 1280px;
        margin: 0 auto;
        padding: 2rem;
        position: relative;
        z-index: 1;
    }

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

    .page-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        margin-bottom: 1.75rem;
        gap: 1rem;
        flex-wrap: wrap;
    }
    .page-title {
        font-family: 'Playfair Display', serif;
        font-size: 1.65rem;
        font-weight: 600;
        color: var(--text);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .page-subtitle {
        font-size: 0.68rem;
        color: var(--text-muted);
        letter-spacing: 0.1em;
        text-transform: uppercase;
        margin-top: 3px;
        opacity: 0.7;
    }

    .btn-blue {
        background: rgba(129,140,248,0.12);
        color: #818cf8;
        border: 1px solid rgba(129,140,248,0.25);
    }
    .btn-blue:hover { background: rgba(129,140,248,0.2); }
    .btn-xs { padding: 0.2rem 0.55rem; font-size: 0.62rem; min-height: 30px; }

    .card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 20px;
        overflow: hidden;
    }

    .subjects-grid {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .subject-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 20px;
        overflow: hidden;
        transition: border-color 0.15s;
    }
    .subject-card:hover { border-color: rgba(142,125,255,0.3); }

    .subject-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--border);
        background: var(--surface2);
    }
    .subject-color {
        width: 12px; height: 12px;
        border-radius: 50%;
        flex-shrink: 0;
        border: 1px solid rgba(255,255,255,0.15);
    }
    .subject-name {
        font-family: 'Playfair Display', serif;
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--text);
        flex: 1;
    }
    .subject-stats {
        font-size: 0.62rem;
        color: var(--text-muted);
        letter-spacing: 0.05em;
        white-space: nowrap;
    }
    .subject-actions {
        display: flex;
        gap: 0.35rem;
        margin-left: auto;
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
    .badge-category { background: rgba(129,140,248,0.08); color: #818cf8; border: 1px solid rgba(129,140,248,0.15); }
    .due-date { font-size: 0.65rem; color: var(--text-muted); white-space: nowrap; }

    .task-row-click { cursor: pointer; }
    .task-row-click td:last-child { position: relative; }
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
    .view-btn-xs { padding: 0.35rem 0.65rem; font-size: 0.65rem; min-height: 32px; }
    .no-tasks {
        text-align: center;
        padding: 1.5rem;
        font-size: 0.72rem;
        color: var(--text-muted);
        font-style: italic;
        opacity: 0.6;
    }

    .lace {
        text-align: center;
        font-size: 1rem;
        letter-spacing: 5px;
        color: var(--border);
        padding: 0.5rem 0 0.3rem;
        pointer-events: none;
        user-select: none;
    }

    .modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 200;
        align-items: center;
        justify-content: center;
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
    .field .err { color: #f87171; font-size: 0.68rem; margin-top: 4px; }

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
        .page-header { flex-direction: column; align-items: stretch; gap: .75rem; }
        .page-title { font-size: 1.3rem; }
        .subject-header { flex-wrap: wrap; padding: .75rem 1rem; gap: .5rem; }
        .subject-header .btn-xs { font-size: .55rem; padding: .15rem .4rem; min-height: 26px; }
        .subject-actions { gap: .2rem; }
        .table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .task-table { min-width: 550px; }
        .task-table th { padding: .5rem .75rem; }
        .task-table td { padding: .55rem .75rem; }
        .subjects-grid { gap: 1rem; }
    }
    @media (min-width: 641px) and (max-width: 1024px) {
        .task-table th { padding: .5rem .85rem; }
        .task-table td { padding: .55rem .85rem; }
    }
</style>
@endpush

@section('content')
<div class="container">

    @if(session('success'))
    <div class="flash">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('success') }} ✨
    </div>
    @endif

    <div class="page-header">
        <div>
            <div class="page-title"> Subjects</div>
            <div class="page-subtitle">Manage your subjects &amp; tasks</div>
        </div>
        <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
            <button onclick="toggleModal('addCategoryModal')" class="btn btn-ghost">
                + Category
            </button>
            <button onclick="toggleModal('addSubjectModal')" class="btn btn-primary">
                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                Add Subject
            </button>
        </div>
    </div>

    @forelse ($subjects as $subject)
    <div class="subject-card" style="margin-bottom:1.25rem;">
        <div class="subject-header">
            <span class="subject-color" style="background:{{ $subject->color ?? '#8e7dff' }}"></span>
            <span class="subject-name">{{ $subject->name }}</span>
            <span class="subject-stats">
                {{ $subject->tasks->where('status', 'completed')->count() }}/{{ $subject->tasks->count() }} done
            </span>
            <div class="subject-actions">
                <button onclick="toggleModal('addTaskModal-{{ $subject->id }}')" class="btn btn-success btn-xs">+ Task</button>
                <button onclick="toggleModal('editSubjectModal-{{ $subject->id }}')" class="btn btn-blue btn-xs">Edit</button>
                <button onclick="toggleModal('deleteSubjectModal-{{ $subject->id }}')" class="btn btn-danger btn-xs">Del</button>
            </div>
        </div>

        @if ($subject->tasks->isNotEmpty())
        <div class="table-wrap">
        <table class="task-table">
            <thead>
                <tr>
                    <th>Task</th>
                    <th>Category</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Due</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($subject->tasks as $task)
                <tr class="task-row-click" onclick="toggleModal('viewTaskModal-{{ $task->id }}')">
                    <td>
                        <div class="task-title">{{ $task->title }}</div>
                        @if ($task->description)
                        <div class="task-desc">{{ $task->description }}</div>
                        @endif
                    </td>
                    <td>
                        @if ($task->category)
                        <span class="badge badge-category">{{ $task->category->name }}</span>
                        @else
                        <span style="color:var(--text-muted);opacity:0.4;font-size:0.62rem;">—</span>
                        @endif
                    </td>
                    <td><span class="badge badge-{{ $task->priority }}">{{ ucfirst($task->priority) }}</span></td>
                    <td><span class="badge badge-{{ $task->status }}">{{ str_replace('_', ' ', ucfirst($task->status)) }}</span></td>
                    <td>
                        @if ($task->status === 'completed')
                        <span class="due-date" style="color:#6ee7b7;">✔ DONE</span>
                        @elseif ($task->due_date)
                        <span class="due-date">{{ \Carbon\Carbon::parse($task->due_date)->format('M j') }}</span>
                        @else
                        <span style="color:var(--text-muted);opacity:0.4;font-size:0.62rem;">—</span>
                        @endif
                    </td>
                    <td style="text-align:right;" onclick="event.stopPropagation()">
                        <div class="actions" style="display:flex;gap:0.3rem;justify-content:flex-end;">
                            @if ($task->status !== 'completed')
                            <form action="{{ route('tasks.complete', $task->id) }}" method="POST" style="display:inline;" onsubmit="triggerConfetti()">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-success btn-xs">✔ Done</button>
                            </form>
                            @endif
                            <button onclick="toggleModal('editTaskModal-{{ $task->id }}')" class="btn btn-blue btn-xs">Edit</button>
                            <button onclick="toggleModal('deleteTaskModal-{{ $task->id }}')" class="btn btn-danger btn-xs">Del</button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        @else
        <div class="no-tasks">No tasks yet. Add one ✨</div>
        @endif
    </div>
    @empty
    <div class="card" style="text-align:center;padding:3rem;color:var(--text-muted);opacity:0.6;">
        No subjects yet. Create your first subject to get started ✦
    </div>
    @endforelse

    @if ($subjects->hasPages())
    <div style="margin-top: 1.5rem; margin-bottom: 1.5rem;">
        {{ $subjects->links() }}
    </div>
    @endif

    <div class="lace">✦ · · · ✦ · · · ✦ · · · ✦</div>

</div>


{{-- ADD SUBJECT MODAL --}}
<div id="addSubjectModal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">✦ New Subject</span>
            <button class="modal-close" onclick="toggleModal('addSubjectModal')">&times;</button>
        </div>
        <form action="{{ route('subjects.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="field">
                    <label>Subject Name</label>
                    <input type="text" name="name" placeholder="e.g. Mathematics" required />
                    @error('name') <div class="err">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label>Color</label>
                    <input type="color" name="color" value="#8e7dff" style="height:42px;padding:3px;cursor:pointer;" />
                    @error('color') <div class="err">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary btn-full">Create Subject ✦</button>
            </div>
        </form>
    </div>
</div>


{{-- ADD CATEGORY MODAL --}}
<div id="addCategoryModal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">✦ New Category</span>
            <button class="modal-close" onclick="toggleModal('addCategoryModal')">&times;</button>
        </div>
        <form action="{{ route('categories.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="field">
                    <label>Category Name</label>
                    <input type="text" name="name" placeholder="e.g. Homework, Quiz" required />
                    @error('name') <div class="err">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary btn-full">Create Category ✦</button>
            </div>
        </form>
    </div>
</div>


{{-- PER SUBJECT MODALS --}}
@foreach ($subjects as $subject)

{{-- Add Task --}}
<div id="addTaskModal-{{ $subject->id }}" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Add Task — <em style="color:var(--accent)">{{ $subject->name }}</em></span>
            <button class="modal-close" onclick="toggleModal('addTaskModal-{{ $subject->id }}')">&times;</button>
        </div>
        <form action="{{ route('tasks.store') }}" method="POST">
            @csrf
            <input type="hidden" name="subject_id" value="{{ $subject->id }}" />
            <div class="modal-body">
                <div class="field">
                    <label>Title</label>
                    <input type="text" name="title" placeholder="Task title" required />
                    @error('title') <div class="err">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label>Description</label>
                    <input type="text" name="description" placeholder="Short description" />
                    @error('description') <div class="err">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label>Category</label>
                    <select name="category_id">
                        <option value="">— None —</option>
                        @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <div class="err">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label>Priority</label>
                    <select name="priority" required>
                        <option value="low">Low (5 pts) 🌙</option>
                        <option value="medium" selected>Medium (10 pts) ⭐</option>
                        <option value="high">High (20 pts) ✦</option>
                    </select>
                    @error('priority') <div class="err">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label>Status</label>
                    <select name="status">
                        <option value="pending" selected>Pending 🌙</option>
                        <option value="in_progress">In Progress ⭐</option>
                        <option value="completed">Completed ✦</option>
                    </select>
                    @error('status') <div class="err">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label>Due Date</label>
                    <input type="date" name="due_date" />
                    @error('due_date') <div class="err">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label>Invite Collaborator (optional)</label>
                    <input type="email" name="invited_email" placeholder="colleague@cosmos.io" />
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success btn-full">Add Task ✦</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Subject --}}
<div id="editSubjectModal-{{ $subject->id }}" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Edit Subject ✦</span>
            <button class="modal-close" onclick="toggleModal('editSubjectModal-{{ $subject->id }}')">&times;</button>
        </div>
        <form action="{{ route('subjects.update', $subject->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="field">
                    <label>Subject Name</label>
                    <input type="text" name="name" value="{{ $subject->name }}" required />
                    @error('name') <div class="err">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label>Color</label>
                    <input type="color" name="color" value="{{ $subject->color ?? '#8e7dff' }}" style="height:42px;padding:3px;cursor:pointer;" />
                    @error('color') <div class="err">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-blue btn-full">Update Subject ✦</button>
            </div>
        </form>
    </div>
</div>

{{-- Delete Subject --}}
<div id="deleteSubjectModal-{{ $subject->id }}" class="modal-overlay">
    <div class="modal">
        <div class="modal-body" style="padding-top:2rem;">
            <div class="delete-icon">🌑</div>
            <div class="delete-text">
                Delete subject <strong>{{ $subject->name }}</strong>?<br>
                This will also remove all its tasks.
            </div>
            <div class="delete-actions">
                <button type="button" onclick="toggleModal('deleteSubjectModal-{{ $subject->id }}')" class="btn btn-success">Cancel</button>
                <form action="{{ route('subjects.destroy', $subject->id) }}" method="POST" style="flex:1;">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger" style="width:100%;justify-content:center;">
                        Delete ✦
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Edit Task --}}
@foreach ($subject->tasks as $task)
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
                    @error('title') <div class="err">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label>Description</label>
                    <input type="text" name="description" value="{{ $task->description }}" />
                    @error('description') <div class="err">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label>Category</label>
                    <select name="category_id">
                        <option value="">— None —</option>
                        @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $task->category_id === $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <div class="err">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label>Priority</label>
                    <select name="priority">
                        <option value="low"    {{ $task->priority === 'low'    ? 'selected' : '' }}>Low (5 pts) 🌙</option>
                        <option value="medium" {{ $task->priority === 'medium' ? 'selected' : '' }}>Medium (10 pts) ⭐</option>
                        <option value="high"   {{ $task->priority === 'high'   ? 'selected' : '' }}>High (20 pts) ✦</option>
                    </select>
                    @error('priority') <div class="err">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label>Status</label>
                    <select name="status">
                        <option value="pending"     {{ $task->status === 'pending'     ? 'selected' : '' }}>Pending 🌙</option>
                        <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>In Progress ⭐</option>
                        <option value="completed"   {{ $task->status === 'completed'   ? 'selected' : '' }}>Completed ✦</option>
                    </select>
                    @error('status') <div class="err">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label>Due Date</label>
                    <input type="date" name="due_date" value="{{ $task->due_date ? $task->due_date->format('Y-m-d') : '' }}" />
                    @error('due_date') <div class="err">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label>Invite Collaborator (optional)</label>
                    <input type="email" name="invited_email" placeholder="colleague@cosmos.io" />
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-blue btn-full">Update Task ✦</button>
                <button type="button"
                    onclick="toggleModal('editTaskModal-{{ $task->id }}'); toggleModal('deleteTaskModal-{{ $task->id }}')"
                    class="btn btn-danger btn-full">Delete Task</button>
            </div>
        </form>
    </div>
</div>

{{-- Delete Task --}}
<div id="deleteTaskModal-{{ $task->id }}" class="modal-overlay">
    <div class="modal">
        <div class="modal-body" style="padding-top:2rem;">
            <div class="delete-icon">🌑</div>
            <div class="delete-text">Delete task <strong>{{ $task->title }}</strong>?</div>
            <div class="delete-actions">
                <button type="button"
                    onclick="toggleModal('deleteTaskModal-{{ $task->id }}'); toggleModal('editTaskModal-{{ $task->id }}')"
                    class="btn btn-success">Cancel</button>
                <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" style="flex:1;">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger" style="width:100%;justify-content:center;">
                        Delete ✦
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- View Task --}}
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
                    <button type="submit" class="btn btn-success view-btn-xs">✔ Done</button>
                </form>
                @endif
                <button onclick="event.stopPropagation();toggleModal('viewTaskModal-{{ $task->id }}');toggleModal('editTaskModal-{{ $task->id }}')" class="btn btn-blue view-btn-xs">Edit</button>
                <button onclick="event.stopPropagation();toggleModal('viewTaskModal-{{ $task->id }}');toggleModal('deleteTaskModal-{{ $task->id }}')" class="btn btn-danger view-btn-xs">Delete</button>
            </div>
        </div>
    </div>
</div>
@endforeach
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
