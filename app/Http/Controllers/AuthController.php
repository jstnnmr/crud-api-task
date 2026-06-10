<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;


class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function showLogin(): View|\Illuminate\Http\RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->intended('/data');
        }
        return view('auth.login');
    }

    public function showRegister(): View|\Illuminate\Http\RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->intended('/data');
        }
        return view('auth.register');
    }

    public function showVerifyForm(Request $request): View
    {
        return view('auth.verify-email', ['email' => $request->get('email')]);
    }

    public function register(Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'regex:/[0-9]/', 'confirmed'],
        ], [
            'password.regex'      => 'The password must contain at least one number.',
            'password.min'        => 'The password must be at least 8 characters.',
            'password.confirmed'  => 'The password confirmation does not match.',
        ]);

        $result = $this->authService->register(data: $validated);

        if (!$request->expectsJson()) {
            if ($result->success) {
                return redirect('/verify-email?email=' . $validated['email'])
                    ->with('status', $result->message);
            }
            return back()->withErrors(['email' => $result->message]);
        }

        return response()->json($result, $result->status);
    }

    public function verify(Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'code'  => ['required', 'string', 'size:6'],
        ]);

        $result = $this->authService->verifyEmail(email: $validated['email'], code: $validated['code']);

        if (!$request->expectsJson()) {
            if ($result->success) {
                return redirect()->intended('/data');
            }
            return back()->withErrors(['code' => $result->message]);
        }

        return response()->json($result, $result->status);
    }

    public function resendCode(Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $result = $this->authService->resendVerificationCode(email: $validated['email']);

        if (!$request->expectsJson()) {
            if ($result->success) {
                return back()->with('status', $result->message);
            }
            return back()->withErrors(['email' => $result->message]);
        }

        return response()->json($result, $result->status);
    }

    public function login(Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $result = $this->authService->login($validated);

        if (!$request->expectsJson()) {
            if ($result->success) {
                return redirect()->intended('/data');
            }
            return back()->withErrors(['email' => $result->message]);
        }

        return response()->json($result, $result->status);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
