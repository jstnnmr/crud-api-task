<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\NoteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NoteController extends Controller
{
    public function __construct(
        protected NoteService $noteService
    ) {}

    public function index(Request $request): View
    {
        $perPage = (int) $request->query('per_page', 6);
        $result = $this->noteService->getAll(userId: auth()->id(), perPage: $perPage);
        return view('notes.index', ['notes' => $result->data]);
    }

    public function list(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 6);
        $result = $this->noteService->getAll(userId: $request->user()->id, perPage: $perPage);
        return response()->json($result, $result->status);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $result = $this->noteService->getById(id: $id, userId: $request->user()->id);
        return response()->json($result, $result->status);
    }

    public function store(Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'title'   => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'color'   => ['nullable', 'string', 'size:7'],
        ]);

        $result = $this->noteService->create(userId: $request->user()->id, data: $validated);

        if (!$request->expectsJson()) {
            return back()->with('status', $result->message);
        }

        return response()->json($result, $result->status);
    }

    public function update(Request $request, int $id): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'title'   => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'color'   => ['nullable', 'string', 'size:7'],
        ]);

        $result = $this->noteService->update(id: $id, userId: $request->user()->id, data: $validated);

        if (!$request->expectsJson()) {
            return back()->with('status', $result->message);
        }

        return response()->json($result, $result->status);
    }

    public function destroy(Request $request, int $id): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $result = $this->noteService->delete(id: $id, userId: $request->user()->id);

        if (!$request->expectsJson()) {
            return back()->with('status', $result->message);
        }

        return response()->json($result, $result->status);
    }

    public function invite(Request $request, int $id): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'invited_email' => ['required', 'string', 'email', 'max:255'],
        ]);

        $result = $this->noteService->invite(
            noteId: $id,
            userId: $request->user()->id,
            data: $validated
        );

        if (!$request->expectsJson()) {
            if ($result->success) {
                return back()->with('status', $result->message);
            }
            return back()->withErrors(['invited_email' => $result->message]);
        }

        return response()->json($result, $result->status);
    }

    public function removeCollaborator(Request $request, int $id, int $collaboratorId): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $result = $this->noteService->removeCollaborator(
            noteId: $id,
            userId: $request->user()->id,
            collaboratorId: $collaboratorId
        );

        if (!$request->expectsJson()) {
            return back()->with('status', $result->message);
        }

        return response()->json($result, $result->status);
    }
}
