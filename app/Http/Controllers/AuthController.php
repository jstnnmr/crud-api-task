<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function showLogin(): View
    {
        return view('auth.login'); // Ensure this view file exists in resources/views/auth/login.blade.php
    }

    public function showRegister(): View
    {
        return view('auth.register'); // Ensure this view file exists
    }

    public function register(Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6'],
            'role'     => ['sometimes', 'in:parent,child'],
        ]);

        $result = $this->authService->register(data: $validated);

        // If it's a web request, redirect to dashboard
        if (!$request->expectsJson()) {
            if ($result->success) {
                return redirect()->intended('/data');
            }
            return back()->withErrors(['email' => $result->message]);
        }

        // API response
        return response()->json($result, $result->status);
    }
    public function login(Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $result = $this->authService->login($validated);

        // If the request was made from a browser (Web), redirect
        if (!$request->expectsJson()) {
            if ($result->success) {
                return redirect()->intended('/data'); // Redirect to your data view
            }
            return back()->withErrors(['email' => $result->message]);
        }

        // Otherwise, return JSON (for mobile/Postman)
        return response()->json($result, $result->status);
    }
}