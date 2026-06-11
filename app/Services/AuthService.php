<?php

namespace App\Services;

use App\Mail\VerifyEmailMail;
use App\Models\User;
use App\Repositories\AuthRepository;
use App\Support\ServiceReturn;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

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
            $user = $this->authRepository->create(data: $data);

            $code = $this->generateVerificationCode(user: $user);

            Mail::to($user->email)->send(new VerifyEmailMail(code: $code, userName: $user->name));

            return ServiceReturn::success(
                data: ['email' => $user->email],
                message: 'Account created. Please check your email for the verification code.',
                status: 201
            );
        } catch (\Exception $e) {
            return ServiceReturn::error(message: 'Registration failed: ' . $e->getMessage(), status: 500);
        }
    }

    public function verifyEmail(string $email, string $code): ServiceReturn
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return ServiceReturn::error(message: 'User not found.', status: 404);
        }

        if ($user->email_verified_at) {
            Auth::login($user);
            return ServiceReturn::success(message: 'Email already verified.');
        }

        if (!$user->verification_code || trim($user->verification_code) !== trim($code)) {
            return ServiceReturn::error(message: 'Invalid verification code.', status: 400);
        }

        if ($user->verification_code_expires_at && now()->gt($user->verification_code_expires_at)) {
            return ServiceReturn::error(message: 'Verification code has expired. Request a new one.', status: 400);
        }

        $user->update([
            'email_verified_at'         => now(),
            'verification_code'         => null,
            'verification_code_expires_at' => null,
        ]);

        Auth::login($user, remember: true);

        return ServiceReturn::success(message: 'Email verified successfully.');
    }

    public function resendVerificationCode(string $email): ServiceReturn
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return ServiceReturn::error(message: 'User not found.', status: 404);
        }

        if ($user->email_verified_at) {
            return ServiceReturn::success(message: 'Email already verified.');
        }

        $code = $this->generateVerificationCode(user: $user);

        Mail::to($user->email)->send(new VerifyEmailMail(code: $code, userName: $user->name));

        return ServiceReturn::success(message: 'A new verification code has been sent to your email.');
    }

    public function login(array $data): ServiceReturn
    {
        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return ServiceReturn::error(message: 'Invalid credentials', status: 401);
        }

        Auth::login($user, remember: true);
        $token = $user->createToken('auth_token')->plainTextToken;

        return ServiceReturn::success(data: ['user' => $user, 'token' => $token]);
    }

    private function generateVerificationCode(User $user): string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->update([
            'verification_code'           => $code,
            'verification_code_expires_at' => now()->addMinutes(10),
        ]);

        return $code;
    }
}
