# EaseTask AI — Panel Redesign Implementation Plan

Replaces the gradient chat-bubble UI with a context-anchored side panel: live task stats strip, command-style input, and structured "breakdown" replies that convert directly into subtasks.

This plan assumes the Phase 1-4 backend refactor (provider abstraction, session persistence, streaming, scope enforcement) is already in place per `AI_REFACTOR_PLAN.md`, and the gap fixes from the QA review have been applied (IDOR scoping in `prepareConversation`, refusal cache scoped per-user, generating-cache TTL handling, etc).

---

## Phase Overview

| Phase | Focus | Effort |
|---|---|---|
| 5 | Context strip data pipeline | ~35 min |
| 6 | Structured breakdown responses (JSON mode) | ~50 min |
| 7 | Panel UI — Blade + Alpine rewrite | ~70 min |
| 8 | Command-input behavior & subtask conversion | ~40 min |
| 9 | FAB → compact panel adaptation | ~30 min |

---

## Phase 5 — Context Strip Data Pipeline

**Goal:** The three live numbers (overdue, today, streak) render on initial page load and stay current without a full page refresh, across both the panel and FAB.

### Steps

**5.1 — Create `app/Http/Resources/AiContextResource.php`**

A single resource shape reused by the page load, session endpoints, and stream response headers — one source of truth for "what does the context strip show right now."

```php
class AiContextResource extends JsonResource
{
    public static function fromUser(User $user, ProductivityService $productivity): array
    {
        $today = now()->toDateString();

        return [
            'overdue' => $user->tasks()
                ->whereIn('status', ['pending', 'in_progress'])
                ->whereDate('due_date', '<', $today)
                ->count(),
            'due_today' => $user->tasks()
                ->whereIn('status', ['pending', 'in_progress'])
                ->whereDate('due_date', '=', $today)
                ->count(),
            'streak' => $productivity->getStreak($user->id)['current'],
        ];
    }
}
```

**5.2 — Modify `AiAssistantController::index()`**

Pass `AiContextResource::fromUser($user, $productivity)` to the view as `$aiContext`, used for the initial server-rendered strip (no flash of zeroes on load).

**5.3 — Modify `AiAssistantController::showSession()`**

Include `context` in the JSON response alongside `messages` — every session switch refreshes the strip, since switching sessions is already a round trip.

```php
return response()->json([
    'messages' => $messages,
    'generating' => Cache::get("session_{$session->id}_generating", false),
    'context' => AiContextResource::fromUser(auth()->user(), $this->productivityService),
]);
```

