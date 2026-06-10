<?php

namespace App\Http\Controllers;

use App\Services\AiAssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiAssistantController extends Controller
{
    public function __construct(
        protected AiAssistantService $aiAssistantService
    ) {}

    public function index(): View
    {
        return view('ai.index');
    }

    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'messages' => ['required', 'array', 'min:1'],
            'messages.*.role'    => ['required', 'string', 'in:user,assistant'],
            'messages.*.content' => ['required', 'string'],
        ]);

        $tasks = $request->user()
            ->tasks()
            ->latest('tasks.created_at')
            ->take(20)
            ->get(['tasks.title', 'tasks.status', 'tasks.priority', 'tasks.due_date'])
            ->toArray();

        try {
            $reply = $this->aiAssistantService->chat(
                messages: $validated['messages'],
                tasks: $tasks
            );
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 502);
        }

        return response()->json(['reply' => $reply]);
    }
}
