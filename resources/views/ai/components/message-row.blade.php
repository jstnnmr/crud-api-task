<template x-for="(message, index) in messages" :key="index">
    <div class="ai-row">
        <template x-if="message.role === 'user'">
            <div class="ai-row__user">
                <span class="ai-row__prompt-glyph">&gt;</span>
                <span x-text="message.content"></span>
            </div>
        </template>

        <template x-if="message.role === 'assistant'">
            <div class="ai-row__assistant" :class="{ 'ai-row__assistant--refusal': message.isRefusal }">
                <div class="ai-message-content" x-html="renderMarkdown(message.displayContent || message.content)"></div>

                <template x-if="message.subtasks && message.subtasks.length">
                    <div class="ai-subtask-list">
                        <template x-for="(task, i) in message.subtasks" :key="i">
                            <div class="ai-subtask-row">
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0; color: var(--ai-accent);"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span x-text="task.title" class="ai-subtask-row__title"></span>
                                <span x-show="task.estimate_minutes" x-text="task.estimate_minutes + 'm'" class="ai-subtask-row__time"></span>
                            </div>
                        </template>
                    </div>
                </template>

                <template x-if="message.subtasks && message.subtasks.length && !message.subtasksAccepted">
                    <button
                        class="ai-action-btn"
                        :disabled="message.acceptingSubtasks"
                        @click="acceptSubtasks(index)"
                    >
                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        <span x-text="message.acceptingSubtasks ? 'Adding...' : 'Add as subtasks'"></span>
                    </button>
                </template>

                <template x-if="message.subtasksAccepted">
                    <div class="ai-action-confirm">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#5DCAA5" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Added to your tasks
                    </div>
                </template>
            </div>
        </template>

        <template x-if="message.isError">
            <div class="ai-row__error">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#F0997B" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span x-text="message.content"></span>
                <button @click="retryMessage(message.failedPrompt, index)" style="margin-left:auto; background:transparent; border:0.5px solid rgba(240,153,123,0.3); border-radius:6px; padding:2px 8px; color:#F0997B; font-size:11px; cursor:pointer;">
                    <svg width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="display:inline; vertical-align:middle;"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Retry
                </button>
            </div>
        </template>
    </div>
</template>
