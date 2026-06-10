<?php

namespace App\Services;

use App\Mail\PasswordChangeMail;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Support\ServiceReturn;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class AccountService
{
    protected UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function show(User $user): ServiceReturn
    {
        $data = [
            'name'  => $user->name,
            'email' => $user->email,
            'photo' => $user->photo ? asset('storage/' . $user->photo) : null,
            'role'  => $user->role,
        ];

        return ServiceReturn::success(data: $data);
    }

    public function updateProfile(User $user, array $data): ServiceReturn
    {
        $updateData = [];

        if (isset($data['name']) && $data['name'] !== $user->name) {
            $updateData['name'] = $data['name'];
        }

        if (isset($data['email']) && $data['email'] !== $user->email) {
            $existing = User::where('email', $data['email'])->where('id', '!=', $user->id)->first();
            if ($existing) {
                return ServiceReturn::error(message: 'Email is already taken.', status: 422);
            }
            $updateData['email'] = $data['email'];
        }

        if (empty($updateData)) {
            return ServiceReturn::success(message: 'Nothing to update.');
        }

        $this->userRepository->update(id: $user->id, data: $updateData);

        return ServiceReturn::success(message: 'Profile updated successfully.');
    }

    public function sendPasswordChangeCode(User $user, array $data): ServiceReturn
    {
        if (!Hash::check($data['current_password'], $user->password)) {
            return ServiceReturn::error(message: 'Current password is incorrect.', status: 403);
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->update([
            'verification_code'           => $code,
            'verification_code_expires_at' => now()->addMinutes(10),
        ]);

        try {
            Mail::to($user->email)->send(new PasswordChangeMail(code: $code, userName: $user->name));
        } catch (\Exception $e) {
            return ServiceReturn::error(message: 'Failed to send verification email.', status: 500);
        }

        return ServiceReturn::success(message: 'A verification code has been sent to your email.');
    }

    public function confirmPasswordChange(User $user, array $data): ServiceReturn
    {
        if (!$user->verification_code || trim($user->verification_code) !== trim($data['code'])) {
            return ServiceReturn::error(message: 'Invalid verification code.', status: 400);
        }

        if ($user->verification_code_expires_at && now()->gt($user->verification_code_expires_at)) {
            return ServiceReturn::error(message: 'Verification code has expired.', status: 400);
        }

        $this->userRepository->update(id: $user->id, data: [
            'password'                    => Hash::make($data['new_password']),
            'verification_code'           => null,
            'verification_code_expires_at' => null,
        ]);

        return ServiceReturn::success(message: 'Password updated successfully.');
    }

    public function updatePhoto(User $user, UploadedFile $photo): ServiceReturn
    {
        if ($user->photo) {
            Storage::disk('public')->delete($user->photo);
        }

        $path = $photo->store('photos', 'public');

        $this->userRepository->update(id: $user->id, data: ['photo' => $path]);

        return ServiceReturn::success(
            data: ['photo' => asset('storage/' . $path)],
            message: 'Photo updated successfully.'
        );
    }
}
