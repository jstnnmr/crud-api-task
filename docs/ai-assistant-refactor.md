# AI Assistant Refactor — Implementation Plan

Transition from Groq API to OpenCode-Go DeepSeek v4 Flash, with persistence, streaming, and scope enforcement.

---

## Phase Overview

| Phase | Focus | Effort |
|---|---|---|
| 1 | Provider abstraction & DeepSeek integration | ~40 min |
| 2 | Chat session persistence | ~60 min |
| 3 | Streaming responses | ~45 min |
| 4 | Smarter system prompt & scope enforcement | ~30 min |

---

## Phase 1 — Provider Abstraction & DeepSeek Integration

**Goal:** Decouple from Groq. Make the AI provider a pluggable config value. Add OpenCode-Go/DeepSeek as the default provider. Groq becomes a configurable fallback.

### Steps

**1.1 — Create `config/ai.php`**
```php
<?php

return [
    'default' => env('AI_PROVIDER', 'opencodego'),

    'providers' => [
        'opencodego' => [
            'key'   => env('OPENAICODE_API_KEY'),
            'model' => env('AI_MODEL', 'deepseek-v4-flash'),
            'url'   => 'https://opencode.ai/zen/go/v1/chat/completions',
        ],
        'groq' => [
            'key'   => env('GROQ_API_KEY'),
            'model' => env('AI_MODEL', 'llama-3.3-70b-versatile'),
            'url'   => 'https://api.groq.com/openai/v1/chat/completions',
        ],
    ],

    'limits' => [
        'max_response_tokens' => 1024,
        'max_context_messages' => 20,
    ],
];
```

**1.2 — Create `app/Contracts/AiProvider.php`**
```php
<?php

namespace App\Contracts;

interface AiProvider
{
    public function chat(array $messages): string;
    public function stream(array $messages): \Generator;
}
```

**1.3 — Create `app/Services/Ai/OpenCodeGoProvider.php`**

Sends POST to the DeepSeek endpoint using OpenAI-compatible format. Implements both `chat()` and `stream()`.

```php
class OpenCodeGoProvider implements AiProvider
{
    public function __construct(
        protected array $config
    ) {}

    public function chat(array $messages): string
    {
        $response = Http::withToken($this->config['key'])
            ->timeout(60)
            ->post($this->config['url'], [
                'model'       => $this->config['model'],
                'messages'    => $messages,
                'max_tokens'  => config('ai.limits.max_response_tokens'),
                'stream'      => false,
            ]);

        $response->throw();
        return $response->json('choices.0.message.content');
    }

    public function stream(array $messages): Generator
    {
        $response = Http::withToken($this->config['key'])
            ->timeout(120)
            ->withOptions(['stream' => true])
            ->post($this->config['url'], [
                'model'       => $this->config['model'],
                'messages'    => $messages,
                'max_tokens'  => config('ai.limits.max_response_tokens'),
                'stream'      => true,
            ]);

        // Yield each content delta from the SSE stream
        foreach ($this->parseSSE($response->body()) as $token) {
            yield $token;
        }
    }
}
```

**1.4 — Create `app/Services/Ai/AiProviderFactory.php`**

Resolves the provider from `config('ai.default')`. Falls back gracefully:

```php
class AiProviderFactory
{
    public function make(?string $name = null): AiProvider
    {
        $name ??= config('ai.default');
        $config = config("ai.providers.{$name}");

        if (!$config || blank($config['key'] ?? null)) {
            $config = config('ai.providers.groq');
        }

        return match ($name) {
            'opencodego' => new OpenCodeGoProvider($config),
            'groq'       => new GroqProvider($config),
            default      => throw new \RuntimeException("Unknown AI provider: {$name}"),
        };
    }
}
```

**1.5 — Modify `app/Services/AiAssistantService.php`**

- Accept `AiProvider` via constructor injection (Laravel auto-resolves via `AiProviderFactory`)
- Remove all direct Groq API calls
- Add `streamChat()` method that wraps `$this->provider->stream()`
- The system prompt builder stays here (moved to Phase 4 for enrichment)

**1.6 — Modify `config/services.php`**

Remove the `groq` block — all AI config now lives in `config/ai.php`.

**1.7 — Update `.env.example`**

