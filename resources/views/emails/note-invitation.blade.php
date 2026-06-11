<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Note Invitation</title>
    <style>
        body { font-family: 'DM Sans','Segoe UI',sans-serif; background: #f5f3ff; margin: 0; padding: 0; }
        .container { max-width: 480px; margin: 0 auto; padding: 2rem 1.5rem; }
        .card { background: #fff; border-radius: 16px; padding: 2rem; box-shadow: 0 4px 24px rgba(142,125,255,.08); border: 1px solid rgba(142,125,255,.12); text-align: center; }
        .logo { font-family: 'Playfair Display',serif; font-size: 1.25rem; font-weight: 600; color: #1a1638; margin-bottom: 1rem; }
        .logo span { font-size: 1.4rem; margin-right: .3rem; }
        h2 { font-family: 'Playfair Display',serif; font-size: 1.1rem; color: #1a1638; margin: 0 0 .5rem; }
        p { font-size: .85rem; color: #6b6580; line-height: 1.5; margin: 0 0 1.5rem; }
        .btn { display: inline-block; padding: .75rem 2rem; background: #8e7dff; color: #fff; text-decoration: none; border-radius: 999px; font-size: .85rem; font-weight: 500; }
        .btn:hover { background: #7b6aee; }
        .footer { font-size: .7rem; color: #a09bb5; text-align: center; margin-top: 1.5rem; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="logo"><span>🌙</span>EaseTask</div>
            <h2>Note invitation</h2>
            <p><strong>{{ $inviter->name }}</strong> has invited you to collaborate on a note.</p>
            <p style="font-size:1rem;font-weight:600;color:#1a1638;">{{ $note->title }}</p>
            <a href="{{ url('/notes') }}" class="btn">Open Notes ✦</a>
        </div>
        <div class="footer">&copy; {{ date('Y') }} EaseTask.</div>
    </div>
</body>
</html>
