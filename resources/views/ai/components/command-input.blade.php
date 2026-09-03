<div class="ai-command-bar">
    <span class="ai-command-bar__glyph">&gt;</span>
    <input
        type="text"
        x-model="input"
        @keydown.enter="!isLoading && sendMessage(input)"
        :disabled="isLoading"
        placeholder="Ask about your tasks..."
        class="ai-command-bar__input"
    />
    <template x-if="!isLoading">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="var(--ai-text-dim)" stroke-width="2" class="ai-command-bar__hint"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    </template>
    <template x-if="isLoading">
        <button class="ai-command-bar__stop" @click="stopGenerating()">
            <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24"><rect x="6" y="6" width="12" height="12" rx="1"/></svg>
            Stop
        </button>
    </template>
</div>
