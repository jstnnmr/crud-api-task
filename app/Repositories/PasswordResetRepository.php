<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PasswordResetRepository
{
    public function createToken(string $email): string
    {
        $token = Str::random(60);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            ['token' => $token, 'created_at' => now()]
        );

        return $token;
    }

    public function findValidToken(string $email, string $token): ?object
    {
        $record = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->where('token', $token)
            ->first();

        if (!$record) {
            return null;
        }

        $expiresAt = strtotime($record->created_at . '+60 minutes');
        if (time() > $expiresAt) {
            $this->deleteToken(email: $email);
            return null;
        }

        return $record;
    }

    public function deleteToken(string $email): void
    {
        DB::table('password_reset_tokens')->where('email', $email)->delete();
    }
}
