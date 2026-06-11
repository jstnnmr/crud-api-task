@extends('layouts.app')
@section('title', 'Dashboard | EaseTask')

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
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }
    .stat-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 1.25rem;
        text-align: center;
    }
    .stat-icon {
        font-size: 1.5rem;
        margin-bottom: 0.4rem;
    }
    .stat-value {
        font-family: 'Playfair Display', serif;
        font-size: 1.6rem;
        font-weight: 600;
        color: var(--text);
    }
    .stat-label {
        font-size: 0.65rem;
        color: var(--text-muted);
        letter-spacing: 0.08em;
        text-transform: uppercase;
        margin-top: 0.15rem;
    }
    .section-title {
        font-family: 'Playfair Display', serif;
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text);
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 20px;
        overflow: hidden;
        margin-bottom: 1.5rem;
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
    .badge-in_progress { background: rgba(110,231,183,0.1); color: #6ee7b7; border: 1px solid rgba(110,231,183,0.15); }
    .badge-completed { background: rgba(142,125,255,0.1); color: #8e7dff; border: 1px solid rgba(142,125,255,0.15); }
    .badge-low { background: rgba(110,231,183,0.12); color: #6ee7b7; border: 1px solid rgba(110,231,183,0.2); }
    .badge-medium { background: rgba(251,191,36,0.12); color: #fbbf24; border: 1px solid rgba(251,191,36,0.2); }
    .badge-high { background: rgba(248,113,113,0.12); color: #f87171; border: 1px solid rgba(248,113,113,0.2); }
    .subject-color {
        display: inline-block;
        width: 8px; height: 8px;
        border-radius: 50%;
        margin-right: 0.3rem;
    }
    .no-tasks {
        text-align: center;
        padding: 1.5rem;
        font-size: 0.72rem;
        color: var(--text-muted);
        font-style: italic;
        opacity: 0.6;
    }

    .stat-link { text-decoration: none; display: block; }
    .stat-link .stat-card { cursor: pointer; transition: transform 0.15s, box-shadow 0.15s; }
    .stat-link .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(142,125,255,0.15); }

    .ai-hero {
        background: linear-gradient(135deg, rgba(99,102,241,0.08), rgba(168,85,247,0.05));
        border: 1px solid rgba(99,102,241,0.2);
        border-radius: 20px;
        padding: 1.25rem 1.5rem;
        margin-bottom: 1.75rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        position: relative;
        overflow: hidden;
    }
    .ai-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 200px;
        height: 200px;
        background: radial-gradient(circle, rgba(99,102,241,0.08), transparent 70%);
        pointer-events: none;
    }
    .ai-hero-left { display: flex; align-items: center; gap: 1rem; }
    .ai-hero-icon {
        width: 48px; height: 48px;
        border-radius: 14px;
        background: linear-gradient(135deg, rgba(99,102,241,0.2), rgba(168,85,247,0.15));
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .ai-hero-text h3 { font-family: 'Playfair Display', serif; font-size: 1rem; font-weight: 600; color: var(--text); margin: 0 0 2px; }
    .ai-hero-text p { font-size: .72rem; color: var(--text-muted); margin: 0; line-height: 1.4; }
    .ai-hero-btn {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .5rem 1.2rem;
        border-radius: 999px;
        border: none;
        background: linear-gradient(135deg, #6366f1, #a855f7);
        color: #fff;
        font-size: .75rem;
        font-weight: 600;
        font-family: inherit;
        cursor: pointer;
        text-decoration: none;
        white-space: nowrap;
        transition: transform .15s, box-shadow .15s;
        flex-shrink: 0;
    }
    .ai-hero-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 16px rgba(99,102,241,0.35); }
    .ai-hero-chips { display: flex; gap: .4rem; flex-wrap: wrap; margin-top: .5rem; }
    .ai-hero-chip {
        padding: .3rem .7rem;
        font-size: .65rem;
        border-radius: 999px;
        border: 1px solid rgba(99,102,241,.25);
        color: var(--text-muted);
        background: rgba(99,102,241,.06);
        cursor: pointer;
        transition: all .15s;
        text-decoration: none;
        font-family: inherit;
    }
    .ai-hero-chip:hover { background: rgba(99,102,241,.15); color: var(--text); border-color: rgba(99,102,241,.35); }

    @media (max-width: 640px) {
        .ai-hero { flex-direction: column; align-items: stretch; text-align: center; padding: 1rem; }
        .ai-hero-left { flex-direction: column; text-align: center; }
        .ai-hero-chips { justify-content: center; }
        .ai-hero-btn { justify-content: center; }
    }

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
        .stats-grid { grid-template-columns: repeat(2, 1fr); gap: .6rem; }
        .stat-card { padding: .85rem; }
        .stat-value { font-size: 1.2rem; }
        .section-title { font-size: .95rem; }
        .table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .task-table { min-width: 500px; }
        .task-table th { padding: .5rem .75rem; }
        .task-table td { padding: .55rem .75rem; }
        .view-modal { max-width: 100%; }
        .view-detail-row { flex-direction: column; gap: 0.15rem; }
    }
</style>
@endpush

@section('content')
<div class="container">

    <div class="page-title">🌙 Dashboard</div>
    <div class="page-subtitle">Welcome back, {{ $user->name }} ✦</div>

    <div class="ai-hero">
        <div class="ai-hero-left">
            <div class="ai-hero-icon">
                <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="#818cf8" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z"/></svg>
            </div>
            <div class="ai-hero-text">
                <h3>AI Productivity Assistant</h3>
                <p>Ask anything about your tasks — prioritize, break down, or get suggestions ✦</p>
            </div>
        </div>
        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:.5rem;">
            <a href="{{ url('/ai') }}" class="ai-hero-btn">
                Open AI Assistant
                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
            </a>
            <div class="ai-hero-chips">
                <a href="{{ url('/ai') }}" class="ai-hero-chip">⚡ What should I prioritize?</a>
                <a href="{{ url('/ai') }}" class="ai-hero-chip">📋 Any overdue tasks?</a>
                <a href="{{ url('/ai') }}" class="ai-hero-chip">🎯 Break down my biggest task</a>
            </div>
        </div>
    </div>

    <div class="stats-grid">
        <a href="{{ url('/data') }}" class="stat-link">
            <div class="stat-card">
                <div class="stat-icon">📚</div>
                <div class="stat-value">{{ $subjects->count() }}</div>
                <div class="stat-label">Subjects</div>
            </div>
        </a>
        <a href="{{ url('/my-tasks') }}" class="stat-link">
            <div class="stat-card">
                <div class="stat-icon">⭐</div>
                <div class="stat-value">{{ $subjects->sum('tasks_count') }}</div>
                <div class="stat-label">Total Tasks</div>
            </div>
        </a>
        <a href="{{ url('/my-tasks') }}" class="stat-link">
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-value">{{ $subjects->sum('completed_tasks_count') }}</div>
                <div class="stat-label">Completed</div>
            </div>
        </a>
        <a href="{{ url('/productivity') }}" class="stat-link">
            <div class="stat-card">
                <div class="stat-icon">🏆</div>
                <div class="stat-value">{{ $user->total_points ?? 0 }}</div>
                <div class="stat-label">Total Points</div>
            </div>
        </a>
    </div>

    @if ($todayTasks->isNotEmpty())
    <div class="section-title">⏰ Today's Tasks</div>
    <div class="card">
        <div class="table-wrap">
        <table class="task-table">
            <thead>
                <tr>
                    <th>Task</th>
                    <th>Subject</th>
                    <th>Priority</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($todayTasks as $task)
                <tr class="task-row-click" onclick="toggleModal('viewTaskModal-{{ $task->id }}')">
                    <td>
                        <div class="task-title">{{ $task->title }}</div>
                    </td>
                    <td>
                        @if ($task->subject)
                        <span class="subject-color" style="background:{{ $task->subject->color ?? '#8e7dff' }}"></span>
                        {{ $task->subject->name }}
                        @endif
                    </td>
                    <td><span class="badge badge-{{ $task->priority }}">{{ ucfirst($task->priority) }}</span></td>
                    <td><span class="badge badge-{{ $task->status }}">{{ str_replace('_', ' ', ucfirst($task->status)) }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
    @else
    <div class="section-title">⏰ Today's Tasks</div>
    <div class="card">
        <div class="no-tasks">No tasks due today. Enjoy your rest ✨</div>
    </div>
    @endif

</div>

@foreach ($todayTasks as $task)

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
                    <button type="submit" class="btn btn-success">✔ Done</button>
                </form>
                @endif
                <button onclick="event.stopPropagation();toggleModal('viewTaskModal-{{ $task->id }}');toggleModal('editTaskModal-{{ $task->id }}')" class="btn btn-blue" style="background:rgba(129,140,248,0.12);color:#818cf8;border:1px solid rgba(129,140,248,0.25);">Edit</button>
                <button onclick="event.stopPropagation();toggleModal('viewTaskModal-{{ $task->id }}');toggleModal('deleteTaskModal-{{ $task->id }}')" class="btn btn-danger">Delete</button>
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
