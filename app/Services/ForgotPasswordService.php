<?php

namespace App\Services;

use App\Mail\ForgotPasswordMail;
use App\Models\User;
use App\Repositories\PasswordResetRepository;
use App\Support\ServiceReturn;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordService
{
    protected PasswordResetRepository $passwordResetRepository;

    public function __construct(PasswordResetRepository $passwordResetRepository)
    {
        $this->passwordResetRepository = $passwordResetRepository;
    }

    public function sendResetLink(string $email): ServiceReturn
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return ServiceReturn::error(message: 'Email not found.', status: 404);
        }

        try {
            $token = $this->passwordResetRepository->createToken(email: $email);

            $resetUrl = url('/reset-password/' . $token);

            Mail::to($user->email)->send(new ForgotPasswordMail(resetUrl: $resetUrl));

            return ServiceReturn::success(message: 'A reset link has been sent to your email.');
        } catch (\Exception $e) {
            return ServiceReturn::error(message: 'Failed to send reset email. Please try again.', status: 500);
        }
    }

    public function reset(array $data): ServiceReturn
    {
        $record = $this->passwordResetRepository->findValidToken(
            email: $data['email'],
            token: $data['token']
        );

        if (!$record) {
            return ServiceReturn::error(message: 'Invalid or expired reset token.', status: 400);
        }

        try {
            $user = User::where('email', $data['email'])->first();

            if (!$user) {
                return ServiceReturn::error(message: 'User not found.', status: 404);
            }

            $user->update([
                'password'                  => Hash::make($data['password']),
                'email_verified_at'         => $user->email_verified_at ?? now(),
                'verification_code'         => null,
                'verification_code_expires_at' => null,
            ]);

            $this->passwordResetRepository->deleteToken(email: $data['email']);

            return ServiceReturn::success(message: 'Password has been reset successfully.');
        } catch (\Exception $e) {
            return ServiceReturn::error(message: 'Failed to reset password. Please try again.', status: 500);
        }
    }
}