**5.4 — Modify the streaming response headers (Phase 3's `stream()` method)**

After the stream completes and the assistant message is persisted, append a final non-content header carrying refreshed context — since a "break down my task" interaction is likely to change task counts (e.g. if the user accepts subtasks mid-conversation via a separate request).

```php
return response()->stream(function () use (...) {
    // ...existing token loop...
}, 200, [
    'Content-Type' => 'text/event-stream',
    'Cache-Control' => 'no-cache',
    'X-Accel-Buffering' => 'no',
    'X-Session-Id' => $session->id,
    'X-Session-Title' => $newTitle ?? '',
    'X-Ai-Context' => json_encode(AiContextResource::fromUser(auth()->user(), $this->productivityService)),
]);
```

> Note: `X-Ai-Context` is read by the frontend in Phase 7. Keep this payload small — three integers as JSON is ~40 bytes, well within header size limits.

**5.5 — Cache invalidation hook**

Wherever a task's `status` or `due_date` changes outside the AI module (the main task CRUD controller), no action needed — `AiContextResource::fromUser()` queries live data on every call. The only "staleness" risk is the in-memory strip on the client between requests, which 5.3/5.4 cover.

---

## Phase 6 — Structured Breakdown Responses (JSON Mode)

**Goal:** When the user asks for a task breakdown, the model returns parseable subtask data — not just prose — so the "Add as subtasks" button in the new UI has something to act on.

### Approach

Rather than a separate endpoint, detect breakdown-shaped requests via the **system prompt** (consistent with the existing "no keyword pre-filter" philosophy from Phase 4) and ask the model to emit a fenced JSON block alongside its prose. The frontend extracts the block; the prose remains the visible reply.

### Steps

**6.1 — Extend `buildSystemPrompt()` in `AiAssistantService`**

Add a structured-output instruction block:

```php
private function buildSystemPrompt(): string
{
    // ...existing context-building (taskStats, streak, tasks)...

    return <<<PROMPT
You are EaseTask Assistant, a productivity AI. Your only purpose is to help the user manage their tasks and schedule.

RULES:
- ONLY answer questions about the user's tasks, subjects, productivity stats, schedule, or organization.
- If asked about anything else, respond EXACTLY: "I can only help with your tasks and productivity."
- Use only the task data provided below. Do not invent tasks or make assumptions.

STRUCTURED OUTPUT:
If — and only if — the user asks you to break down, split, plan, or create subtasks for a SPECIFIC task that exists in USER'S TASKS below, respond with:
1. A short (1-2 sentence) prose summary referencing the task by its exact title.
2. A fenced code block labeled \`subtasks\` containing a JSON array, where each item has "title" (string, max 60 chars) and "estimate_minutes" (integer).

Example:
Your highest priority task is "Finish client proposal" — here's a breakdown:
```subtasks
[{"title": "Outline proposal structure", "estimate_minutes": 15}, {"title": "Draft pricing section", "estimate_minutes": 30}]
```

For all other queries, respond in plain prose only. Do not emit a \`subtasks\` block unless the conditions above are met.

USER CONTEXT:
...
PROMPT;
}

```

**6.2 — Create `app/Services/Ai/StructuredReplyParser.php`**

Server-side extraction so the frontend never has to regex the raw stream — the parser runs once after the full assistant message is assembled (post-stream, before persistence).

```php
class StructuredReplyParser
{
    /**
     * Splits a raw assistant reply into display prose and an optional
     * subtasks payload. Returns ['prose' => string, 'subtasks' => array|null].
     */
    public function parse(string $raw): array
    {
        if (!preg_match('/```subtasks\s*(\[.*?\])\s*```/s', $raw, $matches)) {
            return ['prose' => trim($raw), 'subtasks' => null];
        }

        $prose = trim(str_replace($matches[0], '', $raw));
        $subtasks = json_decode($matches[1], true);

        if (!is_array($subtasks) || json_last_error() !== JSON_ERROR_NONE) {
            // Malformed JSON from the model — degrade to prose-only,
            // strip the broken fence so it doesn't render as a code block
            return ['prose' => $prose, 'subtasks' => null];
        }

        // Validate shape; drop malformed entries rather than failing the whole reply
        $subtasks = array_values(array_filter(array_map(function ($item) {
            if (!isset($item['title']) || !is_string($item['title'])) {
                return null;
            }
            return [
                'title' => mb_substr($item['title'], 0, 60),
                'estimate_minutes' => isset($item['estimate_minutes']) && is_int($item['estimate_minutes'])
                    ? max(1, min(480, $item['estimate_minutes']))
                    : null,
            ];
        }, $subtasks)));

        return ['prose' => $prose, 'subtasks' => $subtasks ?: null];
    }
}
```

**6.3 — Modify the persistence step (both `chat()` and `stream()`)**

Store the **raw** reply in `ai_chat_messages.content` (so conversation context fed back to the model on the next turn includes the fence — the model may reference its own prior breakdown). Store the **parsed** result separately for rendering.

Add a column:

```php
// migration: add_metadata_to_ai_chat_messages
Schema::table('ai_chat_messages', function (Blueprint $table) {
    $table->json('metadata')->nullable()->after('content');
});
```

```php
$parsed = $this->structuredReplyParser->parse($assistantContent);

$this->chatRepository->addMessage($session->id, 'assistant', $assistantContent, [
    'subtasks' => $parsed['subtasks'],
]);
```

`AiChatRepository::addMessage()` signature becomes `addMessage(int $sessionId, string $role, string $content, ?array $metadata = null)`.

**6.4 — Response payload includes parsed prose separately**

For the non-stream `chat()` endpoint:

```php
return response()->json([
    'reply' => $parsed['prose'],
    'subtasks' => $parsed['subtasks'],
    'session_id' => $session->id,
]);
```

For `stream()`: the raw stream still sends tokens as-is (including the eventual `\`\`\`subtasks` fence as it streams in). The frontend is responsible for detecting and hiding the fence once it starts arriving — see 7.4. The **final** persisted/parsed version is what's returned on subsequent `showSession()` loads, so a page refresh always shows the clean card UI, never raw JSON.

**6.5 — New endpoint: convert subtasks to real tasks**

```php
Route::post('/ai/subtasks/accept', [AiAssistantController::class, 'acceptSubtasks'])
    ->middleware('throttle:30,1');
```

```php
public function acceptSubtasks(Request $request)
{
    $validated = $request->validate([
        'session_id' => 'required|exists:ai_chat_sessions,id',
        'message_id' => 'required|exists:ai_chat_messages,id',
        'parent_task_title' => 'required|string|max:255',
        'subtasks' => 'required|array|min:1|max:20',
        'subtasks.*.title' => 'required|string|max:60',
        'subtasks.*.estimate_minutes' => 'nullable|integer|min:1|max:480',
    ]);

    $session = $this->chatRepository->findSession($validated['session_id'], auth()->id());
    abort_if(!$session, 404);

    $message = $session->messages()->find($validated['message_id']);
    abort_if(!$message, 404);

    $parentTask = auth()->user()->tasks()
        ->where('title', $validated['parent_task_title'])
        ->first();

    abort_if(!$parentTask, 404, 'Referenced task no longer exists.');

    $created = collect($validated['subtasks'])->map(function ($item) use ($parentTask) {
        return $parentTask->subtasks()->create([
            'title' => $item['title'],
            'estimate_minutes' => $item['estimate_minutes'] ?? null,
            'status' => 'pending',
        ]);
    });

    return response()->json([
        'created' => $created->count(),
        'context' => AiContextResource::fromUser(auth()->user(), $this->productivityService),
    ]);
}
```

> **Note:** This assumes a `subtasks` relation/table exists on `Task`. If subtasks are out of scope for the current data model, this endpoint instead creates sibling `Task` rows with a `parent_task_id` — adjust based on actual schema. Either way, the 404 on "Referenced task no longer exists" matters: the breakdown was generated from a snapshot of the user's tasks at prompt-build time, and the task may have been completed/deleted before the user clicks "Add as subtasks."

---

## Phase 7 — Panel UI: Blade + Alpine Rewrite

**Goal:** Replace `resources/views/ai/index.blade.php`'s gradient-bubble layout with the dark-panel, context-strip, annotated-row design.

### Steps

**7.1 — New component structure**

```
resources/views/ai/
├── index.blade.php           (page shell, sidebar + panel)
├── components/
│   ├── context-strip.blade.php
│   ├── message-row.blade.php
│   ├── subtask-card.blade.php
│   └── command-input.blade.php
```

**7.2 — `components/context-strip.blade.php`**

Server-rendered on first load using `$aiContext` (Phase 5.2), then JS-updated thereafter.

```blade
<div class="ai-context-strip" x-data x-init="$store.aiContext.init({{ json_encode($aiContext) }})">
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
```

**7.3 — Alpine store for shared context** (`resources/js/ai-store.js`)

A dedicated store (separate from the chat component) so the FAB (Phase 9) and main panel both read/write the same numbers without prop drilling.

```javascript
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
```

**7.4 — `components/message-row.blade.php`**

Replaces bubble rendering. User messages get the `>` prefix; assistant messages render prose + optional subtask cards.

```blade
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
                <p x-text="message.displayContent || message.content"></p>

                <template x-if="message.subtasks && message.subtasks.length">
                    <div class="ai-subtask-list">
                        <template x-for="(task, i) in message.subtasks" :key="i">
                            <div class="ai-subtask-row">
                                <i class="ti ti-square" aria-hidden="true"></i>
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
                        <i class="ti ti-plus" aria-hidden="true"></i>
                        <span x-text="message.acceptingSubtasks ? 'Adding...' : 'Add as subtasks'"></span>
                    </button>
                </template>

                <template x-if="message.subtasksAccepted">
                    <div class="ai-action-confirm">
                        <i class="ti ti-check" aria-hidden="true"></i> Added to your tasks
                    </div>
                </template>
            </div>
        </template>

        <template x-if="message.isError">
            <div class="ai-row__error">
                <i class="ti ti-alert-triangle" aria-hidden="true"></i>
                <span x-text="message.content"></span>
                <button @click="retryMessage(message.failedPrompt, index)">
                    <i class="ti ti-refresh" aria-hidden="true"></i> Retry
                </button>
            </div>
        </template>
    </div>
</template>
```

**7.5 — `components/command-input.blade.php`**

```blade
<div class="ai-command-bar">
    <span class="ai-command-bar__glyph">&gt;</span>
    <input
        type="text"
        x-model="input"
        @keydown.enter="!isLoading && sendMessage()"
        :disabled="isLoading"
        placeholder="Ask about your tasks..."
        class="ai-command-bar__input"
    />
    <template x-if="!isLoading">
        <i class="ti ti-corner-down-left ai-command-bar__hint" aria-hidden="true"></i>
    </template>
    <template x-if="isLoading">
        <button class="ai-command-bar__stop" @click="stopStream()">
            <i class="ti ti-player-pause" aria-hidden="true"></i> Stop
        </button>
    </template>
</div>
```

**7.6 — CSS additions** (`resources/css/ai-panel.css`)

Flat dark surfaces per the redesign mockup — no gradients. Key custom properties (scoped to `.ai-panel`, not global, to avoid bleeding into the rest of EaseTask's existing theme):

```css
.ai-panel {
    --ai-bg: #1a1530;
    --ai-border: rgba(127, 119, 221, 0.25);
    --ai-border-soft: rgba(127, 119, 221, 0.18);
    --ai-text: #E8E6F7;
    --ai-text-muted: #9C97C4;
    --ai-text-dim: #6E699C;
    --ai-accent: #7F77DD;
    --ai-accent-soft: rgba(127, 119, 221, 0.15);

    background: var(--ai-bg);
    border: 0.5px solid var(--ai-border);
    border-radius: var(--border-radius-lg);
    color: var(--ai-text);
    font-family: var(--font-sans);
}

.ai-context-strip {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1px;
    background: var(--ai-border-soft);
    border-bottom: 0.5px solid var(--ai-border-soft);
}

.ai-context-cell {
    background: var(--ai-bg);
    padding: 10px 12px;
}

.ai-context-label {
    font-size: 11px;
    color: var(--ai-text-muted);
    margin-bottom: 2px;
}

.ai-context-value {
    font-size: 18px;
    font-weight: 500;
}

.ai-context-value--coral { color: #F0997B; }
.ai-context-value--purple { color: #AFA9EC; }
.ai-context-value--teal { color: #5DCAA5; }

.ai-row__user {
    display: flex;
    gap: 8px;
    align-items: flex-start;
    font-size: 13px;
    color: var(--ai-text);
    padding: 6px 16px;
}

.ai-row__prompt-glyph {
    color: var(--ai-accent);
    font-family: var(--font-mono);
    flex-shrink: 0;
}

.ai-row__assistant {
    padding: 6px 16px 6px 34px;
    font-size: 13px;
    color: #B8B4DD;
    line-height: 1.7;
}

.ai-row__assistant--refusal {
    color: var(--ai-text-dim);
    border-left: 1px dashed var(--ai-border-soft);
    margin-left: 16px;
    padding-left: 16px;
}

.ai-subtask-list {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-top: 8px;
}

.ai-subtask-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 10px;
    background: var(--ai-accent-soft);
    border-left: 2px solid var(--ai-accent);
    border-radius: var(--border-radius-md);
    font-size: 12px;
}

.ai-subtask-row__title { flex: 1; color: var(--ai-text); }
.ai-subtask-row__time { color: var(--ai-text-muted); }

.ai-action-btn {
    margin-top: 8px;
    background: var(--ai-accent-soft);
    border: 0.5px solid var(--ai-border);
    color: #CECBF6;
    font-size: 12px;
    padding: 6px 12px;
    border-radius: var(--border-radius-md);
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.ai-action-btn:disabled { opacity: 0.6; }

.ai-action-confirm {
    margin-top: 8px;
    font-size: 12px;
    color: #5DCAA5;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.ai-row__error {
    margin: 6px 16px;
    padding: 8px 12px;
    border: 0.5px solid #A32D2D;
    border-radius: var(--border-radius-md);
    font-size: 12px;
    color: #F0997B;
    display: flex;
    align-items: center;
    gap: 8px;
}

.ai-command-bar {
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(0, 0, 0, 0.2);
    border: 0.5px solid var(--ai-border-soft);
    border-radius: var(--border-radius-md);
    padding: 8px 12px;
    margin: 12px 16px;
}

.ai-command-bar__glyph {
    color: var(--ai-accent);
    font-family: var(--font-mono);
    font-size: 13px;
}

.ai-command-bar__input {
    flex: 1;
    background: transparent;
    border: none;
    outline: none;
    color: var(--ai-text);
    font-size: 13px;
    padding: 0;
}

.ai-command-bar__hint { color: var(--ai-text-dim); font-size: 14px; }

.ai-command-bar__stop {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
    color: #F0997B;
    background: transparent;
    border: 0.5px solid rgba(240, 153, 123, 0.3);
    border-radius: var(--border-radius-md);
    padding: 4px 10px;
}
```

---

## Phase 8 — Command-Input Behavior & Subtask Conversion

**Goal:** Wire the new input bar and subtask cards to the Phase 6 backend.

### Steps

**8.1 — Modify the Alpine `sendMessage()` to strip the `subtasks` fence for live display**

While streaming, raw tokens may include a partial `\`\`\`subtasks` fence as it arrives. The display layer hides it incrementally rather than waiting for the full response:

```javascript
const SUBTASK_FENCE_START = /```subtasks/;

streamMessageInPlace(chunk, messageIndex) {
    const msg = this.messages[messageIndex];
    msg.content += chunk;

    const fenceMatch = msg.content.match(SUBTASK_FENCE_START);
    if (fenceMatch) {
        // Show only the prose before the fence while streaming
        msg.displayContent = msg.content.slice(0, fenceMatch.index).trim();
    } else {
        msg.displayContent = msg.content;
    }
}
```

**8.2 — On stream completion, request the parsed version**

The raw stream doesn't carry structured `subtasks` data (Phase 6.4 only returns parsed JSON via `chat()` or `showSession()`). After a stream finishes, fetch the persisted, parsed message:

```javascript
async onStreamComplete(messageIndex, sessionId) {
    const res = await fetch(`/ai/sessions/${sessionId}/messages/latest`);
    if (!res.ok) return; // display content remains as-is; not fatal

    const data = await res.json();
    const msg = this.messages[messageIndex];
    msg.displayContent = data.content; // clean prose, fence already stripped server-side
    msg.subtasks = data.metadata?.subtasks ?? null;
}
```

> Requires a small new route `GET /ai/sessions/{session}/messages/latest` returning the most recent assistant message with its `metadata` — cheap addition to `AiChatRepository`.

**8.3 — `acceptSubtasks(index)` Alpine method**

```javascript
async acceptSubtasks(index) {
    const msg = this.messages[index];
    msg.acceptingSubtasks = true;

    // Parent task title comes from the prose — extracted server-side at
    // generation time and stored in metadata to avoid fragile client parsing
    const parentTitle = msg.parentTaskTitle;

    try {
        const res = await fetch('/ai/subtasks/accept', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
            body: JSON.stringify({
                session_id: this.activeSessionId,
                message_id: msg.id,
                parent_task_title: parentTitle,
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
}
```

> Note on `parentTaskTitle`: 6.1's prompt asks the model to reference the task "by its exact title" in prose, but extracting that reliably client-side is fragile (the model might phrase it as "Your task 'Finish client proposal'..." with varying punctuation). Cleaner fix: add a third field to the structured output — a sibling fence or JSON key carrying `parent_task_title` explicitly, parsed by `StructuredReplyParser` (6.2) alongside `subtasks`, and stored in `metadata`. Revise 6.1's example to:
>
> ```
> ```subtasks
> {"parent_task_title": "Finish client proposal", "items": [{"title": "...", "estimate_minutes": 15}]}
> ```
>
> ```
> and adjust 6.2's parser to read `metadata.parent_task_title` / `metadata.subtasks` accordingly. This is a small but important correction to Phase 6 — flagging it here since it only becomes obvious once the frontend consumer (8.3) is designed.

**8.4 — Disabled-state styling for the command bar**

Per the original concurrent-submission mitigation: `:disabled="isLoading"` on the input, replaced by the Stop button (7.5). No additional logic needed beyond what Phase 2's original mitigation specified — this phase just applies it to the new visual component.

---

## Phase 9 — FAB → Compact Panel Adaptation

**Goal:** Resolve the "full parity" ambiguity from the documentation review — explicitly adapt each panel feature for the FAB's popover constraints rather than asserting 1:1 parity.

### Steps

**9.1 — FAB renders the same Blade components, different container**

`components/context-strip.blade.php`, `message-row.blade.php`, and `command-input.blade.php` are reused as-is inside `layouts/app.blade.php`'s FAB popover — same Alpine store, same CSS classes. The popover wrapper constrains width/height:

```blade
<div class="ai-fab-popover" x-show="fabOpen" x-transition>
    <div class="ai-panel" style="max-width: 360px; max-height: 480px; overflow-y: auto;">
        @include('ai.components.context-strip')
        <div class="ai-fab-messages">
            @include('ai.components.message-row')
        </div>
        @include('ai.components.command-input')
    </div>
</div>
```

**9.2 — Off-topic nudge adapts to inline banner (resolves doc gap #7)**

Rather than a tooltip (ambiguous in a small popover), the nudge renders as a dismissible banner above the command bar in *both* contexts — simplifies to one implementation instead of two:

```blade
<template x-if="showOffTopicNudge">
    <div class="ai-nudge-banner">
        <i class="ti ti-bulb" aria-hidden="true"></i>
        Try asking about your tasks, schedule, or productivity stats.
        <button @click="showOffTopicNudge = false" aria-label="Dismiss">
            <i class="ti ti-x" aria-hidden="true"></i>
        </button>
    </div>
</template>
```

```css
.ai-nudge-banner {
    margin: 8px 16px 0;
    padding: 8px 12px;
    background: var(--ai-accent-soft);
    border-radius: var(--border-radius-md);
    font-size: 12px;
    color: var(--ai-text-muted);
    display: flex;
    align-items: center;
    gap: 8px;
}

.ai-nudge-banner button {
    margin-left: auto;
    background: transparent;
    border: none;
    color: var(--ai-text-dim);
    padding: 2px;
}
```

**9.3 — Session loading on FAB open (resolves doc gap #1 and #7's skeleton question)**

The FAB shows 2 skeleton rows **on open** if no session is loaded yet — this is the FAB's equivalent of "switching sessions" (it's switching from "closed" to "showing session X"). Combined with the Phase-9 fix for gap #1:

```javascript
async openFab() {
    this.fabOpen = true;
    if (this.activeSessionId) return; // already loaded via tab-sync

    this.fabLoading = true;
    const stored = localStorage.getItem('active_ai_session_id');

    if (stored) {
        await this.loadSession(stored);
    } else {
        // Gap #1 fix: server-side fallback when localStorage is empty
        const res = await fetch('/ai/sessions/active');
        const data = await res.json();
        if (data.session_id) {
            await this.loadSession(data.session_id);
        }
        // else: no sessions exist yet — show empty state, first message creates one
    }
    this.fabLoading = false;
}
```

```css
.ai-skeleton-row {
    height: 14px;
    margin: 8px 16px;
    border-radius: var(--border-radius-md);
    background: var(--ai-border-soft);
    animation: ai-skeleton-pulse 1.4s ease-in-out infinite;
}

@keyframes ai-skeleton-pulse {
    0%, 100% { opacity: 0.4; }
    50% { opacity: 0.8; }
}
```

**9.4 — New route + controller method for `/ai/sessions/active`**

```php
Route::get('/ai/sessions/active', [AiAssistantController::class, 'activeSession']);
```

```php
public function activeSession(Request $request)
{
    $session = $this->chatRepository->getSessions(auth()->id())->first(); // already latest-first

    return response()->json([
        'session_id' => $session?->id,
    ]);
}
```

No new query needed — `getSessions()` (Phase 2.3) already orders by most recent activity, including "Touch Bubbling" (doc 2.1) updates.

---

## File Change Map

| File | Action | Phase |
|---|---|---|
| `app/Http/Resources/AiContextResource.php` | **New** | 5 |
| `app/Http/Controllers/AiAssistantController.php` | **Modify** — context in index/showSession/stream headers, new `acceptSubtasks`, `activeSession`, `latestMessage` | 5, 6, 9 |
| `app/Services/Ai/StructuredReplyParser.php` | **New** | 6 |
| `app/Services/AiAssistantService.php` | **Modify** — system prompt structured-output rules | 6 |
| `database/migrations/*_add_metadata_to_ai_chat_messages.php` | **New** | 6 |
| `app/Repositories/AiChatRepository.php` | **Modify** — `addMessage()` metadata param, `getLatestMessage()` | 6, 8 |
| `routes/web.php` | **Modify** — subtasks/accept, sessions/active, sessions/{id}/messages/latest | 6, 9 |
| `resources/views/ai/index.blade.php` | **Rewrite** — panel shell | 7 |
| `resources/views/ai/components/context-strip.blade.php` | **New** | 7, 9 |
| `resources/views/ai/components/message-row.blade.php` | **New** | 7, 9 |
| `resources/views/ai/components/subtask-card.blade.php` | **New** | 7 |
| `resources/views/ai/components/command-input.blade.php` | **New** | 7, 9 |
| `resources/js/ai-store.js` | **New** | 7, 9 |
| `resources/js/ai-chat.js` (Alpine component) | **Modify** — fence-stripping, accept flow, FAB session loading | 8, 9 |
| `resources/css/ai-panel.css` | **New** | 7, 9 |
| `resources/views/layouts/app.blade.php` | **Modify** — FAB uses shared components | 9 |

---

## Open Items Carried From Documentation Review

These were flagged during the QA pass on the documentation and should be resolved alongside this UI work, since the new UI surfaces them more directly:

- **Refusal styling** (`.msg-bubble--refusal` → `.ai-row__assistant--refusal` in 7.6) now visually distinct via dashed left border — confirms refusals render differently from normal replies, addressing the original "muted, dashed" spec.
- **Generating-cache TTL (doc gap #3a)**: with the new context strip refreshing on every `showSession()` call (5.3), a stale `generating: true` flag becomes more visible — a user switching sessions would see the strip update but the previous session still marked "generating" indefinitely if the TTL/detach logic isn't fixed. Recommend resolving doc gap #3a/#3b before or alongside Phase 7, since the new UI has more surface area where staleness shows.
- **Refusal cache scoping (doc gap #4)**: unaffected by this redesign directly, but the context strip makes per-user task state more visually prominent — if a cached cross-user refusal serves an answer that contradicts what the strip shows (e.g., strip shows "Overdue: 3" but a cached refusal says "I can only help with tasks"), the mismatch will be more jarring with this UI than with the old generic bubble. Reinforces the priority of fixing doc gap #4.
