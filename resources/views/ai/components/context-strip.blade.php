<div class="ai-context-strip" x-data x-init="$store.aiContext.init({{ json_encode($aiContext ?? ['overdue' => 0, 'due_today' => 0, 'streak' => 0]) }})">
    <div class="ai-context-cell">
        <div class="ai-context-label">Overdue</div>
        <div class="ai-context-value ai-context-value--coral" x-text="$store.aiContext.overdue"></div>
    </div>
    <div class="ai-context-cell">
        <div class="ai-context-label">Today</div>
        <div class="ai-context-value ai-context-value--purple" x-text="$store.aiContext.due_today"></div>
    </div>
    <div class="ai-context-cell">
        <div class="ai-context-label">Streak</div>
        <div class="ai-context-value ai-context-value--teal" x-text="$store.aiContext.streak + 'd'"></div>
    </div>
</div>
