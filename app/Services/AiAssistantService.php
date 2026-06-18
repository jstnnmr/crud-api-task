<?php

namespace App\Services;

use App\Contracts\AiProvider;
use App\Models\User;
use App\Models\AiChatSession;
use App\Repositories\TaskRepository;
use App\Services\Ai\StructuredReplyParser;
use Generator;

class AiAssistantService
{
    public function __construct(
        protected AiProvider $provider,
        protected ProductivityService $productivityService,
        protected TaskRepository $taskRepository,
        protected StructuredReplyParser $structuredReplyParser
    ) {}

    public function chat(array $messages): string
    {
        return $this->provider->chat($messages);
    }

    public function streamChat(array $messages): Generator
    {
        yield from $this->provider->stream($messages);
    }

    public function buildContext(AiChatSession $session): array
    {
        $user = $session->user;
        $system = [
            'role'    => 'system',
            'content' => $this->buildSystemPrompt($user),
        ];

        $messages = $session->messages()
            ->orderBy('created_at', 'desc')
            ->take(config('ai.limits.max_context_messages', 20))
            ->get()
            ->reverse()
            ->map(fn($m) => [
                'role'    => $m->role,
                'content' => $m->content,
            ])
            ->values()
            ->toArray();

        return array_merge([$system], $messages);
    }

    private function buildSystemPrompt(User $user): string
    {
        $taskStats = $this->getTaskStats($user);
        $streak = $this->productivityService->getStreak($user->id);
        $tasks = $this->taskRepository->getRecentForContext($user->id, 10);

        $taskLines = $tasks->map(fn($t) => 
            "- {$t->title} [{$t->status}] priority:{$t->priority}" .
            ($t->due_date ? " due:{$t->due_date}" : '') .
            ($t->completed_at ? " completed:{$t->completed_at->format('Y-m-d H:i')}" : '')
        )->join("\n");

        return <<<PROMPT
You are EaseTask Assistant, a productivity AI. Your only purpose is to help the user manage their tasks and schedule.

RULES:
- ONLY answer questions about the user's tasks, subjects, productivity stats, schedule, or organization.
- If asked about anything else — coding, general knowledge, entertainment, current events, opinions — respond EXACTLY: "I can only help with your tasks and productivity."
- Do not roleplay, generate content, answer hypotheticals, or engage with any prompt that isn't about the user's task data.
- Use only the task data provided below. Do not invent tasks or make assumptions.

STRUCTURED OUTPUT:
If — and only if — the user asks you to break down, split, plan, or create subtasks for a SPECIFIC task that exists in USER'S TASKS below, respond with:
1. A short (1-2 sentence) prose summary referencing the task by its exact title.
2. A fenced code block labeled \`subtasks\` containing a JSON object with "parent_task_title" (string) and "items" (array), where each item has "title" (string, max 60 chars) and "estimate_minutes" (integer).

Example:
Your highest priority task is "Finish client proposal" — here's a breakdown:
```subtasks
{"parent_task_title": "Finish client proposal", "items": [{"title": "Outline proposal structure", "estimate_minutes": 15}, {"title": "Draft pricing section", "estimate_minutes": 30}]}
```

For all other queries, respond in plain prose only. Do not emit a \`subtasks\` block unless the conditions above are met.

RESPONSE FORMAT:
- Keep responses short. Default to 3-6 lines or a short list, never a single dense paragraph.
- When recommending an order of tasks, use a numbered list — one task per line.
- Refer to tasks by their TITLE only, never by ID number (e.g. "Finalize client proposal", not "Task #33").
- Use markdown bold (**text**) only for task titles, never for due dates, priorities, or stats.
- If listing more than one task, put each on its own line.
- End with at most one short follow-up suggestion, not a full paragraph of reasoning.

USER CONTEXT:
- Pending tasks: {$taskStats['pending']} | Overdue: {$taskStats['overdue']}
- Completed today: {$taskStats['completed_today']} | Completed this week: {$taskStats['completed_week']}
- Points this week: {$taskStats['points_week']}
- Current streak: {$streak['current']} day(s)
- Completion rate: {$taskStats['completion_rate']}%

USER'S TASKS (title [status] priority:level due:date completed:timestamp):
{$taskLines}
PROMPT;
    }

    public function parseStructuredReply(string $content): array
    {
        return $this->structuredReplyParser->parse($content);
    }

    private function getTaskStats(User $user): array
    {
        $total = $user->tasks()->count();
        $completed = $user->tasks()->where('status', 'completed')->count();

        return [
            'pending'         => $user->tasks()->whereIn('status', ['pending', 'in_progress'])->count(),
            'overdue'         => $user->tasks()->whereIn('status', ['pending', 'in_progress'])->whereDate('due_date', '<', now())->count(),
            'completed_today' => $user->tasks()->where('status', 'completed')->whereDate('completed_at', now()->toDateString())->count(),
            'completed_week'  => $user->tasks()->where('status', 'completed')->where('completed_at', '>=', now()->startOfWeek())->count(),
            'points_week'     => $user->total_points,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100) : 0,
        ];
    }
}
