<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Models\Subject;
use App\Services\SubjectService;
use App\Services\CategoryService;
use Illuminate\Support\Facades\Validator;

class SubjectController extends Controller
{
    public function __construct(
        protected SubjectService  $subjectService,
        protected CategoryService $categoryService
    ) {}

    public function index(Request $request): JsonResponse|View
    {
        $result = $this->subjectService->getAll(userId: auth()->id());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $result->data]);
        }

        return view('subjects.index', ['subjects' => $result->data]);
    }

    public function show(Request $request, int $id): JsonResponse|View|RedirectResponse
    {
        $result = $this->subjectService->getByIdWithTasks(id: $id, userId: auth()->id());

        if (!$result->success) {
            if ($request->wantsJson()) return response()->json(['message' => $result->message], $result->status);
            return redirect()->route('dashboard')->with('error', $result->message);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $result->data]);
        }

        $categories = $this->categoryService->getAll(userId: auth()->id())->data;
        return view('subjects.show', ['subject' => $result->data, 'categories' => $categories]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name'  => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) return response()->json(['errors' => $validator->errors()], 422);
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $result = $this->subjectService->create(userId: auth()->id(), data: $validator->validated());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $result->data], 201);
        }

        return redirect()->route('subjects.data')->with('success', 'Subject created!');
    }

    public function update(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name'  => 'sometimes|string|max:255',
            'color' => 'sometimes|string|max:7',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) return response()->json(['errors' => $validator->errors()], 422);
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $result = $this->subjectService->update(id: $id, userId: auth()->id(), data: $validator->validated());

        if (!$result->success) {
            if ($request->wantsJson()) return response()->json(['message' => $result->message], $result->status);
            return redirect()->route('dashboard')->with('error', $result->message);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $result->data]);
        }

        return redirect()->route('subjects.data')->with('success', 'Subject updated!');
    }

    public function data(): View
    {
        $subjects = Subject::where('user_id', auth()->id())
            ->with(['tasks.category'])
            ->orderBy('created_at', 'desc')
            ->get();

        $categories = auth()->user()->categories()->orderBy('name')->get();

        return view('users.data', compact('subjects', 'categories'));
    }

    public function destroy(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $result = $this->subjectService->delete(id: $id, userId: auth()->id());

        if (!$result->success) {
            if ($request->wantsJson()) return response()->json(['message' => $result->message], $result->status);
            return redirect()->route('dashboard')->with('error', $result->message);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $result->message]);
        }

        return redirect()->route('subjects.data')->with('success', 'Subject deleted!');
    }
}