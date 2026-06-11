<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
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
        }
        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.25rem;
            font-weight: 600;
            color: #1a1638;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .logo span {
            display: inline-block;
            font-size: 1.4rem;
            margin-right: 0.3rem;
        }
        h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.1rem;
            color: #1a1638;
            margin: 0 0 0.5rem;
            text-align: center;
        }
        p {
            font-size: 0.85rem;
            color: #6b6580;
            line-height: 1.5;
            text-align: center;
            margin: 0 0 1.5rem;
        }
        .btn {
            display: block;
            width: fit-content;
            margin: 0 auto;
            padding: 0.75rem 2rem;
            background: #8e7dff;
            color: #ffffff;
            text-decoration: none;
            border-radius: 999px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .btn:hover {
            background: #7b6aee;
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
            <h2>Password Reset Request</h2>
            <p>We received a request to reset your password. Click the button below to choose a new one.</p>
            <a href="{{ $resetUrl }}" class="btn">Reset Password ✦</a>
            <p style="margin-top: 1rem;">If you didn't request this, you can safely ignore this email.</p>
        </div>
        <div class="footer">&copy; {{ date('Y') }} EaseTask. All rights reserved.</div>
    </div>
</body>
</html>