```env
AI_PROVIDER=opencodego
OPENAICODE_API_KEY=
AI_MODEL=deepseek-v4-flash
GROQ_API_KEY=
```

---

## Phase 2 — Chat Session Persistence

**Goal:** Conversations survive page navigation, browser refresh, and tab close.

### Steps

**2.1 — Create migrations**
```
database/migrations/YYYY_MM_DD_HHMMSS_create_ai_chat_sessions_table.php
database/migrations/YYYY_MM_DD_HHMMSS_create_ai_chat_messages_table.php
```

**Schema:**

```php
// ai_chat_sessions
Schema::create('ai_chat_sessions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('title')->nullable();
    $table->timestamps();
});

// ai_chat_messages
Schema::create('ai_chat_messages', function (Blueprint $table) {
    $table->id();
    $table->foreignId('session_id')->constrained('ai_chat_sessions')->cascadeOnDelete();
    $table->string('role'); // 'user' | 'assistant' | 'system'
    $table->longText('content');
    $table->timestamps();
});
```

**2.2 — Create Models**

`AiChatSession`:
```php
class AiChatSession extends Model
{
    protected $fillable = ['user_id', 'title'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(AiChatMessage::class);
    }
}
```

`AiChatMessage`:
```php
class AiChatMessage extends Model
{
    protected $fillable = ['session_id', 'role', 'content'];

    public function session(): BelongsTo
    {
        return $this->belongsTo(AiChatSession::class);
    }
}
```

**2.3 — Create `app/Repositories/AiChatRepository.php`**

Methods:
- `getSessions(int $userId): Collection` — latest first
- `findSession(int $id, int $userId): ?AiChatSession`
- `createSession(int $userId, ?string $title = null): AiChatSession`
- `getMessages(int $sessionId, int $userId): Collection` — oldest first
- `addMessage(int $sessionId, string $role, string $content): AiChatMessage`
- `deleteSession(int $id, int $userId): void`

**2.4 — Modify `AiAssistantController`**

- `index()` — pass user's sessions to the view
- `chat(Request)` — accepts optional `session_id` param; creates or loads session; saves user message before API call; saves assistant reply after; returns `{ reply, session_id }`
- New method `sessions()` — list sessions for the sidebar (AJAX refresh)

**2.5 — Modify `resources/views/ai/index.blade.php`**

Replace the single-chat Alpine component with a session-based layout:

```
┌─────────────────────────────────────────────────┐
│  ┌──────────────┐  ┌────────────────────────┐   │
│  │ Sessions     │  │                        │   │
│  │ ─────────────│  │   Chat Messages        │   │
│  │ + New Chat   │  │                        │   │
│  │              │  │   ...scroll...         │   │
│  │ Planning...  │  │                        │   │
│  │ Algebra Help │  │  ┌─────────────────┐   │   │
│  │ Today's Qs   │  │  │ [Type message]  │   │   │
│  └──────────────┘  └────────────────────────┘   │
└─────────────────────────────────────────────────┘
```

Alpine.js state changes:
- `sessions[]` — loaded on mount, refreshed after new chat
- `activeSessionId` — switches when clicking a session
- On mount: loads last session's messages from DB via `GET /ai/sessions/{id}`
- On send: saves to DB, calls AI, saves reply, refreshes messages

**2.6 — Modify FAB (`layouts/app.blade.php`)**

- The FAB always uses the user's latest/most recent session
- Messages are fetched from DB on open
- Each message sent/recieved is persisted via an API call sharing the same controller

**2.7 — Update routes**

`routes/web.php`:
```php
Route::get('/ai/sessions', [AiAssistantController::class, 'sessions']);
Route::get('/ai/sessions/{session}', [AiAssistantController::class, 'showSession']);
Route::post('/ai/sessions', [AiAssistantController::class, 'createSession']);
Route::delete('/ai/sessions/{session}', [AiAssistantController::class, 'deleteSession']);
```

All inside the `auth` middleware group alongside existing `/ai` routes.

---

## Phase 3 — Streaming Responses

**Goal:** Tokens appear in real-time instead of waiting for a full response.

### Steps

**3.1 — Implement streaming in provider**

`OpenCodeGoProvider::stream()` sends the request with `stream: true` and yields each content delta chunk. The parse logic handles the SSE `data: {...}` lines.

