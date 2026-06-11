<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Reminder</title>
    <style>
        body {
            font-family: 'DM Sans', 'Segoe UI', sans-serif;
            background: #f5f3ff;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 520px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
        }
        .card {
            background: #ffffff;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 4px 24px rgba(142, 125, 255, 0.08);
            border: 1px solid rgba(142, 125, 255, 0.12);
        }
        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.3rem;
            font-weight: 600;
            color: #1a1638;
            margin-bottom: 0.25rem;
            text-align: center;
        }
        .logo span { font-size: 1.5rem; margin-right: 0.3rem; }
        .greeting {
            font-size: 0.9rem;
            color: #1a1638;
            margin: 1.25rem 0 0.25rem;
        }
        .intro {
            font-size: 0.82rem;
            color: #6b6580;
            line-height: 1.5;
            margin: 0 0 1.25rem;
        }
        .task-card {
            background: #f8f7ff;
            border: 1px solid rgba(142, 125, 255, 0.15);
            border-radius: 14px;
            padding: 1rem 1.15rem;
            margin-bottom: 0.75rem;
        }
        .task-card:last-child { margin-bottom: 0; }
        .task-title {
            font-family: 'Playfair Display', serif;
            font-size: 0.92rem;
            font-weight: 600;
            color: #1a1638;
            margin-bottom: 0.35rem;
        }
        .task-meta {
            font-size: 0.72rem;
            color: #6b6580;
            line-height: 1.6;
        }
        .task-meta strong { color: #1a1638; font-weight: 600; }
        .badge {
            display: inline-block;
            padding: 1px 7px;
            border-radius: 999px;
            font-size: 0.62rem;
            font-weight: 600;
        }
        .badge-low { background: rgba(110,231,183,0.15); color: #059669; }
        .badge-medium { background: rgba(251,191,36,0.15); color: #b45309; }
        .badge-high { background: rgba(248,113,113,0.15); color: #dc2626; }
        .badge-pending { background: rgba(251,191,36,0.1); color: #b45309; }
        .badge-in_progress { background: rgba(110,231,183,0.1); color: #059669; }
        .btn-wrap { text-align: center; margin-top: 1.5rem; }
        .btn {
            display: inline-block;
            padding: 0.7rem 2rem;
            background: linear-gradient(135deg, #6366f1, #8e7dff);
            color: #ffffff;
            text-decoration: none;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 500;
        }
        .btn:hover { opacity: 0.9; }
        .footer {
            font-size: 0.68rem;
            color: #a09bb5;
            text-align: center;
            margin-top: 1.5rem;
        }
        hr { border: none; border-top: 1px solid rgba(142,125,255,0.1); margin: 1.25rem 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="logo"><span>🌙</span>EaseTask</div>
            <hr>
            <div class="greeting">Hi {{ $userName }},</div>
            <p class="intro">
                You have <strong>{{ $tasks->count() }}</strong> task(s) that need attention:
            </p>

            @foreach ($tasks as $task)
            <div class="task-card">
                <div class="task-title">{{ $task->title }}</div>
                <div class="task-meta">
                    @if ($task->subject)
                        <strong>Subject:</strong> {{ $task->subject->name }}<br>
                    @endif
                    @if ($task->category)
                        <strong>Category:</strong> {{ $task->category->name }}<br>
                    @endif
                    <strong>Priority:</strong> <span class="badge badge-{{ $task->priority }}">{{ ucfirst($task->priority) }}</span><br>
                    <strong>Status:</strong> <span class="badge badge-{{ $task->status }}">{{ str_replace('_', ' ', ucfirst($task->status)) }}</span><br>
                    <strong>Due:</strong> {{ \Carbon\Carbon::parse($task->due_date)->format('M j, Y') }}
                    @if ($task->due_date->isToday())
                        <span style="color:#dc2626;font-weight:600;"> — Due Today!</span>
                    @elseif ($task->due_date->isTomorrow())
                        <span style="color:#b45309;font-weight:600;"> — Due Tomorrow</span>
                    @elseif ($task->due_date->isPast())
                        <span style="color:#dc2626;font-weight:600;"> — Overdue!</span>
                    @endif
                    @if ($task->description)
                        <br><em style="color:#6b6580;">{{ $task->description }}</em>
                    @endif
                </div>
            </div>
            @endforeach

            <div class="btn-wrap">
                <a href="{{ url('/my-tasks') }}" class="btn">View My Tasks ✦</a>
            </div>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} EaseTask. All rights reserved.<br>
            <span style="font-size:0.62rem;">This is an automated reminder from your productivity assistant.</span>
        </div>
    </div>
</body>
</html>
