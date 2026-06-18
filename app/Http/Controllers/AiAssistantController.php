<?php

namespace App\Http\Controllers;

use App\Http\Resources\AiContextResource;
use App\Services\AiAssistantService;
use App\Services\ProductivityService;
use App\Repositories\AiChatRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AiAssistantController extends Controller
{
    public function __construct(
        protected AiAssistantService $aiAssistantService,
        protected AiChatRepository $chatRepository,
        protected ProductivityService $productivityService
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $sessions = $this->chatRepository->getSessions($user->id);
        $aiContext = AiContextResource::fromUser($user, $this->productivityService);

        return view('ai.index', compact('sessions', 'aiContext'));
    }

    public function sessions(Request $request): JsonResponse
    {
        $sessions = $this->chatRepository->getSessions($request->user()->id);
        return response()->json($sessions);
    }

    public function showSession(Request $request, $id): JsonResponse
    {
        $session = $this->chatRepository->findSession((int) $id, $request->user()->id);
        if (!$session) {
            return response()->json(['error' => 'Chat session not found.'], 404);
        }

        $messages = $session->messages()->orderBy('created_at', 'asc')->get()->map(function ($msg) {
            $data = [
                'id' => $msg->id,
                'role' => $msg->role,
                'content' => $msg->content,
                'created_at' => $msg->created_at,
            ];

            if ($msg->role === 'assistant' && $msg->metadata) {
                $data['displayContent'] = $msg->metadata['prose'] ?? $msg->content;
                $data['subtasks'] = $msg->metadata['subtasks'] ?? null;
                $data['parentTaskTitle'] = $msg->metadata['parent_task_title'] ?? null;
            } else {
                $data['displayContent'] = $msg->content;
                $data['subtasks'] = null;
                $data['parentTaskTitle'] = null;
            }

            $data['isRefusal'] = $msg->content === 'I can only help with your tasks and productivity.';

            return $data;
        })->toArray();

        $generating = Cache::has("session_{$session->id}_generating");
        $context = AiContextResource::fromUser($request->user(), $this->productivityService);

        return response()->json(compact('messages', 'generating', 'context'))
            ->header('X-Assistant-Generating', $generating ? '1' : '0');
    }

    public function latestMessage(Request $request, $id): JsonResponse
    {
        $message = $this->chatRepository->getLatestMessage((int) $id, $request->user()->id);
        if (!$message) {
            return response()->json(['error' => 'No messages found.'], 404);
        }

        $data = [
            'id' => $message->id,
            'role' => $message->role,
            'content' => $message->content,
        ];

        if ($message->metadata) {
            $data['displayContent'] = $message->metadata['prose'] ?? $message->content;
            $data['subtasks'] = $message->metadata['subtasks'] ?? null;
            $data['parentTaskTitle'] = $message->metadata['parent_task_title'] ?? null;
        } else {
            $data['displayContent'] = $message->content;
            $data['subtasks'] = null;
            $data['parentTaskTitle'] = null;
        }

        return response()->json($data);
    }

    public function createSession(Request $request): JsonResponse
    {
        $session = $this->chatRepository->createSession(
            $request->user()->id,
            $request->input('title')
        );
        return response()->json($session, 201);
    }

    public function deleteSession(Request $request, $id): JsonResponse
    {
        $session = $this->chatRepository->findSession((int) $id, $request->user()->id);
        if (!$session) {
            return response()->json(['error' => 'Chat session not found.'], 404);
        }
        $this->chatRepository->deleteSession((int) $id, $request->user()->id);
        return response()->json(['message' => 'Session deleted successfully']);
    }

    public function forkSession(Request $request, $id): JsonResponse
    {
        $session = $this->chatRepository->forkSession((int) $id, $request->user()->id);
        if (!$session) {
            return response()->json(['error' => 'Session not found'], 404);
        }
        return response()->json($session, 201);
    }

    public function activeSession(Request $request): JsonResponse
    {
        $session = $this->chatRepository->getSessions($request->user()->id)->first();

        return response()->json([
            'session_id' => $session?->id,
        ]);
    }

    protected function prepareConversation(Request $request): array
    {
        $request->validate([
            'session_id' => ['nullable', 'integer'],
            'message'    => ['required', 'string'],
        ]);

        $userId = $request->user()->id;
        $sessionId = $request->input('session_id');

        if ($sessionId) {
            $session = $this->chatRepository->findSession($sessionId, $userId);
            if (!$session) {
                abort(404, 'Chat session not found.');
            }
        } else {
            $session = $this->chatRepository->createSession($userId);
        }

        $userMessage = $request->input('message');
        
        // Save user message
        $this->chatRepository->addMessage($session->id, 'user', $userMessage);

        $titleUpdated = false;
        if (blank($session->title) || $session->title === 'New Chat') {
            $session->update([
                'title' => Str::limit($userMessage, 40)
            ]);
            $titleUpdated = true;
        }

        $context = $this->aiAssistantService->buildContext($session);

        return [$session, $context, $userMessage, $titleUpdated];
    }

    public function chat(Request $request): JsonResponse
    {
        try {
            [$session, $context, $userMessage, $titleUpdated] = $this->prepareConversation($request);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getStatusCode());
        }

        $refusalString = "I can only help with your tasks and productivity.";
        $msgHash = md5(trim(strtolower($userMessage)));
        
        $user = $session->user;
        $totalTasks = $user->tasks()->count();
        $completedTasks = $user->tasks()->where('status', 'completed')->count();
        $points = $user->total_points;
        $contextHash = md5("t:{$totalTasks}|c:{$completedTasks}|p:{$points}");
        $cacheKey = "session_{$session->id}_refused_{$msgHash}_{$contextHash}";

        if (Cache::has($cacheKey)) {
            try {
                if (\App\Models\AiChatSession::where('id', $session->id)->exists()) {
                    $this->chatRepository->addMessage($session->id, 'assistant', $refusalString);
                }
            } catch (\Illuminate\Database\QueryException $e) {
                \Illuminate\Support\Facades\Log::info("Session {$session->id} was deleted mid-stream. FK error prevented.");
            }
            return response()->json([
                'reply'      => $refusalString,
                'session_id' => $session->id,
                'title'      => $titleUpdated ? $session->title : null,
                'subtasks'   => null,
            ]);
        }

        try {
            $reply = $this->aiAssistantService->chat($context);

            if (empty(trim($reply))) {
                $reply = 'I couldn\'t generate a response. Please try rephrasing your question.';
            }

            $parsed = $this->aiAssistantService->parseStructuredReply($reply);
            
            try {
                if (\App\Models\AiChatSession::where('id', $session->id)->exists()) {
                    $this->chatRepository->addMessage($session->id, 'assistant', $reply, [
                        'prose' => $parsed['prose'],
                        'subtasks' => $parsed['subtasks'],
                        'parent_task_title' => $parsed['parent_task_title'],
                    ]);
                    
                    if (trim($reply) === $refusalString) {
                        Cache::put($cacheKey, true, 3600);
                    }
                }
            } catch (\Illuminate\Database\QueryException $e) {
                \Illuminate\Support\Facades\Log::info("Session {$session->id} was deleted mid-stream. FK error prevented.");
            }
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 502);
        }

        return response()->json([
            'reply'      => $parsed['prose'] ?: $reply,
            'subtasks'   => $parsed['subtasks'],
            'session_id' => $session->id,
            'title'      => $titleUpdated ? $session->title : null,
        ]);
    }

    public function stream(Request $request): StreamedResponse
    {
        [$session, $context, $userMessage, $titleUpdated] = $this->prepareConversation($request);

        $refusalString = "I can only help with your tasks and productivity.";
        $msgHash = md5(trim(strtolower($userMessage)));
        
        $user = $session->user;
        $totalTasks = $user->tasks()->count();
        $completedTasks = $user->tasks()->where('status', 'completed')->count();
        $points = $user->total_points;
        $contextHash = md5("t:{$totalTasks}|c:{$completedTasks}|p:{$points}");
        $cacheKey = "session_{$session->id}_refused_{$msgHash}_{$contextHash}";

        Cache::put("session_{$session->id}_generating", true, 180);

        $headers = [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'X-Session-Id'      => $session->id,
        ];
        
        if ($titleUpdated) {
            $headers['X-Session-Title'] = $session->title;
        }

        if (Cache::has($cacheKey)) {
            return response()->stream(function () use ($session, $refusalString, $headers) {
                ignore_user_abort(true);
                while (ob_get_level() > 0) {
                    ob_end_flush();
                }
                
                echo $refusalString;
                flush();

                try {
                    if (\App\Models\AiChatSession::where('id', $session->id)->exists()) {
                        $this->chatRepository->addMessage($session->id, 'assistant', $refusalString);
                    }
                } catch (\Illuminate\Database\QueryException $e) {
                    \Illuminate\Support\Facades\Log::info("Session {$session->id} was deleted mid-stream. FK error prevented.");
                } finally {
                    Cache::forget("session_{$session->id}_generating");
                }
            }, 200, $headers);
        }

        return response()->stream(function () use ($context, $session, $cacheKey, $refusalString, $headers) {
            ignore_user_abort(true);

            while (ob_get_level() > 0) {
                ob_end_flush();
            }

            $assistantContent = '';

            try {
                foreach ($this->aiAssistantService->streamChat($context) as $chunk) {
                    $assistantContent .= $chunk;
                    echo $chunk;
                    flush();
                }

                if (empty(trim($assistantContent))) {
                    $assistantContent = 'I couldn\'t generate a response. Please try rephrasing your question.';
                }

                $parsed = $this->aiAssistantService->parseStructuredReply($assistantContent);

                try {
                    if (\App\Models\AiChatSession::where('id', $session->id)->exists()) {
                        $this->chatRepository->addMessage($session->id, 'assistant', $assistantContent, [
                            'prose' => $parsed['prose'],
                            'subtasks' => $parsed['subtasks'],
                            'parent_task_title' => $parsed['parent_task_title'],
                        ]);
                        if (trim($assistantContent) === $refusalString) {
                            Cache::put($cacheKey, true, 3600);
                        }
                    }
                } catch (\Illuminate\Database\QueryException $e) {
                    \Illuminate\Support\Facades\Log::info("Session {$session->id} was deleted mid-stream. FK error prevented.");
                }
            } catch (\Exception $e) {
                echo "\n[ERROR: " . $e->getMessage() . "]";
                flush();
            } finally {
                Cache::forget("session_{$session->id}_generating");
            }
        }, 200, $headers + [
            'X-Ai-Context' => json_encode(
                AiContextResource::fromUser($session->user, $this->productivityService)
            ),
        ]);
    }

    public function acceptSubtasks(Request $request): JsonResponse
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
                'priority' => 'medium',
                'status' => 'pending',
                'subject_id' => $parentTask->subject_id,
                'category_id' => $parentTask->category_id,
                'description' => 'Subtask of: ' . $parentTask->title . ' (est. ' . ($item['estimate_minutes'] ?? '?') . ' min)',
            ]);
        });

        return response()->json([
            'created' => $created->count(),
            'context' => AiContextResource::fromUser(auth()->user(), $this->productivityService),
        ]);
    }
}