```php
private function parseSSE(string $body): Generator
{
    foreach (explode("\n", $body) as $line) {
        if (str_starts_with($line, 'data: ')) {
            $data = json_decode(substr($line, 6), true);
            if (isset($data['choices'][0]['delta']['content'])) {
                yield $data['choices'][0]['delta']['content'];
            }
        }
    }
}
```

**3.2 — Create streaming endpoint**

`AiAssistantController::stream(Request)`:
- Same validation as `chat()`
- Calls `AiAssistantService::streamChat()`
- Returns `Symfony\Component\HttpFoundation\StreamedResponse`

```php
public function stream(Request $request): StreamedResponse
{
    $request->validate([...same as chat...]);

    $session = $this->loadSession($request);
    $messages = $this->buildContext($session);
    $messages[] = ['role' => 'user', 'content' => $request->input('messages.0.content')];

    // Save user message
    $this->chatRepository->addMessage($session->id, 'user', $request->input('messages.0.content'));

    $assistantContent = '';

    return response()->stream(function () use ($messages, $session, &$assistantContent) {
        foreach ($this->aiService->streamChat($messages) as $chunk) {
            $assistantContent .= $chunk;
            echo $chunk;
            ob_flush(); flush();
        }
    }, 200, [
        'Content-Type' => 'text/event-stream',
        'Cache-Control' => 'no-cache',
        'X-Session-Id' => $session->id,
    ]);
}
```

The streaming endpoint is simpler than the full SSE format — it flushes raw text tokens. The frontend builds the reply incrementally. When the stream ends, the controller saves the full `$assistantContent` to the DB.

**3.3 — Update routes**

```php
Route::post('/ai/chat/stream', [AiAssistantController::class, 'stream'])->middleware('throttle:30,1');
```

Both the full-page chat and the FAB widget use a `fetch()` call that reads the response body via `ReadableStream`:

```javascript
async sendMessageStream(text) {
    const response = await fetch('/ai/chat/stream', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ session_id: this.activeSessionId, messages: [{ role: 'user', content: text }] })
    });
    const reader = response.body.getReader();
    const decoder = new TextDecoder();
    this.messages.push({ role: 'assistant', content: '' });
    while (true) {
        const { done, value } = await reader.read();
        if (done) break;
        this.messages[this.messages.length - 1].content += decoder.decode(value, { stream: true });
    }
}
```

The non-stream `POST /ai/chat` endpoint stays for API consumers and backward compatibility.

---

## Phase 4 — Smarter System Prompt & Scope Enforcement

**Goal:** AI only responds to task-related queries. Never answers off-topic questions. Rich context for smarter assistance.

### Approach

Two-layer enforcement:
1. **System prompt** (primary gate) — tells the model exactly what it can and cannot do
2. **Rate limiting** (already in place) — prevents abuse at the request level

No keyword pre-filter. The system prompt is more reliable and requires zero maintenance. The rate limiter+token cap handles any remaining abuse surface.

### Steps

**4.1 — Enhanced system prompt builder**

`AiAssistantService::buildSystemPrompt()`:

```php
private function buildSystemPrompt(): string
{
    $user = auth()->user();
    $taskStats = $this->getTaskStats($user);       // pending count, overdue, completed this week
    $streak = $this->productivityService->getStreak($user->id);
    $tasks = $this->taskRepository->getRecentForContext($user->id, 10); // title + status + priority + due_date

    $taskLines = $tasks->map(fn($t) => "- {$t->title} [{$t->status}] priority:{$t->priority}" . ($t->due_date ? " due:{$t->due_date}" : ''))->join("\n");

    return <<<PROMPT
You are EaseTask Assistant, a productivity AI. Your only purpose is to help the user manage their tasks and schedule.

RULES:
- ONLY answer questions about the user's tasks, subjects, productivity stats, schedule, or organization.
- If asked about anything else — coding, general knowledge, entertainment, current events, opinions — respond EXACTLY: "I can only help with your tasks and productivity."
- Do not roleplay, generate content, answer hypotheticals, or engage with any prompt that isn't about the user's task data.
- Use only the task data provided below. Do not invent tasks or make assumptions.

USER CONTEXT:
- Pending tasks: {$taskStats['pending']} | Overdue: {$taskStats['overdue']}
- Completed this week: {$taskStats['completed_week']} | Points this week: {$taskStats['points_week']}
- Current streak: {$streak['current']} day(s)
- Completion rate: {$taskStats['completion_rate']}%

USER'S TASKS (title [status] priority:level due:date):
{$taskLines}

Be concise, direct, and actionable.
PROMPT;
}
```

