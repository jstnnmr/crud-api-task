@extends('layouts.app')
@section('title', 'AI Assistant | EaseTask')

@push('styles')
<style>
    .ai-container { max-width: 720px; margin: 0 auto; padding: 1.5rem 2rem 2rem; display: flex; flex-direction: column; height: calc(100vh - 60px); position: relative; }
    .ai-container::before {
        content: '';
        position: fixed;
        top: 50%;
        left: 50%;
        width: 600px;
        height: 600px;
        transform: translate(-50%, -50%);
        background: radial-gradient(circle, rgba(99,102,241,0.04), transparent 60%);
        pointer-events: none;
        animation: aiAmbient 6s ease-in-out infinite alternate;
    }
    @keyframes aiAmbient {
        0% { opacity: .5; transform: translate(-50%, -50%) scale(1); }
        100% { opacity: 1; transform: translate(-50%, -50%) scale(1.15); }
    }
    .ai-header { text-align: center; margin-bottom: 1.25rem; flex-shrink: 0; position: relative; }
    .ai-title {
        font-family: 'Playfair Display', serif;
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--text);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        background: linear-gradient(135deg, #e0d7ff, #a78bfa, #818cf8);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .ai-title svg { -webkit-text-fill-color: initial; filter: drop-shadow(0 0 8px rgba(99,102,241,0.3)); }
    .ai-subtitle { font-size: .75rem; color: var(--text-muted); display: flex; align-items: center; justify-content: center; gap: .4rem; margin-top: .3rem; }
    .ai-subtitle .dot { width: 6px; height: 6px; border-radius: 50%; background: #22c55e; display: inline-block; animation: dotPulse 2s ease-in-out infinite; }
    @keyframes dotPulse { 0%,100% { opacity: 1; } 50% { opacity: .4; } }
    .ai-messages { flex: 1; overflow-y: auto; padding: .5rem 0; display: flex; flex-direction: column; gap: .75rem; scroll-behavior: smooth; }
    .ai-messages:empty { justify-content: center; align-items: center; }
    .ai-empty { text-align: center; color: var(--text-muted); }
    .ai-empty .ai-sparkle-wrap { position: relative; display: inline-block; margin-bottom: .75rem; }
    .ai-empty .ai-sparkle-wrap svg { opacity: .6; animation: sparkleFloat 3s ease-in-out infinite; }
    @keyframes sparkleFloat {
        0%, 100% { transform: translateY(0) rotate(-5deg); }
        50% { transform: translateY(-6px) rotate(5deg); }
    }
    .ai-empty p { font-size: .95rem; margin-bottom: 1.25rem; color: var(--text); }
    .chip-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .5rem; max-width: 360px; margin: 0 auto; }
    .chip {
        padding: .55rem .85rem;
        font-size: .75rem;
        border-radius: 999px;
        border: 1px solid rgba(99,102,241,.3);
        color: var(--text);
        background: rgba(99,102,241,.1);
        cursor: pointer;
        transition: all .2s;
        text-align: center;
        line-height: 1.3;
        font-family: inherit;
    }
    .chip:hover { background: rgba(99,102,241,.22); border-color: rgba(99,102,241,.45); transform: translateY(-1px); }
    .msg-row { display: flex; animation: msgIn .25s ease; }
    @keyframes msgIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
    .msg-row.user { justify-content: flex-end; }
    .msg-row.assistant { justify-content: flex-start; }
    .msg-bubble { max-width: 75%; padding: .65rem 1rem; border-radius: 18px; font-size: .88rem; line-height: 1.55; word-break: break-word; }
    .msg-row.user .msg-bubble { background: linear-gradient(135deg, #6366f1, #7c3aed); color: #fff; border-bottom-right-radius: 4px; }
    .msg-row.assistant .msg-bubble { background: var(--surface2); color: var(--text); border-bottom-left-radius: 4px; border: 1px solid var(--border); }
    .typing { display: flex; gap: 4px; padding: .65rem 1rem; background: var(--surface2); border-radius: 18px; border-bottom-left-radius: 4px; width: fit-content; border: 1px solid var(--border); }
    .typing span { width: 7px; height: 7px; border-radius: 50%; background: var(--accent); animation: bounce 1.4s infinite; }
    .typing span:nth-child(2) { animation-delay: .2s; }
    .typing span:nth-child(3) { animation-delay: .4s; }
    @keyframes bounce { 0%,60%,100% { transform: translateY(0); } 30% { transform: translateY(-7px); } }
    .ai-input-wrap { flex-shrink: 0; padding-top: .75rem; border-top: 1px solid var(--border); margin-top: .5rem; position: relative; }
    .ai-input-row { display: flex; align-items: center; gap: .5rem; background: var(--surface2); border: 1px solid var(--border); border-radius: 14px; padding: .3rem .3rem .3rem 1rem; transition: border-color .2s, box-shadow .2s; }
    .ai-input-row:focus-within { border-color: rgba(99,102,241,.4); box-shadow: 0 0 0 3px rgba(99,102,241,.08); }
    .ai-input-row input { flex: 1; background: transparent; border: none; outline: none; padding: .6rem 0; font-size: .88rem; color: var(--text); font-family: inherit; }
    .ai-input-row input::placeholder { color: var(--text-muted); }
    .ai-input-row button { width: 38px; height: 38px; border-radius: 10px; border: none; background: linear-gradient(135deg, #6366f1, #7c3aed); color: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: opacity .15s, transform .15s; flex-shrink: 0; }
    .ai-input-row button:disabled { opacity: .4; cursor: default; }
    .ai-input-row button:not(:disabled):hover { opacity: .9; transform: scale(1.05); }
    .ai-input-row button:not(:disabled):active { transform: scale(.95); }
    .ai-error { text-align: center; padding: .75rem; background: rgba(248,113,113,.1); border: 1px solid rgba(248,113,113,.2); border-radius: 10px; font-size: .78rem; color: #f87171; margin-bottom: .5rem; flex-shrink: 0; }
    .ai-glow {
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 80%;
        height: 2px;
        background: linear-gradient(90deg, transparent, rgba(99,102,241,0.3), transparent);
        border-radius: 999px;
    }

    @media (max-width: 640px) {
        .ai-container { padding: 1rem; }
        .ai-title { font-size: 1.2rem; }
        .msg-bubble { max-width: 88%; font-size: .82rem; padding: .55rem .85rem; }
        .chip-grid { grid-template-columns: 1fr; max-width: 100%; }
        .ai-input-row { padding: .2rem .2rem .2rem .75rem; }
        .ai-input-row input { font-size: .82rem; padding: .5rem 0; }
    }
    @media (min-width: 641px) and (max-width: 1024px) {
        .ai-container { padding: 1.25rem 1.5rem 1.5rem; }
    }
</style>
@endpush

@section('content')
<div class="ai-container" x-data="aiChat()">
    <div class="ai-header">
        <div class="ai-title">
            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#818cf8" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z"/></svg>
            AI Assistant
        </div>
        <div class="ai-subtitle"><span class="dot"></span> Powered by Groq</div>
    </div>

    <div x-show="error" class="ai-error" x-text="error"></div>

    <div class="ai-messages" x-ref="messages">
        <template x-if="messages.length === 0">
            <div class="ai-empty">
                <svg width="44" height="44" fill="none" viewBox="0 0 24 24" stroke="#6366f1" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z"/></svg>
                <p>How can I help with your tasks?</p>
                <div class="chip-grid">
                    <template x-for="chip in suggestions" :key="chip">
                        <button class="chip" @click="sendMessage(chip)" x-text="chip"></button>
                    </template>
                </div>
            </div>
        </template>

        <template x-for="(msg, i) in messages" :key="i">
            <div class="msg-row" :class="msg.role">
                <div class="msg-bubble" x-text="msg.content"></div>
            </div>
        </template>

        <div x-show="loading" class="msg-row assistant">
            <div class="typing"><span></span><span></span><span></span></div>
        </div>
    </div>

    <div class="ai-input-wrap">
        <form class="ai-input-row" @submit.prevent="sendMessage(input)">
            <input x-model="input" type="text" placeholder="Ask about your tasks..." :disabled="loading">
            <button type="submit" :disabled="loading || !input.trim()">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
            </button>
        </form>
        <div class="ai-glow"></div>
    </div>
</div>

<script>
function aiChat() {
    return {
        loading: false,
        input: '',
        messages: [],
        error: '',
        suggestions: [
            'What should I prioritize?',
            'Any overdue tasks?',
            'Break down my biggest task',
            'Help me write a task description'
        ],

        async sendMessage(text) {
            if (!text?.trim() || this.loading) return;
            this.error = '';
            const content = text.trim();
            this.input = '';
            this.messages.push({ role: 'user', content });
            this.loading = true;

            try {
                const res = await fetch('/ai/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('[name=_token]')?.value ?? '',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ messages: this.messages })
                });

                if (!res.ok) {
                    const text = await res.text();
                    try { const json = JSON.parse(text); throw new Error(json.error || json.message || 'Request failed'); }
                    catch (e) { if (e.message !== 'Request failed') throw e; throw new Error(text.substring(0, 100)); }
                }

                const data = await res.json();
                this.messages.push({ role: 'assistant', content: data.reply });
            } catch (e) {
                this.error = e.message;
            } finally {
                this.loading = false;
                this.$nextTick(() => {
                    const el = this.$refs.messages;
                    if (el) el.scrollTo({ top: el.scrollHeight, behavior: 'smooth' });
                });
            }
        }
    };
}
</script>
@endsection
