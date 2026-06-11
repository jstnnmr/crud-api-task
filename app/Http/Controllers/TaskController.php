<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Services\TaskService;
use App\Services\TeamService;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    public function __construct(
        protected TaskService $taskService,
        protected TeamService $teamService
    ) {}

    public function index(Request $request): JsonResponse|RedirectResponse
    {
        $result = $this->taskService->getAll(userId: auth()->id());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $result->data]);
        }

        return redirect()->route('dashboard');
    }

    public function show(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $result = $this->taskService->getById(id: $id, userId: auth()->id());

        if (!$result->success) {
            if ($request->wantsJson()) return response()->json(['message' => $result->message], $result->status);
            return redirect()->route('dashboard')->with('error', $result->message);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $result->data]);
        }

        return redirect()->route('dashboard');
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'subject_id'    => 'required|exists:subjects,id',
            'category_id'   => 'nullable|exists:categories,id',
            'category_name' => 'nullable|string|max:255',
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'priority'      => 'required|in:low,medium,high',
            'status'        => 'required|in:pending,in_progress,completed',
            'due_date'      => 'nullable|date',
            'invited_email' => 'nullable|email|max:255',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) return response()->json(['errors' => $validator->errors()], 422);
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Verify subject belongs to auth user
        $subject = auth()->user()->subjects()->find($request->subject_id);
        if (!$subject) {
            if ($request->wantsJson()) return response()->json(['message' => 'Unauthorized'], 403);
            return redirect()->back()->with('error', 'Unauthorized');
        }

        $result = $this->taskService->createTask(
            userId: auth()->id(),
            data: $validator->validated()
        );

        // Send invite if email provided
        if ($request->filled('invited_email') && $result->success) {
            $this->teamService->invite(
                userId: auth()->id(),
                data: [
                    'task_id'       => $result->data->id,
                    'invited_email' => $request->invited_email,
                ]
            );
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $result->data], 201);
        }

        return redirect()->route('subjects.data')->with('success', 'Task added!' . ($request->filled('invited_email') ? ' Invitation sent.' : ''));
    }

    public function update(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'category_id'   => 'nullable|exists:categories,id',
            'category_name' => 'nullable|string|max:255',
            'title'         => 'sometimes|string|max:255',
            'description'   => 'nullable|string',
            'priority'      => 'sometimes|in:low,medium,high',
            'status'        => 'sometimes|in:pending,in_progress,completed',
            'due_date'      => 'nullable|date',
            'invited_email' => 'nullable|email|max:255',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) return response()->json(['errors' => $validator->errors()], 422);
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $result = $this->taskService->updateTask(
            id: $id,
            userId: auth()->id(),
            data: $validator->validated()
        );

        if (!$result->success) {
            if ($request->wantsJson()) return response()->json(['message' => $result->message], $result->status);
            return redirect()->back()->with('error', $result->message);
        }

        // Send invite if email provided
        if ($request->filled('invited_email')) {
            $this->teamService->invite(
                userId: auth()->id(),
                data: [
                    'task_id'       => $id,
                    'invited_email' => $request->invited_email,
                ]
            );
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $result->data]);
        }

        return redirect()->route('subjects.data')->with('success', 'Task updated!' . ($request->filled('invited_email') ? ' Invitation sent.' : ''));
    }

    public function destroy(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $result = $this->taskService->deleteTask(id: $id, userId: auth()->id());

        if (!$result->success) {
            if ($request->wantsJson()) return response()->json(['message' => $result->message], $result->status);
            return redirect()->back()->with('error', $result->message);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $result->message]);
        }

        return redirect()->route('subjects.data')->with('success', 'Task deleted!');
    }

    public function complete(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $result = $this->taskService->completeTask(id: $id, userId: auth()->id());

        if (!$result->success) {
            if ($request->wantsJson()) return response()->json(['message' => $result->message], $result->status);
            return redirect()->back()->with('error', $result->message);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success'       => true,
                'message'       => $result->message,
                'points_earned' => $result->data['points_earned'],
                'data'          => $result->data['task'],
            ]);
        }

        return redirect()->back()->with(['success' => $result->message, 'confetti' => true]);
    }
}