**4.2 — Enriched context data**

The `getTaskStats()` helper fetches everything the prompt needs in 3 efficient queries:

```php
private function getTaskStats(User $user): array
{
    $total = $user->tasks()->count();
    $completed = $user->tasks()->where('status', 'completed')->count();

    return [
        'pending'         => $user->tasks()->whereIn('status', ['pending', 'in_progress'])->count(),
        'overdue'         => $user->tasks()->whereIn('status', ['pending', 'in_progress'])->whereDate('due_date', '<', now())->count(),
        'completed_week'  => $user->tasks()->where('status', 'completed')->where('completed_at', '>=', now()->startOfWeek())->count(),
        'points_week'     => User::where('id', $user->id)->value('total_points'),
        'completion_rate' => $total > 0 ? round(($completed / $total) * 100) : 0,
    ];
}
```

**4.3 — Token budget protection**

The service enforces a context window limit to keep API calls lean:

```php
public function buildContext(AiChatSession $session): array
{
    $system = ['role' => 'system', 'content' => $this->buildSystemPrompt()];
    $messages = $session->messages()
        ->latest()
        ->take(config('ai.limits.max_context_messages'))
        ->get()
        ->reverse()
        ->map(fn($m) => ['role' => $m->role, 'content' => $m->content])
        ->values()
        ->toArray();

    return array_merge([$system], $messages);
}
```

This guarantees:
- System prompt is always the first message
- At most 20 conversation turns are sent (configurable)
- Oldest messages are dropped first

---

## File Change Map

| File | Action | Phase |
|---|---|---|
| `config/ai.php` | **New** | 1 |
| `app/Contracts/AiProvider.php` | **New** | 1 |
| `app/Services/Ai/OpenCodeGoProvider.php` | **New** | 1, 3 |
| `app/Services/Ai/AiProviderFactory.php` | **New** | 1 |
| `app/Services/Ai/GroqProvider.php` | **New** | 1 |
| `config/services.php` | **Modify** — remove groq block | 1 |
| `app/Services/AiAssistantService.php` | **Modify** — provider DI, prompt builder, stream wrapper | 1, 3, 4 |
| `.env.example` | **Modify** — add AI env vars | 1 |
| `database/migrations/*_ai_chat_sessions.php` | **New** | 2 |
| `database/migrations/*_ai_chat_messages.php` | **New** | 2 |
| `app/Models/AiChatSession.php` | **New** | 2 |
| `app/Models/AiChatMessage.php` | **New** | 2 |
| `app/Repositories/AiChatRepository.php` | **New** | 2 |
| `app/Http/Controllers/AiAssistantController.php` | **Modify** — session flow, stream endpoint | 2, 3 |
| `routes/web.php` | **Modify** — add session + stream routes | 2, 3 |
| `resources/views/ai/index.blade.php` | **Modify** — session sidebar, streaming | 2, 3 |
| `resources/views/layouts/app.blade.php` | **Modify** — FAB persistence + streaming | 2, 3 |
| `app/Services/ProductivityService.php` | **Modify** — export `getStreak()` for AI context | 4 |

---

## Scope Enforcement Summary

| Layer | Mechanism | Purpose |
|---|---|---|
| System prompt | Explicit instructions + refusal phrase | Primary gate |
| `max_tokens` (1024) | API parameter | Prevents long-winded responses |
| `max_context_messages` (20) | Service layer truncation | Limits token budget per call |
| Throttle (30/min) | Middleware | Prevents abuse at request level |

No keyword pre-filter. The system prompt is the sole semantic gate — it's more reliable, zero-maintenance, and won't produce false positives for creative-but-task-relevant queries.

---

## Gaps, Loopholes & Mitigations (Reviewed by Senior Engineer & QA)

During the review of this implementation plan, the following gaps and technical loopholes were identified. The proposed mitigations below must be incorporated during execution.

