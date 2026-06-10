<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ForgotPasswordService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    protected ForgotPasswordService $forgotPasswordService;

    public function __construct(ForgotPasswordService $forgotPasswordService)
    {
        $this->forgotPasswordService = $forgotPasswordService;
    }

    public function showForgotForm(): View
    {
        return view('auth.forgot-password');
    }

    public function showResetForm(string $token): View
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    public function sendResetLink(Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        $result = $this->forgotPasswordService->sendResetLink(email: $validated['email']);

        if (!$request->expectsJson()) {
            if ($result->success) {
                return back()->with('status', $result->message);
            }
            return back()->withErrors(['email' => $result->message]);
        }

        return response()->json($result, $result->status);
    }

    public function reset(Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'email'    => ['required', 'string', 'email', 'max:255'],
            'token'    => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'regex:/[0-9]/', 'confirmed'],
        ]);

        $result = $this->forgotPasswordService->reset(data: $validated);

        if (!$request->expectsJson()) {
            if ($result->success) {
                return redirect('/login')->with('status', $result->message);
            }
            return back()->withErrors(['email' => $result->message]);
        }

        return response()->json($result, $result->status);
    }
}
