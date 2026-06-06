@extends('layouts.app')
@section('title', 'Dashboard | Lumina Tasks')

@push('styles')
<style>
    /* ── LAYOUT ── */
    .container {
        max-width: 1280px;
        margin: 0 auto;
        padding: 2rem;
        position: relative;
        z-index: 1;
    }

    /* ── FLASH ── */
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

    /* ── PAGE HEADER ── */
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

    /* ── BUTTONS ── */
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-family: 'DM Sans', sans-serif;
        font-size: 0.77rem;
        font-weight: 500;
        border-radius: 999px;
        border: none;
        cursor: pointer;
        transition: all 0.15s ease;
        padding: 0.5rem 1.2rem;
        text-decoration: none;
        white-space: nowrap;
    }
    .btn-primary {
        background: var(--accent);
        color: #fff;
        box-shadow: 0 2px 12px rgba(142,125,255,0.3);
    }
    .btn-primary:hover {
        opacity: 0.88;
        transform: translateY(-1px);
        box-shadow: 0 4px 18px rgba(142,125,255,0.4);
    }
    .btn-success {
        background: rgba(110,231,183,0.12);
        color: #6ee7b7;
        border: 1px solid rgba(110,231,183,0.25);
    }
    .btn-success:hover { background: rgba(110,231,183,0.2); }
    .btn-blue {
        background: rgba(129,140,248,0.12);
        color: #818cf8;
        border: 1px solid rgba(129,140,248,0.25);
    }
    .btn-blue:hover { background: rgba(129,140,248,0.2); }
    .btn-danger {
        background: rgba(248,113,113,0.1);
        color: #f87171;
        border: 1px solid rgba(248,113,113,0.25);
    }
    .btn-danger:hover { background: rgba(248,113,113,0.2); }
    .btn-sm { padding: 0.28rem 0.75rem; font-size: 0.68rem; }
    .btn-full { width: 100%; justify-content: center; padding: 0.63rem 1rem; font-size: 0.8rem; border-radius: 12px; }

    /* ── CARD ── */
    .card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 20px;
        overflow: hidden;
    }

    /* ── TABLE ── */
    table { width: 100%; border-collapse: collapse; }
    thead tr {
        background: var(--surface2);
        border-bottom: 1px solid var(--border);
    }
    th {
        padding: 0.85rem 1.25rem;
        font-family: 'DM Sans', sans-serif;
        font-size: 0.62rem;
        font-weight: 600;
        color: var(--text-muted);
        letter-spacing: 0.13em;
        text-transform: uppercase;
        text-align: left;
    }
    tbody tr {
        border-bottom: 1px solid var(--border);
        transition: background 0.12s;
    }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background: var(--surface2); }
    td {
        padding: 0.9rem 1.25rem;
        font-size: 0.8rem;
        color: var(--text);
        vertical-align: middle;
    }

    /* ── simple-datatables overrides ── */
    .dataTable-wrapper .dataTable-top,
    .dataTable-wrapper .dataTable-bottom {
        padding: 1rem 1.5rem;
        background: var(--surface);
        border-bottom: 1px solid var(--border);
    }
    .dataTable-wrapper .dataTable-bottom {
        border-bottom: none;
        border-top: 1px solid var(--border);
    }
    .dataTable-search input,
    .dataTable-selector {
        background: var(--surface2) !important;
        border: 1px solid var(--border) !important;
        color: var(--text) !important;
        border-radius: 999px !important;
        padding: 0.4rem 1rem !important;
        font-family: 'DM Sans', sans-serif !important;
        font-size: 0.75rem !important;
        outline: none !important;
    }
    .dataTable-search input:focus { border-color: var(--accent) !important; }
    .dataTable-pagination li a {
        background: var(--surface2) !important;
        border: 1px solid var(--border) !important;
        color: var(--text-muted) !important;
        border-radius: 999px !important;
        font-size: 0.72rem !important;
    }
    .dataTable-pagination li.active a {
        background: var(--accent) !important;
        border-color: var(--accent) !important;
        color: #fff !important;
    }
    .dataTable-info { color: var(--text-muted) !important; font-size: 0.72rem !important; }

    /* ── ID BADGE ── */
    .id-badge {
        display: inline-block;
        background: rgba(142,125,255,0.12);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 2px 9px;
        font-size: 0.68rem;
        color: var(--text-muted);
    }

    /* ── TASK CHIPS ── */
    .task-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: var(--surface2);
        border: 1px solid var(--border);
        border-radius: 999px;
        padding: 3px 10px 3px 7px;
        font-size: 0.67rem;
        color: var(--text-muted);
        cursor: pointer;
        transition: all 0.12s;
        margin: 2px 2px 2px 0;
    }
    .task-chip:hover {
        border-color: var(--accent);
        color: var(--accent);
        background: rgba(142,125,255,0.1);
    }
    .task-chip .status-dot {
        width: 5px; height: 5px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .status-pending     { background: #fcd34d; }
    .status-in_progress { background: #6ee7b7; }
    .status-completed   { background: var(--accent); }
    .no-tasks { font-size: 0.72rem; color: var(--text-muted); font-style: italic; opacity: 0.6; }

    /* ── ACTIONS ── */
    .actions { display: flex; gap: 0.4rem; flex-wrap: wrap; }

    /* ── LACE ── */
    .lace {
        text-align: center;
        font-size: 1rem;
        letter-spacing: 5px;
        color: var(--border);
        padding: 0.5rem 0 0.3rem;
        pointer-events: none;
        user-select: none;
    }

    /* ── MODAL ── */
    .modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 200;
        align-items: center;
        justify-content: center;
        background: rgba(15,12,41,0.7);
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
        box-shadow: 0 24px 60px rgba(0,0,0,0.4);
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

    /* ── FORM FIELDS ── */
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

    /* ── DELETE MODAL ── */
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
    .delete-actions .btn { flex: 1; justify-content: center; padding: 0.6rem; font-size: 0.78rem; border-radius: 12px; }
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
            <div class="page-title">
                🌙 User Records
            </div>
            <div class="page-subtitle">Manage users &amp; tasks — across the galaxy</div>
        </div>
        <button onclick="toggleModal('addUserModal')" class="btn btn-primary">
            <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
            Add User
        </button>
    </div>

    <div class="card">
        <table id="search-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Tasks</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                <tr>
                    <td><span class="id-badge">#{{ $user->id }}</span></td>
                    <td style="font-weight:500;">{{ $user->name }}</td>
                    <td style="color:var(--text-muted);">{{ $user->email }}</td>
                    <td>
                        @forelse ($user->tasks as $task)
                            <span onclick="toggleModal('editTaskModal-{{ $task->id }}')" class="task-chip">
                                <span class="status-dot status-{{ $task->status }}"></span>
                                {{ $task->title }}
                            </span>
                        @empty
                            <span class="no-tasks">No tasks yet ✨</span>
                        @endforelse
                    </td>
                    <td>
                        <div class="actions">
                            <button onclick="toggleModal('addTaskModal-{{ $user->id }}')" class="btn btn-success btn-sm">+ Task</button>
                            <button onclick="toggleModal('editUserModal-{{ $user->id }}')" class="btn btn-blue btn-sm">Edit</button>
                            <button onclick="toggleModal('deleteModal-{{ $user->id }}')" class="btn btn-danger btn-sm">Delete</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center; color:var(--text-muted); padding:3rem; opacity:0.6;">
                        No users found. ✦
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="lace">✦ · · · ✦ · · · ✦ · · · ✦</div>
    </div>

</div>


{{-- ===================== ADD USER MODAL ===================== --}}
<div id="addUserModal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">✦ New User</span>
            <button class="modal-close" onclick="toggleModal('addUserModal')">&times;</button>
        </div>
        <form action="{{ url('/users') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="field">
                    <label>Name</label>
                    <input type="text" name="name" placeholder="Full name" />
                    @error('name') <div class="err">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="email@cosmos.io" />
                    @error('email') <div class="err">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary btn-full">Create User ✦</button>
            </div>
        </form>
    </div>
</div>


{{-- ===================== PER USER MODALS ===================== --}}
@foreach ($users as $user)

{{-- Add Task --}}
<div id="addTaskModal-{{ $user->id }}" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Add Task — <em style="color:var(--accent)">{{ $user->name }}</em></span>
            <button class="modal-close" onclick="toggleModal('addTaskModal-{{ $user->id }}')">&times;</button>
        </div>
        <form action="{{ url('/tasks') }}" method="POST">
            @csrf
            <input type="hidden" name="user_id" value="{{ $user->id }}" />
            <div class="modal-body">
                <div class="field">
                    <label>Title</label>
                    <input type="text" name="title" placeholder="Task title" />
                    @error('title') <div class="err">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label>Description</label>
                    <input type="text" name="description" placeholder="Short description" />
                    @error('description') <div class="err">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label>Due Date</label>
                    <input type="date" name="due_date" />
                    @error('due_date') <div class="err">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label>Status</label>
                    <select name="status">
                        <option value="pending">Pending 🌙</option>
                        <option value="in_progress">In Progress ⭐</option>
                        <option value="completed">Completed ✦</option>
                    </select>
                    @error('status') <div class="err">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success btn-full">Add Task ✦</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit User --}}
<div id="editUserModal-{{ $user->id }}" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Edit User ✦</span>
            <button class="modal-close" onclick="toggleModal('editUserModal-{{ $user->id }}')">&times;</button>
        </div>
        <form action="{{ url('/users/' . $user->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="field">
                    <label>Name</label>
                    <input type="text" name="name" value="{{ $user->name }}" />
                    @error('name') <div class="err">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label>Email</label>
                    <input type="email" name="email" value="{{ $user->email }}" />
                    @error('email') <div class="err">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-blue btn-full">Update User ✦</button>
            </div>
        </form>
    </div>
</div>

{{-- Delete User --}}
<div id="deleteModal-{{ $user->id }}" class="modal-overlay">
    <div class="modal">
        <div class="modal-body" style="padding-top:2rem;">
            <div class="delete-icon">🌑</div>
            <div class="delete-text">
                Delete <strong>{{ $user->name }}</strong>?<br>
                This will also remove all their tasks.
            </div>
            <div class="delete-actions">
                <button type="button" onclick="toggleModal('deleteModal-{{ $user->id }}')"
                    class="btn btn-success">Cancel</button>
                <form action="{{ url('/users/' . $user->id) }}" method="POST" style="flex:1;">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger" style="width:100%;justify-content:center;">
                        Delete ✦
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Task modals --}}
@foreach ($user->tasks as $task)

{{-- Edit Task --}}
<div id="editTaskModal-{{ $task->id }}" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Edit Task ✦</span>
            <button class="modal-close" onclick="toggleModal('editTaskModal-{{ $task->id }}')">&times;</button>
        </div>
        <form action="{{ url('/tasks/' . $task->id) }}" method="POST">
            @csrf @method('PUT')
            <div class="modal-body">
                <div class="field">
                    <label>Title</label>
                    <input type="text" name="title" value="{{ $task->title }}" />
                    @error('title') <div class="err">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label>Description</label>
                    <input type="text" name="description" value="{{ $task->description }}" />
                    @error('description') <div class="err">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label>Due Date</label>
                    <input type="date" name="due_date" value="{{ $task->due_date }}" />
                    @error('due_date') <div class="err">{{ $message }}</div> @enderror
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
                <form action="{{ url('/tasks/' . $task->id) }}" method="POST" style="flex:1;">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger" style="width:100%;justify-content:center;">
                        Delete ✦
                    </button>
                </form>
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

    if (document.getElementById('search-table') && typeof simpleDatatables !== 'undefined' && typeof simpleDatatables.DataTable !== 'undefined') {
        new simpleDatatables.DataTable('#search-table', {
            searchable: true,
            sortable: true,
            perPage: 10,
        });
    }
</script>
@endsection