### 1. HTTP Client Streaming Blockage (Guzzle vs Laravel Http Client)
* **Gap:** The plan calls `$response->body()` in `OpenCodeGoProvider::stream()` to process Server-Sent Events (SSE). In Laravel's HTTP wrapper, `$response->body()` reads the *entire* response buffer into memory and returns it as a string *after* the request fully completes. This blocks the stream and breaks real-time rendering.
* **Mitigation:** Use a PSR-7 stream with the `send` method or retrieve the raw Guzzle stream response. Read the stream resource buffer line-by-line using standard PHP buffer reading tools to yield chunks instantly.
  ```php
  $response = Http::withToken($this->config['key'])
      ->timeout(120)
      ->withHeaders(['Accept' => 'text/event-stream'])
      ->withOptions(['stream' => true])
      ->send('POST', $this->config['url'], [
          'json' => [
              'model'      => $this->config['model'],
              'messages'   => $messages,
              'max_tokens' => config('ai.limits.max_response_tokens'),
              'stream'     => true,
          ]
      ]);

  $body = $response->getBody();
  while (!$body->eof()) {
      $line = $this->readLineFromStream($body);
      if (str_starts_with($line, 'data: ')) {
          $dataStr = trim(substr($line, 6));
          if ($dataStr === '[DONE]') {
              break;
          }
          $data = json_decode($dataStr, true);
          if (isset($data['choices'][0]['delta']['content'])) {
              yield $data['choices'][0]['delta']['content'];
          }
      }
  }
  ```

### 2. User Abort During Live Streams (Orphaned Messages / DB Out-of-Sync)
* **Gap:** If the user closes the browser tab, closes the chat widget, or navigates away mid-response, the HTTP connection is severed. The PHP script running the `StreamedResponse` loop may abort immediately. This leaves the database state out-of-sync: the user's message is saved, but the assistant's reply is either completely lost or truncated in the database, while the user saw it stream halfway before aborting.
* **Mitigation:** 
  1. Call `ignore_user_abort(true);` at the beginning of the `StreamedResponse` callback so that the generation finishes and persists the complete assistant response even if the user disconnects.
  2. Implement an automatic clean-up or save the assistant response incrementally.

### 3. Server-Side Output Buffering & Proxy Buffering
* **Gap:** Web servers (Nginx, Apache) and PHP output buffers (`output_buffering` in `php.ini`) aggregate output data and send it in larger TCP packets. Without disabling buffering, the stream chunks will be held back and flushed all at once at the end of the request, destroying the streaming experience.
* **Mitigation:**
  1. Send headers to disable Nginx proxy buffering: `'X-Accel-Buffering' => 'no'`.
  2. Turn off PHP's internal output buffering layers before starting the streaming loop:
     ```php
     while (ob_get_level() > 0) {
         ob_end_flush();
     }
     ```

### 4. Provider Factory Fallback Logic Bug
* **Gap:** In `AiProviderFactory::make()`, if the default provider (e.g. `opencodego`) has a blank key, it updates the `$config` variable to `groq` but still runs `match($name)` on the original provider name. This instantiates `OpenCodeGoProvider` using `groq`'s configuration (sending keys to the wrong API endpoint).
* **Mitigation:** Change both `$name` and `$config` to the fallback values when resolving:
  ```php
  $name ??= config('ai.default');
  $config = config("ai.providers.{$name}");

  if (!$config || blank($config['key'] ?? null)) {
      $name = 'groq';
      $config = config('ai.providers.groq');
  }
  ```

### 5. Chat Session Title Generation (UX Deficit)
* **Gap:** New sessions are created with `title = null`. If the user opens multiple chats, the sidebar will show multiple "Untitled" sessions, leading to a poor user experience.
* **Mitigation:** When the first message is sent in a session, update the session title automatically. We can take the first 40 characters of the user's first query or run a short, fast summarization prompt asynchronously.

### 6. Concurrent Message Submissions (Race Conditions)
* **Gap:** A user can double-click "Send" or press Enter rapidly, sending multiple requests concurrently. This creates interleaved user/assistant message logs within the same database session, corrupting the prompt history.
* **Mitigation:** Frontend UI elements (text area, buttons, FAB input) must be disabled while an active stream request is processing.

