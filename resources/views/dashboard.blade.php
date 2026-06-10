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
</style>
@endpush

@section('content')
<div class="container">

    <div class="page-title">🌙 Dashboard</div>
    <div class="page-subtitle">Welcome back, {{ $user->name }} ✦</div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📚</div>
            <div class="stat-value">{{ $subjects->count() }}</div>
            <div class="stat-label">Subjects</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">⭐</div>
            <div class="stat-value">{{ $subjects->sum('tasks_count') }}</div>
            <div class="stat-label">Total Tasks</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-value">{{ $subjects->sum('completed_tasks_count') }}</div>
            <div class="stat-label">Completed</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🏆</div>
            <div class="stat-value">{{ $user->total_points ?? 0 }}</div>
            <div class="stat-label">Total Points</div>
        </div>
    </div>

    @if ($todayTasks->isNotEmpty())
    <div class="section-title">⏰ Today's Tasks</div>
    <div class="card">
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
                <tr>
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
    @else
    <div class="section-title">⏰ Today's Tasks</div>
    <div class="card">
        <div class="no-tasks">No tasks due today. Enjoy your rest ✨</div>
    </div>
    @endif

</div>
@endsection
