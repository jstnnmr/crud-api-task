<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\AccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    protected AccountService $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    public function index(): View
    {
        $user = auth()->user();
        $result = $this->accountService->show(user: $user);
        return view('account.index', ['account' => $result->data]);
    }

    public function show(Request $request): JsonResponse
    {
        $result = $this->accountService->show(user: $request->user());
        return response()->json($result, $result->status);
    }

    public function updateProfile(Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name'  => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255'],
        ]);

        $result = $this->accountService->updateProfile(user: $request->user(), data: $validated);

        if (!$request->expectsJson()) {
            if ($result->success) {
                return back()->with('status', $result->message);
            }
            return back()->withErrors(['email' => $result->message]);
        }

        return response()->json($result, $result->status);
    }

    public function updatePassword(Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password'     => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $result = $this->accountService->sendPasswordChangeCode(user: $request->user(), data: $validated);

        if (!$request->expectsJson()) {
            if ($result->success) {
                return back()->with('status', $result->message);
            }
            return back()->withErrors(['current_password' => $result->message]);
        }

        return response()->json($result, $result->status);
    }

    public function confirmPasswordChange(Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'code'         => ['required', 'string', 'size:6'],
            'new_password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $result = $this->accountService->confirmPasswordChange(user: $request->user(), data: $validated);

        if (!$request->expectsJson()) {
            if ($result->success) {
                return back()->with('status', $result->message);
            }
            return back()->withErrors(['code' => $result->message]);
        }

        return response()->json($result, $result->status);
    }

    public function updatePhoto(Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:2048'],
        ]);

        $result = $this->accountService->updatePhoto(user: $request->user(), photo: $validated['photo']);

        if (!$request->expectsJson()) {
            if ($result->success) {
                return back()->with('status', $result->message);
            }
            return back()->withErrors(['photo' => $result->message]);
        }

        return response()->json($result, $result->status);
    }
}
