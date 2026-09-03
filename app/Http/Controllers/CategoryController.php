<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Services\CategoryService;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryService $categoryService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 10);
        $result = $this->categoryService->getAllPaginated(userId: auth()->id(), perPage: $perPage);
        return response()->json(['success' => true, 'data' => $result->data]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) return response()->json(['errors' => $validator->errors()], 422);
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $result = $this->categoryService->create(
            userId: auth()->id(),
            name: $validator->validated()['name']
        );

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $result->data], 201);
        }

        return redirect()->back()->with('success', 'Category added!');
    }

    public function update(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) return response()->json(['errors' => $validator->errors()], 422);
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $result = $this->categoryService->update(
            id: $id,
            userId: auth()->id(),
            name: $validator->validated()['name']
        );

        if (!$result->success) {
            if ($request->wantsJson()) return response()->json(['message' => $result->message], $result->status);
            return redirect()->back()->with('error', $result->message);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $result->data]);
        }

        return redirect()->back()->with('success', 'Category updated!');
    }

    public function destroy(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $result = $this->categoryService->delete(id: $id, userId: auth()->id());

        if (!$result->success) {
            if ($request->wantsJson()) return response()->json(['message' => $result->message], $result->status);
            return redirect()->back()->with('error', $result->message);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $result->message]);
        }

        return redirect()->back()->with('success', 'Category deleted!');
    }
}