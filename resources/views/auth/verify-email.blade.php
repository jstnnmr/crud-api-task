@extends('layouts.app')
@section('title', 'Verify Email | EaseTask')

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
    .auth-success {
        background: rgba(110,231,183,0.1);
        border: 1px solid rgba(110,231,183,0.25);
        color: #6ee7b7;
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
    .field input::placeholder { color: var(--text-muted); opacity: 0.5; }
    .code-input {
        font-size: 1.5rem;
        letter-spacing: 0.5em;
        text-align: center;
        font-weight: 700;
    }
</style>
@endpush

@section('content')
<div class="auth-outer">

    <div class="auth-orb auth-orb--1"></div>
    <div class="auth-orb auth-orb--2"></div>

    <div class="auth-card">
        <div class="auth-header">
            <span class="auth-icon">📬</span>
            <h2 class="auth-title">Check your email</h2>
            <p class="auth-sub">We sent a 6-digit code to {{ $email }} ✦</p>
        </div>

        @if ($errors->any())
        <div class="auth-alert">
            {{ $errors->first() }}
        </div>
        @endif

        @if (session('status'))
        <div class="auth-success">
            {{ session('status') }} ✨
        </div>
        @endif

        <form action="/verify-email" method="POST">
            @csrf
            <input type="hidden" name="email" value="{{ $email }}">
            <div class="field">
                <label>Verification Code</label>
                <input type="text" name="code" class="code-input" placeholder="000000" maxlength="6" inputmode="numeric" pattern="[0-9]{6}" required>
            </div>
            <button type="submit" class="btn btn-primary btn-full">Verify Email ✦</button>
        </form>

        <p class="auth-footer">
            Didn't get it?
            <a href="#" onclick="event.preventDefault(); document.getElementById('resend-form').submit();">Resend code</a>
        </p>

        <form id="resend-form" action="/verify-email/resend" method="POST" style="display:none;">
            @csrf
            <input type="hidden" name="email" value="{{ $email }}">
        </form>
    </div>

</div>
@endsection
