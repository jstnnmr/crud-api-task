document.addEventListener('alpine:init', () => {
    Alpine.store('aiContext', {
        overdue: 0,
        due_today: 0,
        streak: 0,

        init(initial) {
            this.overdue = initial.overdue;
            this.due_today = initial.due_today;
            this.streak = initial.streak;
        },

        update(partial) {
            if (typeof partial.overdue === 'number') this.overdue = partial.overdue;
            if (typeof partial.due_today === 'number') this.due_today = partial.due_today;
            if (typeof partial.streak === 'number') this.streak = partial.streak;
        }
    });
});
