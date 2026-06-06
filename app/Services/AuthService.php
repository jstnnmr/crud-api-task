<?php

namespace App\Services;

use App\Repositories\AuthRepository;
use App\Support\ServiceReturn;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    protected $authRepository;

    public function __construct(AuthRepository $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    public function register(array $data): ServiceReturn
    {
        try {
            // 1. Create the user
            $user = $this->authRepository->create($data);
            
            // 2. Automatically log the user in
            Auth::login($user);
            
            // 3. Create token for API usage (optional, but good for consistency)
            $token = $user->createToken('auth_token')->plainTextToken;
            
            return ServiceReturn::success(
                data: ['user' => $user, 'token' => $token], 
                message: 'Registered and logged in successfully', 
                status: 201
            );
        } catch (\Exception $e) {
            return ServiceReturn::error(message: 'Registration failed: ' . $e->getMessage(), status: 500);
        }
    }

    public function login(array $data): ServiceReturn
    {
        if (!Auth::attempt(['email' => $data['email'], 'password' => $data['password']])) {
            return ServiceReturn::error(message: 'Invalid credentials', status: 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return ServiceReturn::success(data: ['user' => $user, 'token' => $token]);
    }
}