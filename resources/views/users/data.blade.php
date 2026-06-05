<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@9.0.3/dist/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@9.0.3/dist/umd/simple-datatables.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Mono:wght@300;400;500&display=swap" rel="stylesheet">
    <title>CRUD API — Data Monitor</title>
    <style>
        :root {
            --bg: #0a0a0f;
            --surface: #111118;
            --surface2: #18181f;
            --border: #23232e;
            --border-bright: #2e2e3e;
            --accent: #7c6af7;
            --accent2: #4fd1c5;
            --accent3: #f6ad55;
            --danger: #f56565;
            --success: #68d391;
            --text: #e2e2f0;
            --text-muted: #6b6b80;
            --text-dim: #3a3a4a;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'DM Mono', monospace;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Ambient background glow */
        body::before {
            content: '';
            position: fixed;
            top: -200px;
            left: -200px;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(124,106,247,0.06) 0%, transparent 70%);
            pointer-events: none;
            z-index: 0;
        }
        body::after {
            content: '';
            position: fixed;
            bottom: -200px;
            right: -200px;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(79,209,197,0.04) 0%, transparent 70%);
            pointer-events: none;
            z-index: 0;
        }

        /* NAV */
        nav {
            position: sticky;
            top: 0;
            z-index: 100;
            background: rgba(10,10,15,0.85);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border);
            padding: 0 2rem;
            height: 56px;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .nav-logo {
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: 1.1rem;
            color: var(--text);
            letter-spacing: -0.02em;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .nav-logo span.dot {
            display: inline-block;
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--accent);
            box-shadow: 0 0 8px var(--accent);
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }
        .nav-badge {
            font-size: 0.65rem;
            color: var(--text-muted);
            letter-spacing: 0.12em;
            text-transform: uppercase;
            padding: 2px 8px;
            border: 1px solid var(--border-bright);
            border-radius: 999px;
        }

        /* LAYOUT */
        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 2rem;
            position: relative;
            z-index: 1;
        }

        /* PAGE HEADER */
        .page-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            margin-bottom: 1.75rem;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .page-title {
            font-family: 'Syne', sans-serif;
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--text);
            letter-spacing: -0.03em;
        }
        .page-title span {
            color: var(--accent);
        }
        .page-subtitle {
            font-size: 0.7rem;
            color: var(--text-muted);
            letter-spacing: 0.1em;
            text-transform: uppercase;
            margin-top: 2px;
        }

        /* FLASH */
        .flash {
            background: rgba(104, 211, 145, 0.08);
            border: 1px solid rgba(104, 211, 145, 0.25);
            color: var(--success);
            padding: 0.75rem 1.25rem;
            border-radius: 8px;
            font-size: 0.8rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* BUTTONS */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-family: 'DM Mono', monospace;
            font-size: 0.75rem;
            font-weight: 500;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: all 0.15s ease;
            padding: 0.5rem 1rem;
            text-decoration: none;
            white-space: nowrap;
        }
        .btn-primary {
            background: var(--accent);
            color: #fff;
            box-shadow: 0 0 16px rgba(124,106,247,0.25);
        }
        .btn-primary:hover { background: #8f7ef9; box-shadow: 0 0 24px rgba(124,106,247,0.4); transform: translateY(-1px); }
        .btn-success {
            background: rgba(104,211,145,0.12);
            color: var(--success);
            border: 1px solid rgba(104,211,145,0.2);
        }
        .btn-success:hover { background: rgba(104,211,145,0.2); }
        .btn-blue {
            background: rgba(99,179,237,0.12);
            color: #63b3ed;
            border: 1px solid rgba(99,179,237,0.2);
        }
        .btn-blue:hover { background: rgba(99,179,237,0.22); }
        .btn-danger {
            background: rgba(245,101,101,0.12);
            color: var(--danger);
            border: 1px solid rgba(245,101,101,0.2);
        }
        .btn-danger:hover { background: rgba(245,101,101,0.22); }
        .btn-sm { padding: 0.3rem 0.7rem; font-size: 0.7rem; border-radius: 6px; }

        /* TABLE CARD */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            overflow: hidden;
        }

        /* Simple-datatables overrides */
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
            border: 1px solid var(--border-bright) !important;
            color: var(--text) !important;
            border-radius: 8px !important;
            padding: 0.4rem 0.75rem !important;
            font-family: 'DM Mono', monospace !important;
            font-size: 0.75rem !important;
            outline: none !important;
        }
        .dataTable-search input:focus { border-color: var(--accent) !important; }
        .dataTable-pagination li a {
            background: var(--surface2) !important;
            border: 1px solid var(--border-bright) !important;
            color: var(--text-muted) !important;
            border-radius: 6px !important;
            font-size: 0.72rem !important;
        }
        .dataTable-pagination li.active a {
            background: var(--accent) !important;
            border-color: var(--accent) !important;
            color: #fff !important;
        }
        .dataTable-info { color: var(--text-muted) !important; font-size: 0.72rem !important; }

        table {
            width: 100%;
            border-collapse: collapse;
        }
        thead tr {
            background: var(--surface2);
            border-bottom: 1px solid var(--border-bright);
        }
        th {
            padding: 0.85rem 1.25rem;
            font-family: 'Syne', sans-serif;
            font-size: 0.65rem;
            font-weight: 700;
            color: var(--text-muted);
            letter-spacing: 0.12em;
            text-transform: uppercase;
            text-align: left;
        }
        tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background 0.12s;
        }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: rgba(255,255,255,0.02); }
        td {
            padding: 0.9rem 1.25rem;
            font-size: 0.8rem;
            color: var(--text);
            vertical-align: middle;
        }

        /* ID badge */
        .id-badge {
            display: inline-block;
            background: var(--surface2);
            border: 1px solid var(--border-bright);
            border-radius: 6px;
            padding: 2px 8px;
            font-size: 0.7rem;
            color: var(--text-muted);
            letter-spacing: 0.05em;
        }

        /* Task chips */
        .task-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: var(--surface2);
            border: 1px solid var(--border-bright);
            border-radius: 999px;
            padding: 3px 10px;
            font-size: 0.68rem;
            color: var(--text);
            cursor: pointer;
            transition: all 0.12s;
            margin: 2px 2px 2px 0;
        }
        .task-chip:hover {
            border-color: var(--accent);
            color: var(--accent);
            background: rgba(124,106,247,0.08);
        }
        .task-chip .status-dot {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .status-pending { background: var(--accent3); }
        .status-in_progress { background: var(--accent2); }
        .status-completed { background: var(--success); }

        .no-tasks { font-size: 0.72rem; color: var(--text-dim); font-style: italic; }

        /* ACTIONS */
        .actions { display: flex; gap: 0.4rem; flex-wrap: wrap; }

        /* MODAL */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 200;
            align-items: center;
            justify-content: center;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(6px);
        }
        .modal-overlay.active { display: flex; }
        .modal {
            background: var(--surface);
            border: 1px solid var(--border-bright);
            border-radius: 16px;
            width: 100%;
            max-width: 440px;
            margin: 1rem;
            box-shadow: 0 32px 80px rgba(0,0,0,0.6);
            animation: modalIn 0.2s ease;
        }
        @keyframes modalIn {
            from { opacity: 0; transform: translateY(12px) scale(0.97); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
        }
        .modal-title {
            font-family: 'Syne', sans-serif;
            font-size: 0.95rem;
            font-weight: 700;
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
        .modal-body { padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem; }
        .modal-footer { padding: 0 1.5rem 1.5rem; display: flex; flex-direction: column; gap: 0.6rem; }

        /* FORM */
        .field label {
            display: block;
            font-size: 0.68rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 0.4rem;
        }
        .field input,
        .field select {
            width: 100%;
            background: var(--surface2);
            border: 1px solid var(--border-bright);
            border-radius: 8px;
            padding: 0.6rem 0.9rem;
            color: var(--text);
            font-family: 'DM Mono', monospace;
            font-size: 0.8rem;
            outline: none;
            transition: border-color 0.15s;
        }
        .field input:focus,
        .field select:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(124,106,247,0.12); }
        .field select option { background: var(--surface2); }
        .field .err { color: var(--danger); font-size: 0.68rem; margin-top: 4px; }

        .btn-full { width: 100%; justify-content: center; padding: 0.65rem 1rem; font-size: 0.8rem; border-radius: 10px; }

        /* DELETE CONFIRM */
        .delete-icon {
            width: 48px; height: 48px;
            background: rgba(245,101,101,0.1);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1rem;
        }
        .delete-icon svg { color: var(--danger); width: 22px; height: 22px; }
        .delete-text {
            text-align: center;
            font-size: 0.82rem;
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 1.25rem;
        }
        .delete-text strong { color: var(--text); }
        .delete-actions { display: flex; gap: 0.75rem; }
        .delete-actions .btn { flex: 1; justify-content: center; padding: 0.6rem; font-size: 0.78rem; border-radius: 8px; }
    </style>
</head>
<body>

<nav>
    <a class="nav-logo" href="#">
        <span class="dot"></span>
        CRUD<span style="color:var(--accent)">_</span>API
    </a>
    <span class="nav-badge">Data Monitor</span>
</nav>

<div class="container">

    @if(session('success'))
    <div class="flash">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif

    <div class="page-header">
        <div>
            <div class="page-title">User <span>Records</span></div>
            <div class="page-subtitle">Manage users &amp; tasks</div>
        </div>
        <button onclick="toggleModal('addUserModal')"
            class="btn btn-primary">
            <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
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
                            <span onclick="toggleModal('editTaskModal-{{ $task->id }}')"
                                class="task-chip">
                                <span class="status-dot status-{{ $task->status }}"></span>
                                {{ $task->title }}
                            </span>
                        @empty
                            <span class="no-tasks">No tasks</span>
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
                    <td colspan="5" style="text-align:center; color:var(--text-dim); padding:3rem;">No users found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- ===================== ADD USER MODAL ===================== -->
<div id="addUserModal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">New User</span>
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
                    <input type="email" name="email" placeholder="email@example.com" />
                    @error('email') <div class="err">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary btn-full">Create User</button>
            </div>
        </form>
    </div>
</div>

<!-- ===================== PER USER MODALS ===================== -->
@foreach ($users as $user)

<!-- Add Task -->
<div id="addTaskModal-{{ $user->id }}" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Add Task — <span style="color:var(--accent)">{{ $user->name }}</span></span>
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
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                    </select>
                    @error('status') <div class="err">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success btn-full">Add Task</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User -->
<div id="editUserModal-{{ $user->id }}" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Edit User</span>
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
                <button type="submit" class="btn btn-blue btn-full">Update User</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete User -->
<div id="deleteModal-{{ $user->id }}" class="modal-overlay">
    <div class="modal">
        <div class="modal-body" style="padding-top:2rem;">
            <div class="delete-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </div>
            <div class="delete-text">Delete <strong>{{ $user->name }}</strong>?<br>This will also remove all their tasks.</div>
            <div class="delete-actions">
                <button type="button" onclick="toggleModal('deleteModal-{{ $user->id }}')" class="btn" style="border:1px solid var(--border-bright);color:var(--text-muted);">Cancel</button>
                <form action="{{ url('/users/' . $user->id) }}" method="POST" style="flex:1;">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger" style="width:100%;justify-content:center;">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Task modals -->
@foreach ($user->tasks as $task)

<!-- Edit Task -->
<div id="editTaskModal-{{ $task->id }}" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Edit Task</span>
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
                        <option value="pending" {{ $task->status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ $task->status === 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                    @error('status') <div class="err">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-blue btn-full">Update Task</button>
                <button type="button" onclick="toggleModal('editTaskModal-{{ $task->id }}'); toggleModal('deleteTaskModal-{{ $task->id }}')"
                    class="btn btn-danger btn-full">Delete Task</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Task -->
<div id="deleteTaskModal-{{ $task->id }}" class="modal-overlay">
    <div class="modal">
        <div class="modal-body" style="padding-top:2rem;">
            <div class="delete-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </div>
            <div class="delete-text">Delete task <strong>{{ $task->title }}</strong>?</div>
            <div class="delete-actions">
                <button type="button"
                    onclick="toggleModal('deleteTaskModal-{{ $task->id }}'); toggleModal('editTaskModal-{{ $task->id }}')"
                    class="btn" style="border:1px solid var(--border-bright);color:var(--text-muted);">Cancel</button>
                <form action="{{ url('/tasks/' . $task->id) }}" method="POST" style="flex:1;">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger" style="width:100%;justify-content:center;">Delete</button>
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

    // Close modal on overlay click
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) this.classList.remove('active');
        });
    });

    // DataTable
    if (document.getElementById("search-table") && typeof simpleDatatables.DataTable !== 'undefined') {
        const dataTable = new simpleDatatables.DataTable("#search-table", {
            searchable: true,
            sortable: true,
            perPage: 10,
        });
    }
</script>

</body>
</html>