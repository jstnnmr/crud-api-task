<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Password Change</title>
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
        .code {
            display: inline-block;
            font-size: 2rem;
            font-weight: 700;
            letter-spacing: 0.3em;
            color: #8e7dff;
            background: #f5f3ff;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            border: 2px dashed #8e7dff;
            margin-bottom: 1.5rem;
        }
        .footer {
            font-size: 0.7rem;
            color: #a09bb5;
            text-align: center;
            margin-top: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="logo"><span>🌙</span>EaseTask</div>
            <h2>Confirm password change</h2>
            <p>Hi {{ $userName }},<br>Use the code below to confirm your password change request.</p>
            <div class="code">{{ $code }}</div>
            <p>This code expires in 10 minutes. If you didn't request this, you can ignore this email.</p>
        </div>
        <div class="footer">&copy; {{ date('Y') }} EaseTask. All rights reserved.</div>
    </div>
</body>
</html>
