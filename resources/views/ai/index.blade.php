@extends('layouts.app')
@section('title', 'AI Assistant | EaseTask')

@push('styles')
<style>
    .ai-layout-container { display: flex; height: calc(100vh - 60px); max-width: 1200px; margin: 0 auto; width: 100%; position: relative; }
    
    .ai-sidebar-nav { width: 260px; border-right: 1px solid var(--border); padding: 1.25rem 1rem; display: flex; flex-direction: column; gap: 1rem; flex-shrink: 0; background: var(--surface); z-index: 15; overflow-y: auto; }
    
    .ai-prune-warning { font-size: 0.72rem; color: var(--text-muted); background: var(--surface2); border: 1px dashed var(--border); padding: 0.5rem; border-radius: 8px; line-height: 1.4; display: flex; align-items: flex-start; gap: 0.35rem; }
    
    .ai-sessions-list { display: flex; flex-direction: column; gap: 0.4rem; overflow-y: auto; flex: 1; }
    
    .ai-session-item { display: flex; align-items: center; justify-content: space-between; padding: 0.55rem 0.75rem; border-radius: 10px; cursor: pointer; transition: all 0.2s; border: 1px solid transparent; background: transparent; }
    
    .ai-session-item:hover { background: var(--surface2); }
    
    .ai-session-item--active { background: rgba(99,102,241,0.08) !important; border-color: rgba(99,102,241,0.2) !important; }
    
    .ai-session-title { font-size: 0.82rem; color: var(--text); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-weight: 500; }
    
    .ai-session-item--active .ai-session-title { color: var(--accent); }
    
    .ai-session-actions { display: flex; align-items: center; gap: 0.25rem; opacity: 0; transition: opacity 0.2s; }
    
    .ai-session-item:hover .ai-session-actions, .ai-session-item--active .ai-session-actions { opacity: 1; }
    
    .ai-session-action-btn { background: transparent; border: none; padding: 0.25rem; border-radius: 4px; color: var(--text-muted); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.15s; }
    
    .ai-session-action-btn:hover { background: var(--border); color: var(--text); }
    
    .ai-session-action-btn--danger:hover { background: rgba(239,68,68,0.15); color: #ef4444; }

    .ai-mobile-menu-btn { display: none; background: var(--surface2); border: 1px solid var(--border); border-radius: 8px; padding: 0.5rem; color: var(--text); cursor: pointer; }
    
    .ai-sidebar-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 12; }

    .typing-dot-pulse {
        width: 6px; height: 6px; border-radius: 50%;
        background-color: var(--accent);
        animation: typingDotPulse 1s infinite alternate;
    }
    
    @keyframes typingDotPulse {
        from { transform: scale(0.8); opacity: 0.5; }
        to { transform: scale(1.2); opacity: 1; }
    }

    @keyframes msgSlideIn {
        from { opacity: 0; transform: translateY(6px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .ai-row { animation: msgSlideIn 0.2s ease; }

    @media (max-width: 768px) {
        .ai-sidebar-nav { position: fixed; left: 0; top: 60px; bottom: 0; transform: translateX(-100%); transition: transform 0.3s; z-index: 15; }
        .ai-sidebar-nav--open { transform: translateX(0); }
        .ai-sidebar-backdrop { display: block; }
        .ai-mobile-menu-btn { display: block; }
    }
</style>
@endpush

@section('content')
<div class="ai-layout-container" x-data="aiChat()" x-init="init()">
    <!-- Sidebar -->
    <div class="ai-sidebar-nav" :class="{ 'ai-sidebar-nav--open': sidebarOpen }">
        <button class="btn btn-primary w-full" @click="createNewSession()" :disabled="isLoading">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="margin-right: 4px; display: inline-block; vertical-align: middle;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            New Chat
        </button>
        
        <div class="ai-prune-warning">
            <span>💡</span>
            <span>Sessions last for 30 days of inactivity. You can "Fork" to clone a chat context.</span>
        </div>

        <hr style="border-top: 1px solid var(--border); margin: 0.5rem 0;" />

        <div class="ai-sessions-list">
            <template x-for="sess in sessions" :key="sess.id">
                <div class="ai-session-item" :class="{ 'ai-session-item--active': activeSessionId === sess.id }">
                    <div class="ai-session-item-content" style="flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 2px;" @click="loadSession(sess.id)">
                        <div class="ai-session-title" x-text="sess.title || 'Untitled Chat'"></div>
                        <div class="ai-session-time" style="font-size: 0.68rem; color: var(--text-muted);" x-text="formatRelativeTime(sess.updated_at)"></div>
                    </div>
                    
                    <template x-if="confirmingDeleteSessionId !== sess.id">
                        <div class="ai-session-actions">
                            <button class="ai-session-action-btn" @click.stop="forkSession(sess.id)" title="Fork/Clone Session" :disabled="isLoading">
                                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h6M8 7h4m0 0l-3-3m3 3l-3 3M16 12l3-3m0 0l-3-3m3 3H10" /></svg>
                            </button>
                            <button class="ai-session-action-btn ai-session-action-btn--danger" @click.stop="confirmingDeleteSessionId = sess.id" title="Delete Session" :disabled="isLoading">
                                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </div>
                    </template>
                    
                    <template x-if="confirmingDeleteSessionId === sess.id">
                        <div class="ai-session-actions" style="opacity: 1; display: flex; gap: 4px; align-items: center;">
                            <button class="ai-session-action-btn ai-session-action-btn--danger" @click.stop="deleteSession(sess.id)" title="Confirm Delete" style="font-size: 0.72rem; padding: 2px 6px; border: 1px solid rgba(239,68,68,0.4); border-radius: 4px; background: rgba(239,68,68,0.1);">Delete</button>
                            <button class="ai-session-action-btn" @click.stop="confirmingDeleteSessionId = null" title="Cancel Delete" style="font-size: 0.72rem; padding: 2px 6px; border: 1px solid var(--border); border-radius: 4px;">Cancel</button>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </div>
    
    <div class="ai-sidebar-backdrop" x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak></div>

    <!-- Main Panel -->
    <div class="ai-panel" style="flex: 1; border-radius: 0; border: none;">
        <!-- Context Strip -->
        @include('ai.components.context-strip')

        <!-- Messages -->
        <div class="ai-messages-panel" x-ref="messages">
            <template x-if="messages.length === 0 && !isLoading">
                <div class="ai-empty-state">
                    <svg width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="#7F77DD" stroke-width="1" style="margin: 0 auto 0.75rem;"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z"/></svg>
                    <p>Ask about your tasks, or try one of these:</p>
                    <div class="ai-chip-grid">
                        <template x-for="chip in suggestions" :key="chip">
                            <button class="ai-chip" @click="sendMessage(chip)" :disabled="isLoading" x-text="chip"></button>
                        </template>
                    </div>
                </div>
            </template>

            <template x-if="messages.length === 0 && isLoading">
                <div style="padding: 1rem;">
                    <div class="ai-skeleton-row" style="width: 50%;"></div>
                    <div class="ai-skeleton-row" style="width: 70%; animation-delay: 0.15s;"></div>
                    <div class="ai-skeleton-row" style="width: 40%; animation-delay: 0.3s;"></div>
                </div>
            </template>

            @include('ai.components.message-row')

            <div x-show="isLoading && messages.length > 0" class="ai-row__assistant" style="padding: 6px 16px 6px 34px;">
                <div style="display: flex; gap: 6px; align-items: center; padding: 4px 0;">
                    <span class="typing-dot-pulse"></span>
                    <span class="typing-dot-pulse" style="animation-delay: 0.2s;"></span>
                    <span class="typing-dot-pulse" style="animation-delay: 0.4s;"></span>
                </div>
            </div>
        </div>

        <!-- Nudge Banner -->
        <template x-if="showNudge">
            <div class="ai-nudge-banner">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                Try asking about your tasks, schedule, or productivity stats.
                <button @click="showNudge = false">
                    <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </template>

        <!-- Command Input -->
        @include('ai.components.command-input')
    </div>
</div>

<script>
function aiChat() {
    return {
        sessions: @json($sessions),
        activeSessionId: null,
        isLoading: false,
        fabLoading: false,
        sidebarOpen: false,
        input: '',
        messages: [],
        error: '',
        confirmingDeleteSessionId: null,
        showNudge: false,
        offTopicAttempts: 0,
        reader: null,
        suggestions: [
            'What should I prioritize?',
            'Any overdue tasks?',
            'Break down my biggest task',
            'Help me write a task description'
        ],

        init() {
            const savedId = localStorage.getItem('active_ai_session_id');
            if (savedId) {
                const sId = parseInt(savedId);
                if (this.sessions.some(s => s.id === sId)) {
                    this.loadSession(sId, false);
                } else if (this.sessions.length > 0) {
                    this.loadSession(this.sessions[0].id, true);
                } else {
                    this.createNewSession();
                }
            } else if (this.sessions.length > 0) {
                this.loadSession(this.sessions[0].id, true);
            } else {
                this.createNewSession();
            }

            window.addEventListener('storage', (e) => {
                if (e.key === 'active_ai_session_id') {
                    const sId = parseInt(e.newValue);
                    if (sId && sId !== this.activeSessionId) {
                        this.refreshSessionsList().then(() => {
                            if (this.sessions.some(s => s.id === sId)) {
                                this.loadSession(sId, false);
                            }
                        });
                    }
                }
            });
        },

        async loadSession(id, setStorage = true) {
            this.activeSessionId = id;
            this.confirmingDeleteSessionId = null;
            this.sidebarOpen = false;
            this.isLoading = true;
            this.error = '';
            this.messages = [];
            this.showNudge = false;
            
            if (setStorage) {
                localStorage.setItem('active_ai_session_id', id);
            }

            try {
                const res = await fetch(`/ai/sessions/${id}`);
                if (!res.ok) throw new Error('Failed to load session messages');
                const data = await res.json();
                this.messages = data.messages || [];
                if (data.context) {
                    Alpine.store('aiContext').update(data.context);
                }
                
                const isGenerating = res.headers.get('X-Assistant-Generating') === '1';
                if (isGenerating) {
                    this.pollSessionForAssistantReply(id);
                }
            } catch (e) {
                this.error = e.message;
            } finally {
                this.isLoading = false;
                this.scrollToBottom();
            }
        },

        async pollSessionForAssistantReply(id, attempt = 1) {
            if (attempt > 30 || this.activeSessionId !== id) return;
            setTimeout(async () => {
                try {
                    const res = await fetch(`/ai/sessions/${id}`);
                    if (res.ok) {
                        const data = await res.json();
                        const isGenerating = res.headers.get('X-Assistant-Generating') === '1';
                        
                        if ((data.messages?.length > this.messages.length) || !isGenerating) {
                            this.messages = data.messages || [];
                            if (data.context) {
                                Alpine.store('aiContext').update(data.context);
                            }
                            this.scrollToBottom();
                            await this.refreshSessionsList();
                        }
                        
                        if (isGenerating && this.activeSessionId === id) {
                            this.pollSessionForAssistantReply(id, attempt + 1);
                        }
                    }
                } catch (e) {
                    console.error('Failed to poll session status:', e);
                }
            }, 2000);
        },

        async createNewSession() {
            this.isLoading = true;
            this.error = '';
            this.sidebarOpen = false;
            this.showNudge = false;
            this.messages = [];
            try {
                const res = await fetch('/ai/sessions', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('[name=_token]')?.value ?? '',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ title: 'New Chat' })
                });
                if (!res.ok) throw new Error('Failed to create session');
                const session = await res.json();
                
                this.sessions.unshift(session);
                this.activeSessionId = session.id;
                localStorage.setItem('active_ai_session_id', session.id);
            } catch (e) {
                this.error = e.message;
            } finally {
                this.isLoading = false;
                this.scrollToBottom();
            }
        },

        async deleteSession(id) {
            this.isLoading = true;
            this.error = '';
            try {
                const res = await fetch(`/ai/sessions/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('[name=_token]')?.value ?? '',
                        'Accept': 'application/json'
                    }
                });
                if (!res.ok) throw new Error('Failed to delete session');
                
                this.sessions = this.sessions.filter(s => s.id !== id);
                this.confirmingDeleteSessionId = null;

                if (this.activeSessionId === id) {
                    localStorage.removeItem('active_ai_session_id');
                    if (this.sessions.length > 0) {
                        this.loadSession(this.sessions[0].id, true);
                    } else {
                        this.createNewSession();
                    }
                }
            } catch (e) {
                this.error = e.message;
            } finally {
                this.isLoading = false;
            }
        },

        async forkSession(id) {
            this.isLoading = true;
            this.error = '';
            try {
                const res = await fetch(`/ai/sessions/${id}/fork`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('[name=_token]')?.value ?? '',
                        'Accept': 'application/json'
                    }
                });
                if (!res.ok) throw new Error('Failed to fork session');
                const newSession = await res.json();
                
                await this.refreshSessionsList();
                await this.loadSession(newSession.id, true);
            } catch (e) {
                this.error = e.message;
                this.isLoading = false;
            }
        },

        async refreshSessionsList() {
            try {
                const res = await fetch('/ai/sessions');
                if (res.ok) {
                    this.sessions = await res.json();
                }
            } catch (e) {
                console.error('Failed to refresh sessions:', e);
            }
        },

        async sendMessage(text) {
            if (!text?.trim() || this.isLoading) return;
            this.error = '';
            this.showNudge = false;
            const content = text.trim();

            const offTopicKeywords = ['task', 'todo', 'overdue', 'schedule', 'priority', 'subject', 'streak', 'productivity', 'points', 'due', 'completion', 'organi'];
            const isTaskRelated = offTopicKeywords.some(keyword => content.toLowerCase().includes(keyword));

            if (!isTaskRelated) {
                this.offTopicAttempts++;
                if (this.offTopicAttempts >= 2) {
                    this.showNudge = true;
                    return;
                }
            } else {
                this.offTopicAttempts = 0;
            }

            this.input = '';
            this.messages.push({ role: 'user', content });
            this.isLoading = true;
            this.scrollToBottom();

            let msgIdx;

            try {
                const csrfToken = document.querySelector('[name=_token]')?.value ?? '';
                const response = await fetch('/ai/chat/stream', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'text/event-stream'
                    },
                    body: JSON.stringify({
                        session_id: this.activeSessionId,
                        message: content
                    })
                });

                if (!response.ok) {
                    const errorMsg = await response.text();
                    throw new Error(errorMsg || 'Failed to stream response');
                }

                const newSessionId = response.headers.get('X-Session-Id');
                const newSessionTitle = response.headers.get('X-Session-Title');
                const aiContextHeader = response.headers.get('X-Ai-Context');

                if (aiContextHeader) {
                    try {
                        Alpine.store('aiContext').update(JSON.parse(aiContextHeader));
                    } catch (e) {}
                }

                if (newSessionId) {
                    const sId = parseInt(newSessionId);
                    if (sId !== this.activeSessionId) {
                        this.activeSessionId = sId;
                        localStorage.setItem('active_ai_session_id', sId);
                        
                        if (!this.sessions.some(s => s.id === sId)) {
                            this.sessions.unshift({
                                id: sId,
                                title: newSessionTitle || 'New Chat',
                                updated_at: new Date().toISOString()
                            });
                        }
                    } else if (newSessionTitle) {
                        const session = this.sessions.find(s => s.id === sId);
                        if (session) {
                            session.title = newSessionTitle;
                        }
                    }
                }

                // Push a placeholder assistant message
                msgIdx = this.messages.length;
                this.messages.push({ role: 'assistant', content: '', displayContent: '', subtasks: null, parentTaskTitle: null });

                this.reader = response.body.getReader();
                const decoder = new TextDecoder();

                while (true) {
                    const { done, value } = await this.reader.read();
                    if (done) break;

                    const chunk = decoder.decode(value, { stream: true });
                    const msg = this.messages[msgIdx];
                    msg.content += chunk;

                    // Strip fence during streaming for display
                    const fenceMatch = msg.content.match(/```subtasks/);
                    if (fenceMatch) {
                        msg.displayContent = msg.content.slice(0, fenceMatch.index).trim();
                    } else {
                        msg.displayContent = msg.content;
                    }
                    this.scrollToBottom();
                }

                // On stream complete, fetch parsed version
                await this.onStreamComplete(msgIdx, this.activeSessionId);
                await this.refreshSessionsList();

            } catch (e) {
                if (e.name === 'AbortError' || e.message.includes('cancel')) {
                    console.log('Stream request aborted by user.');
                    // Replace blank placeholder with cancellation notice
                    const placeholder = this.messages[msgIdx];
                    if (placeholder && !placeholder.content) {
                        placeholder.content = 'Response generation was stopped.';
                        placeholder.displayContent = 'Response generation was stopped.';
                    }
                } else {
                    // Replace the placeholder instead of pushing a duplicate
                    this.messages[msgIdx] = {
                        role: 'assistant',
                        content: 'Sorry, something went wrong: ' + e.message,
                        displayContent: 'Sorry, something went wrong: ' + e.message,
                        isError: true,
                        failedPrompt: content
                    };
                }
            } finally {
                this.isLoading = false;
                this.reader = null;
                this.scrollToBottom();
            }
        },

        async onStreamComplete(messageIndex, sessionId) {
            try {
                const res = await fetch(`/ai/sessions/${sessionId}/messages/latest`);
                const msg = this.messages[messageIndex];
                if (!res.ok) {
                    if (msg && !msg.content.trim()) {
                        msg.displayContent = 'I couldn\'t generate a response. Please try rephrasing your question.';
                    }
                    return;
                }
                const data = await res.json();
                if (msg) {
                    msg.displayContent = data.displayContent || data.content;
                    msg.subtasks = data.subtasks || null;
                    msg.parentTaskTitle = data.parentTaskTitle || null;
                    msg.isRefusal = data.content === 'I can only help with your tasks and productivity.';
                    msg.id = data.id;
                }
            } catch (e) {
                console.error('Failed to fetch parsed message:', e);
            }
        },

        async acceptSubtasks(index) {
            const msg = this.messages[index];
            if (!msg?.subtasks?.length) return;
            msg.acceptingSubtasks = true;
            msg.acceptError = null;

            try {
                const res = await fetch('/ai/subtasks/accept', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('[name=_token]')?.value ?? '',
                    },
                    body: JSON.stringify({
                        session_id: this.activeSessionId,
                        message_id: msg.id,
                        parent_task_title: msg.parentTaskTitle,
                        subtasks: msg.subtasks,
                    })
                });

                if (!res.ok) {
                    const err = await res.json().catch(() => null);
                    throw new Error(err?.message || 'Could not add subtasks.');
                }

                const data = await res.json();
                msg.subtasksAccepted = true;
                Alpine.store('aiContext').update(data.context);
            } catch (e) {
                msg.acceptError = e.message;
            } finally {
                msg.acceptingSubtasks = false;
            }
        },

        stopGenerating() {
            if (this.reader) {
                this.reader.cancel();
                this.isLoading = false;
                this.reader = null;
            }
        },

        renderMarkdown(content) {
            if (!content) return '';
            const html = marked.parse(content, { breaks: true });
            return DOMPurify.sanitize(html);
        },

        formatRelativeTime(dateStr) {
            if (!dateStr) return '';
            const date = new Date(dateStr);
            const now = new Date();
            const diffMs = now - date;
            if (isNaN(date.getTime()) || diffMs < 0) return 'Just now';
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMins / 60);
            const diffDays = Math.floor(diffHours / 24);
            if (diffMins < 1) return 'Just now';
            if (diffMins < 60) return `${diffMins}m ago`;
            if (diffHours < 24) return `${diffHours}h ago`;
            if (diffDays === 1) return 'Yesterday';
            if (diffDays < 7) return `${diffDays}d ago`;
            return date.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const el = this.$refs.messages;
                if (el) el.scrollTo({ top: el.scrollHeight, behavior: 'smooth' });
            });
        }
    };
}
</script>
@endsection
