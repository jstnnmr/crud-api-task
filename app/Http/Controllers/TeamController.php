<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\TeamService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function __construct(
        protected TeamService $teamService
    ) {}

    public function index(Request $request): View
    {
        $user = auth()->user();
        $perPage = (int) $request->query('per_page', 10);
        $tasks = $this->teamService->getMyTasks(userId: $user->id, perPage: $perPage);
        $invitations = $this->teamService->getInvitations(userId: $user->id);
        $categories = $user->categories()->orderBy('name')->get();
        return view('team.index', [
            'tasks'       => $tasks->data,
            'invitations' => $invitations->data,
            'categories'  => $categories,
        ]);
    }

    public function myTasks(Request $request): View
    {
        $user = auth()->user();
        $perPage = (int) $request->query('per_page', 10);
        $result = $this->teamService->getMyTasks(userId: $user->id, perPage: $perPage);
        $categories = $user->categories()->orderBy('name')->get();
        return view('tasks.index', ['tasks' => $result->data, 'categories' => $categories]);
    }

    public function getMyTasks(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 10);
        $result = $this->teamService->getMyTasks(userId: $request->user()->id, perPage: $perPage);
        return response()->json($result, $result->status);
    }

    public function getCollaborators(Request $request, int $taskId): JsonResponse
    {
        $result = $this->teamService->getCollaborators(taskId: $taskId, userId: $request->user()->id);
        return response()->json($result, $result->status);
    }

    public function invite(Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'task_id'       => ['required', 'integer', 'exists:tasks,id'],
            'invited_email' => ['required', 'string', 'email', 'max:255'],
        ]);

        $result = $this->teamService->invite(userId: $request->user()->id, data: $validated);

        if (!$request->expectsJson()) {
            if ($result->success) {
                return back()->with('status', $result->message);
            }
            return back()->withErrors(['invited_email' => $result->message]);
        }

        return response()->json($result, $result->status);
    }

    public function getInvitations(Request $request): JsonResponse
    {
        $result = $this->teamService->getInvitations(userId: $request->user()->id);
        return response()->json($result, $result->status);
    }

    public function acceptInvitation(Request $request, string $token): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $result = $this->teamService->acceptInvitation(userId: $request->user()->id, token: $token);

        if (!$request->expectsJson()) {
            if ($result->success) {
                return redirect('/team')->with('status', $result->message);
            }
            return back()->withErrors(['invitation' => $result->message]);
        }

        return response()->json($result, $result->status);
    }

    public function declineInvitation(Request $request, string $token): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $result = $this->teamService->declineInvitation(userId: $request->user()->id, token: $token);

        if (!$request->expectsJson()) {
            if ($result->success) {
                return redirect('/team')->with('status', $result->message);
            }
            return back()->withErrors(['invitation' => $result->message]);
        }

        return response()->json($result, $result->status);
    }

    public function removeCollaborator(Request $request, int $taskId, int $collaboratorId): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $result = $this->teamService->removeCollaborator(
            taskId: $taskId,
            ownerId: $request->user()->id,
            collaboratorId: $collaboratorId
        );

        if (!$request->expectsJson()) {
            if ($result->success) {
                return back()->with('status', $result->message);
            }
            return back()->withErrors(['collaborator' => $result->message]);
        }

        return response()->json($result, $result->status);
    }

    public function getActivities(Request $request, int $taskId): JsonResponse
    {
        $result = $this->teamService->getActivities(taskId: $taskId, userId: $request->user()->id);
        return response()->json($result, $result->status);
    }
}
