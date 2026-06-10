<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Invitation</title>
    <style>
        body {
            font-family: 'DM Sans', 'Segoe UI', sans-serif;
            background: #f5f3ff;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 480px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
        }
        .card {
            background: #ffffff;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 24px rgba(142, 125, 255, 0.08);
            border: 1px solid rgba(142, 125, 255, 0.12);
            text-align: center;
        }
        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.25rem;
            font-weight: 600;
            color: #1a1638;
            margin-bottom: 1rem;
        }
        .logo span { font-size: 1.4rem; margin-right: 0.3rem; }
        h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.1rem;
            color: #1a1638;
            margin: 0 0 0.5rem;
        }
        p {
            font-size: 0.85rem;
            color: #6b6580;
            line-height: 1.5;
            margin: 0 0 1.5rem;
        }
        .btn {
            display: inline-block;
            padding: 0.75rem 2rem;
            background: #8e7dff;
            color: #ffffff;
            text-decoration: none;
            border-radius: 999px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .btn:hover { background: #7b6aee; }
        .footer {
            font-size: 0.7rem;
            color: #a09bb5;
            text-align: center;
            margin-top: 1.5rem;
        }
        .task-detail {
            background: #f5f3ff;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            text-align: left;
        }
        .task-detail strong {
            color: #1a1638;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="logo"><span>🌙</span>EaseTask</div>
            <h2>Task invitation</h2>
            <p><strong>{{ $inviter->name }}</strong> has invited you to collaborate on a task.</p>
            <div class="task-detail">
                <strong>{{ $task->title }}</strong>
                @if ($task->subject)
                <br><span style="font-size:0.75rem;color:#6b6580;">{{ $task->subject->name }}</span>
                @endif
            </div>
            <a href="{{ $acceptUrl }}" class="btn">Accept Invitation ✦</a>
            <p style="margin-top:1rem;font-size:0.75rem;">If you don't have an account yet, you'll need to create one first.</p>
        </div>
        <div class="footer">&copy; {{ date('Y') }} EaseTask. All rights reserved.</div>
    </div>
</body>
</html>
