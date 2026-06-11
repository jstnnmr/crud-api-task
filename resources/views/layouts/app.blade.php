<!DOCTYPE html>
<html lang="en" id="html-root">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/coquette.css', 'resources/js/app.js'])
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@9.0.3/dist/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@9.0.3/dist/umd/simple-datatables.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
    @stack('styles')
    <title>@yield('title', 'EaseTask ✦')</title>
</head>
<body>

<nav class="gnav" role="navigation" aria-label="Main navigation">
    <a class="gnav-logo" href="{{ url('/') }}" aria-label="Home">
        <span class="gnav-moon" aria-hidden="true">🌙</span>
        EaseTask
    </a>

    <button class="gnav-hamburger" onclick="toggleMobileMenu()" aria-label="Toggle menu">
        <span></span><span></span><span></span>
    </button>

    <div class="gnav-links" role="menubar">
        <a href="{{ route('subjects.data') }}" class="gnav-link {{ request()->is('/') || request()->is('data') ? 'gnav-link--active' : '' }}" role="menuitem">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            Subjects
        </a>
        <a href="{{ url('/my-tasks') }}" class="gnav-link {{ request()->is('my-tasks') ? 'gnav-link--active' : '' }}" role="menuitem">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            My Tasks
        </a>
        <a href="{{ url('/team') }}" class="gnav-link {{ request()->is('team') ? 'gnav-link--active' : '' }}" role="menuitem">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
            Team
        </a>
        <a href="{{ url('/ai') }}" class="gnav-link {{ request()->is('ai') ? 'gnav-link--active' : '' }}" role="menuitem">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z"/></svg>
            AI
        </a>
        <a href="{{ url('/notes') }}" class="gnav-link {{ request()->is('notes') ? 'gnav-link--active' : '' }}" role="menuitem">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Notes
        </a>
        <a href="{{ url('/productivity') }}" class="gnav-link {{ request()->is('productivity') ? 'gnav-link--active' : '' }}" role="menuitem">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
            Stats
        </a>
    </div>

    <div class="gnav-right">
        @auth
        <a href="{{ url('/account') }}" class="gnav-btn" aria-label="Account settings">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            <span class="gnav-text">Account</span>
        </a>
        <form action="{{ url('/logout') }}" method="POST" style="display:inline">
            @csrf
            <button type="submit" class="gnav-btn" aria-label="Log out">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"/></svg>
                <span class="gnav-text">Logout</span>
            </button>
        </form>
        @endauth

        <button class="gnav-btn gnav-theme-btn" id="themeToggle" onclick="toggleTheme()" aria-label="Toggle theme">
            <span id="themeIcon" aria-hidden="true">☀️</span>
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
        height: 60px;
        padding: 0 1.5rem;
        display: flex;
        align-items: center;
        gap: .75rem;
        background: rgba(26, 22, 56, 0.88);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border-bottom: 1px solid var(--border);
    }
    .light-mode .gnav {
        background: rgba(255, 255, 255, 0.9);
    }
    .gnav-logo {
        display: flex;
        align-items: center;
        gap: .5rem;
        font-family: 'Playfair Display', serif;
        font-size: 1.05rem;
        font-weight: 600;
        color: var(--text);
        text-decoration: none;
        white-space: nowrap;
        min-height: 44px;
    }
    .gnav-moon {
        display: inline-block;
        font-size: 1.25rem;
        animation: moonFloat 4s ease-in-out infinite;
    }
    @keyframes moonFloat {
        0%, 100% { transform: translateY(0) rotate(-5deg); }
        50%       { transform: translateY(-3px) rotate(5deg); }
    }
    .gnav-hamburger {
        display: none;
        flex-direction: column;
        justify-content: center;
        gap: 5px;
        width: 44px;
        height: 44px;
        padding: 10px;
        background: none;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        cursor: pointer;
        margin-left: auto;
    }
    .gnav-hamburger span {
        display: block;
        width: 100%;
        height: 2px;
        background: var(--text);
        border-radius: 2px;
        transition: transform .2s, opacity .2s;
    }
    .gnav.menu-open .gnav-hamburger span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
    .gnav.menu-open .gnav-hamburger span:nth-child(2) { opacity: 0; }
    .gnav.menu-open .gnav-hamburger span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }
    .gnav-links {
        display: flex;
        align-items: center;
        gap: .2rem;
        margin-left: .75rem;
        flex: 1;
    }
    .gnav-link {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .5rem .85rem;
        min-height: 44px;
        border-radius: var(--radius-full);
        font-size: .78rem;
        font-weight: 500;
        color: var(--text-muted);
        text-decoration: none;
        transition: background .15s, color .15s;
    }
    .gnav-link:hover {
        background: rgba(142, 125, 255, 0.12);
        color: var(--accent);
    }
    .gnav-link--active {
        background: rgba(142, 125, 255, 0.15);
        color: var(--accent);
    }
    .gnav-right {
        display: flex;
        align-items: center;
        gap: .4rem;
        margin-left: auto;
    }
    .gnav-btn {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .4rem .9rem;
        min-height: 44px;
        border-radius: var(--radius-full);
        border: 1px solid var(--border);
        background: transparent;
        color: var(--text-muted);
        font-size: .75rem;
        font-weight: 500;
        font-family: 'DM Sans', sans-serif;
        cursor: pointer;
        transition: all .15s;
        white-space: nowrap;
        text-decoration: none;
    }
    .gnav-btn:hover {
        background: rgba(142, 125, 255, 0.1);
        color: var(--accent);
        border-color: var(--accent);
    }
    .gnav-divider {
        display: none;
        height: 1px;
        background: var(--border);
        margin: .5rem 0;
    }
    @media (max-width: 640px) {
        .gnav { padding: 0 1rem; gap: .5rem; }
        .gnav-hamburger { display: flex; }
        .gnav-links {
            display: none;
            position: absolute;
            top: 60px;
            left: 0;
            right: 0;
            flex-direction: column;
            align-items: stretch;
            gap: 0;
            margin-left: 0;
            padding: .75rem 1rem;
            background: rgba(26, 22, 56, 0.96);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
            box-shadow: var(--shadow-md);
            animation: menuSlide .2s ease;
        }
        .light-mode .gnav-links {
            background: rgba(255, 255, 255, 0.96);
        }
        @keyframes menuSlide {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .gnav.menu-open .gnav-links { display: flex; }
        .gnav-link {
            padding: .75rem 1rem;
            min-height: 44px;
            border-radius: var(--radius-sm);
            font-size: .85rem;
        }
        .gnav-link svg { width: 16px; height: 16px; }
        .gnav-right { display: none; }
        .gnav.menu-open .gnav-right {
            display: flex;
            flex-direction: column;
            align-items: stretch;
            gap: .35rem;
            margin-left: 0;
            padding: 0 1rem .75rem;
            position: absolute;
            top: calc(60px + var(--menu-links-height, 0px));
            left: 0;
            right: 0;
            background: rgba(26, 22, 56, 0.96);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
        }
        .light-mode .gnav.menu-open .gnav-right {
            background: rgba(255, 255, 255, 0.96);
        }
        .gnav.menu-open .gnav-right .gnav-btn {
            justify-content: flex-start;
            padding: .65rem 1rem;
            border-radius: var(--radius-sm);
            font-size: .85rem;
        }
        .gnav.menu-open .gnav-right .gnav-text { display: inline; }
        .gnav.menu-open .gnav-right .gnav-divider { display: block; }
        .gnav-logo { font-size: .95rem; }
        .gnav-moon { font-size: 1.1rem; }
    }
    @media (min-width: 641px) and (max-width: 1024px) {
        .gnav { padding: 0 1rem; gap: .5rem; }
        .gnav-link { padding: .45rem .65rem; font-size: .74rem; }
        .gnav-btn { padding: .35rem .75rem; font-size: .72rem; }
    }
</style>

<style>
.confetti-container {
    position: fixed; top: 0; left: 0;
    width: 100%; height: 100%;
    pointer-events: none; z-index: 9999;
    overflow: hidden;
}
.confetti-piece {
    position: absolute; top: -10px;
    animation: confettiFall linear forwards;
}
@keyframes confettiFall {
    0%   { transform: translateY(0) rotate(0deg); opacity: 1; }
    100% { transform: translateY(100vh) rotate(720deg); opacity: 0; }
}
html.light-mode body {
    background: radial-gradient(circle at top right, #f0ecff, #faf8ff) !important;
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

    function toggleMobileMenu() {
        const nav = document.querySelector('.gnav');
        const isOpen = nav.classList.toggle('menu-open');
        if (isOpen) {
            const links = document.querySelector('.gnav-links');
            const linksHeight = links.scrollHeight;
            nav.style.setProperty('--menu-links-height', linksHeight + 'px');
            document.querySelector('.gnav-right').style.top = (60 + linksHeight) + 'px';
        }
    }

    document.addEventListener('click', function(e) {
        const nav = document.querySelector('.gnav');
        if (nav.classList.contains('menu-open') && !nav.contains(e.target)) {
            nav.classList.remove('menu-open');
        }
    });

    document.querySelectorAll('.gnav-link').forEach(function(link) {
        link.addEventListener('click', function() {
            document.querySelector('.gnav').classList.remove('menu-open');
        });
    });

    function triggerConfetti() {
        const colors = ['#8e7dff', '#ff75a0', '#6ee7b7', '#fbbf24', '#818cf8', '#f87171', '#f97316'];
        const container = document.createElement('div');
        container.className = 'confetti-container';
        for (let i = 0; i < 60; i++) {
            const piece = document.createElement('div');
            piece.className = 'confetti-piece';
            piece.style.left = Math.random() * 100 + '%';
            piece.style.background = colors[Math.floor(Math.random() * colors.length)];
            piece.style.width = (Math.random() * 6 + 5) + 'px';
            piece.style.height = (Math.random() * 6 + 5) + 'px';
            piece.style.borderRadius = Math.random() > 0.5 ? '50%' : '2px';
            piece.style.animationDelay = Math.random() * 1.5 + 's';
            piece.style.animationDuration = (Math.random() * 2 + 2) + 's';
            container.appendChild(piece);
        }
        document.body.appendChild(container);
        setTimeout(() => container.remove(), 4000);
    }

    @if(session('confetti'))
    document.addEventListener('DOMContentLoaded', triggerConfetti);
    @endif
</script>

</body>
</html>