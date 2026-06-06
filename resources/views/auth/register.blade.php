@extends('layouts.app')
@section('title', 'Create Account | Lumina Tasks')

@push('styles')
<style>
    .auth-outer {
        min-height: calc(100vh - 56px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        position: relative;
        overflow: hidden;
    }
    .auth-orb {
        position: absolute;
        border-radius: 50%;
        pointer-events: none;
    }
    .auth-orb--1 {
        width: 320px; height: 320px;
        background: radial-gradient(circle, rgba(142,125,255,0.15) 0%, transparent 70%);
        top: -80px; right: -80px;
    }
    .auth-orb--2 {
        width: 240px; height: 240px;
        background: radial-gradient(circle, rgba(255,117,160,0.1) 0%, transparent 70%);
        bottom: -60px; left: -60px;
    }
    .auth-header {
        text-align: center;
        margin-bottom: 1.75rem;
    }
    .auth-icon {
        display: block;
        font-size: 2.2rem;
        margin-bottom: 0.6rem;
        animation: starPulse 3s ease-in-out infinite;
    }
    @keyframes starPulse {
        0%, 100% { transform: scale(1) rotate(-5deg); }
        50%       { transform: scale(1.1) rotate(5deg); }
    }
    .auth-title {
        font-size: 1.4rem;
        color: var(--text);
        margin-bottom: 0.3rem;
    }
    .auth-sub {
        font-size: 0.75rem;
        color: var(--text-muted);
        letter-spacing: 0.05em;
    }
    .auth-alert {
        background: rgba(255,117,160,0.1);
        border: 1px solid rgba(255,117,160,0.3);
        color: var(--rose-deep);
        padding: 0.6rem 1rem;
        border-radius: 10px;
        font-size: 0.75rem;
        margin-bottom: 1rem;
        text-align: center;
    }
    .auth-footer {
        text-align: center;
        margin-top: 1.25rem;
        font-size: 0.75rem;
        color: var(--text-muted);
    }
    .auth-footer a {
        color: var(--accent);
        text-decoration: none;
    }
    .auth-footer a:hover { text-decoration: underline; }
    .field input {
        background: var(--surface2);
        color: var(--text);
        border-color: var(--border);
    }
    .field input::placeholder { color: var(--text-muted); opacity: 0.6; }
    .field input:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(142,125,255,0.15);
    }
</style>
@endpush

@section('content')
<div class="auth-outer">

    <div class="auth-orb auth-orb--1"></div>
    <div class="auth-orb auth-orb--2"></div>

    <div class="auth-card">
        <div class="auth-header">
            <span class="auth-icon">⭐</span>
            <h2 class="auth-title">Join the cosmos</h2>
            <p class="auth-sub">Create your account ✦</p>
        </div>

        @if($errors->any())
        <div class="auth-alert">
            {{ $errors->first() }}
        </div>
        @endif

        <form action="/register" method="POST">
            @csrf
            <div class="field">
                <label>Name</label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="Your name" required>
            </div>
            <div class="field">
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="you@cosmos.io" required>
            </div>
            <div class="field">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-full">Create Account ✦</button>
        </form>

        <p class="auth-footer">Already have an account? <a href="/login">Sign in</a></p>
    </div>

</div>
@endsection