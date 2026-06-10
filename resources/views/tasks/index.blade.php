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
                <tr>
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
    @endif
</div>
@endsection
