@extends('layouts.app')
@section('title', 'My Account | EaseTask')

@push('styles')
<style>
    .container {
        max-width: 600px;
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
    .card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 20px;
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    .card-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--border);
        background: var(--surface2);
        font-family: 'Playfair Display', serif;
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--text);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .card-body {
        padding: 1.25rem;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .field label {
        display: block;
        font-size: 0.64rem;
        letter-spacing: 0.09em;
        text-transform: uppercase;
        color: var(--text-muted);
        margin-bottom: 0.38rem;
    }
    .field input {
        width: 100%;
        background: var(--surface2);
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 0.58rem 0.9rem;
        color: var(--text);
        font-family: 'DM Sans', sans-serif;
        font-size: 0.8rem;
        outline: none;
        transition: border-color 0.15s;
    }
    .field input:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(142,125,255,0.12);
    }
    .field input::placeholder { color: var(--text-muted); opacity: 0.5; }
    .photo-section {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.75rem;
        padding-bottom: 0.5rem;
    }
    .photo-preview {
        width: 96px;
        height: 96px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid var(--accent);
        background: var(--surface2);
    }
    .photo-placeholder {
        width: 96px;
        height: 96px;
        border-radius: 50%;
        background: var(--surface2);
        border: 3px dashed var(--border);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: var(--text-muted);
    }
    .photo-actions {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }
    .flash {
        background: rgba(110,231,183,0.1);
        border: 1px solid rgba(110,231,183,0.25);
        color: #6ee7b7;
        padding: 0.75rem 1.25rem;
        border-radius: 12px;
        font-size: 0.8rem;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .alert {
        background: rgba(248,113,113,0.1);
        border: 1px solid rgba(248,113,113,0.25);
        color: #f87171;
        padding: 0.6rem 1rem;
        border-radius: 10px;
        font-size: 0.75rem;
        margin-bottom: 1rem;
        text-align: center;
    }
    .field .err {
        color: #f87171;
        font-size: 0.68rem;
        margin-top: 4px;
    }
    .file-input-wrapper {
        position: relative;
        overflow: hidden;
        display: inline-block;
    }
    .file-input-wrapper input[type=file] {
        position: absolute;
        left: 0;
        top: 0;
        opacity: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
    }
    .modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 200;
        align-items: center;
        justify-content: center;
        background: rgba(15,12,41,0.75);
        backdrop-filter: blur(6px);
    }
    .modal-overlay.active { display: flex; }
    .modal {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 20px;
        width: 100%;
        max-width: 400px;
        margin: 1rem;
        box-shadow: 0 24px 60px var(--shadow);
        animation: modalIn 0.2s ease;
    }
    @keyframes modalIn {
        from { opacity: 0; transform: translateY(10px) scale(0.97); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }
    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1.1rem 1.4rem;
        border-bottom: 1px solid var(--border);
        background: var(--surface2);
        border-radius: 18px 18px 0 0;
    }
    .modal-title {
        font-family: 'Playfair Display', serif;
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--text);
    }
    .modal-close {
        background: none;
        border: none;
        color: var(--text-muted);
        font-size: 1.2rem;
        cursor: pointer;
        line-height: 1;
        padding: 2px 6px;
        border-radius: 4px;
    }
    .modal-close:hover { color: var(--text); }
    .modal-body { padding: 1.4rem; display: flex; flex-direction: column; gap: 1rem; }
    .modal-footer { padding: 0 1.4rem 1.4rem; display: flex; flex-direction: column; gap: 0.55rem; }
    .code-input {
        font-size: 1.5rem;
        letter-spacing: 0.5em;
        text-align: center;
        font-weight: 700;
    }

    @media (max-width: 640px) {
        .container { padding: 1rem; }
        .page-title { font-size: 1.3rem; }
        .card-header { font-size: .85rem; padding: .75rem 1rem; }
        .card-body { padding: 1rem; gap: .75rem; }
    }
    @media (min-width: 641px) and (max-width: 1024px) {
        .container { max-width: 520px; }
    }
</style>
@endpush

@section('content')
<div class="container">

    <div class="page-title">🌙 My Account</div>
    <div class="page-subtitle">Manage your profile ✦</div>

    @if (session('status'))
    <div class="flash">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('status') }} ✨
    </div>
    @endif

    @if($errors->any())
    <div class="alert">{{ $errors->first() }}</div>
    @endif

    <div class="card">
        <div class="card-header">📸 Profile Photo</div>
        <div class="card-body">
            <div class="photo-section">
                @if ($account['photo'])
                <img src="{{ $account['photo'] }}" alt="Profile photo" class="photo-preview">
                @else
                <div class="photo-placeholder">🌙</div>
                @endif
                <form action="{{ url('/account/photo') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="photo-actions">
                        <span class="btn btn-ghost file-input-wrapper">
                            Choose File
                            <input type="file" name="photo" accept="image/*" onchange="this.form.submit()">
                        </span>
                        <span style="font-size:0.65rem;color:var(--text-muted);">JPG, PNG, WEBP up to 2MB</span>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">✏️ Profile Information</div>
        <div class="card-body">
            <form action="{{ url('/account/profile') }}" method="POST">
                @csrf
                <div class="field">
                    <label>Name</label>
                    <input type="text" name="name" value="{{ $account['name'] }}" required>
                </div>
                <div class="field">
                    <label>Email</label>
                    <input type="email" name="email" value="{{ $account['email'] }}" required>
                </div>
                <button type="submit" class="btn btn-primary btn-full">Save Changes ✦</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">🔒 Change Password</div>
        <div class="card-body">
            <p style="font-size:0.72rem;color:var(--text-muted);margin-bottom:0.5rem;">
                A verification code will be sent to your email before the change takes effect.
            </p>
            <form action="{{ url('/account/password') }}" method="POST">
                @csrf
                <div class="field">
                    <label>Current Password</label>
                    <input type="password" name="current_password" placeholder="Enter current password" required>
                </div>
                <div class="field">
                    <label>New Password</label>
                    <input type="password" name="new_password" id="new_password" placeholder="Min. 6 characters" required>
                </div>
                <div class="field">
                    <label>Confirm New Password</label>
                    <input type="password" name="new_password_confirmation" placeholder="Repeat new password" required>
                </div>
                <button type="submit" class="btn btn-success btn-full">Send Verification Code ✦</button>
            </form>
        </div>
    </div>

</div>

<div id="verifyCodeModal" class="modal-overlay {{ session('status') && str_contains(session('status'), 'verification code') ? 'active' : '' }}">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">🔑 Enter Verification Code</span>
            <button class="modal-close" onclick="toggleModal('verifyCodeModal')">&times;</button>
        </div>
        <form action="{{ url('/account/password/confirm') }}" method="POST">
            @csrf
            <div class="modal-body">
                <p style="font-size:0.75rem;color:var(--text-muted);">A 6-digit code was sent to your email. Enter it below along with your new password.</p>
                <div class="field">
                    <label>Verification Code</label>
                    <input type="text" name="code" class="code-input" placeholder="000000" maxlength="6" inputmode="numeric" pattern="[0-9]{6}" required>
                </div>
                <div class="field">
                    <label>New Password</label>
                    <input type="password" name="new_password" placeholder="Min. 6 characters" required>
                </div>
                <div class="field">
                    <label>Confirm New Password</label>
                    <input type="password" name="new_password_confirmation" placeholder="Repeat new password" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success btn-full">Confirm &amp; Update ✦</button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleModal(id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.toggle('active');
    }
</script>
@endsection
