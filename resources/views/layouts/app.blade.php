<!DOCTYPE html>
<html lang="en" id="html-root">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/coquette.css', 'resources/js/app.js'])
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@9.0.3/dist/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@9.0.3/dist/umd/simple-datatables.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    @stack('styles')
    <title>@yield('title', 'Lumina Tasks ✦')</title>
</head>
<body>

<nav class="gnav">
    <a class="gnav-logo" href="{{ url('/') }}">
        <span class="gnav-moon">🌙</span>
        Lumina Tasks
    </a>

    <div class="gnav-links">
        <a href="{{ url('/users') }}" class="gnav-link {{ request()->is('users*') ? 'gnav-link--active' : '' }}">
            Dashboard
        </a>
        <a href="#" class="gnav-link">My Tasks</a>
        <a href="#" class="gnav-link">Team</a>
    </div>

    <div class="gnav-right">
        <span class="gnav-badge">✦ Task Tracker</span>

        @auth
        <form action="{{ url('/logout') }}" method="POST" style="display:inline">
            @csrf
            <button type="submit" class="gnav-btn">
                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"/></svg>
                Logout
            </button>
        </form>
        @endauth

        <button class="gnav-btn gnav-theme-btn" id="themeToggle" onclick="toggleTheme()" aria-label="Toggle theme">
            <span id="themeIcon">☀️</span>
            <span id="themeLabel">Light</span>
        </button>
    </div>
</nav>

<main>
    @yield('content')
</main>

<style>
    .gnav {
        position: sticky;
        top: 0;
        z-index: 100;
        height: 56px;
        padding: 0 2rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        background: rgba(26, 22, 56, 0.85);
        backdrop-filter: blur(18px);
        border-bottom: 1px solid var(--border);
    }
    .light-mode .gnav {
        background: rgba(255, 255, 255, 0.85);
    }
    .gnav-logo {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-family: 'Playfair Display', serif;
        font-size: 1rem;
        font-weight: 600;
        color: var(--text);
        text-decoration: none;
        white-space: nowrap;
    }
    .gnav-moon {
        display: inline-block;
        font-size: 1.2rem;
        animation: moonFloat 4s ease-in-out infinite;
    }
    @keyframes moonFloat {
        0%, 100% { transform: translateY(0) rotate(-5deg); }
        50%       { transform: translateY(-3px) rotate(5deg); }
    }
    .gnav-links {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        margin-left: 1rem;
        flex: 1;
    }
    .gnav-link {
        padding: 0.3rem 0.8rem;
        border-radius: 999px;
        font-size: 0.75rem;
        color: var(--text-muted);
        text-decoration: none;
        transition: all 0.15s;
    }
    .gnav-link:hover,
    .gnav-link--active {
        background: rgba(142, 125, 255, 0.15);
        color: var(--accent);
    }
    .gnav-right {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-left: auto;
    }
    .gnav-badge {
        font-size: 0.6rem;
        padding: 2px 10px;
        border-radius: 999px;
        border: 1px solid var(--border);
        background: rgba(142, 125, 255, 0.12);
        color: var(--accent);
        letter-spacing: 0.1em;
        text-transform: uppercase;
        white-space: nowrap;
    }
    .gnav-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.3rem 0.8rem;
        border-radius: 999px;
        border: 1px solid var(--border);
        background: transparent;
        color: var(--text-muted);
        font-size: 0.72rem;
        font-family: 'DM Sans', sans-serif;
        cursor: pointer;
        transition: all 0.15s;
        white-space: nowrap;
    }
    .gnav-btn:hover {
        background: rgba(142, 125, 255, 0.12);
        color: var(--accent);
        border-color: var(--accent);
    }
</style>

<script>
    const root = document.getElementById('html-root');
    const themeIcon  = document.getElementById('themeIcon');
    const themeLabel = document.getElementById('themeLabel');

    if (localStorage.getItem('theme') === 'light') {
        root.classList.add('light-mode');
        themeIcon.textContent  = '🌙';
        themeLabel.textContent = 'Dark';
    }

    function toggleTheme() {
        const isLight = root.classList.toggle('light-mode');
        themeIcon.textContent  = isLight ? '🌙' : '☀️';
        themeLabel.textContent = isLight ? 'Dark' : 'Light';
        localStorage.setItem('theme', isLight ? 'light' : 'dark');
    }
</script>

</body>
</html>