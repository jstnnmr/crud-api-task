<?php

namespace App\Http\Resources;

use App\Models\User;
use App\Services\ProductivityService;

class AiContextResource
{
    public static function fromUser(User $user, ProductivityService $productivity): array
    {
        $today = now()->toDateString();

        return [
            'overdue' => $user->tasks()
                ->whereIn('status', ['pending', 'in_progress'])
                ->whereDate('due_date', '<', $today)
                ->count(),
            'due_today' => $user->tasks()
                ->whereIn('status', ['pending', 'in_progress'])
                ->whereDate('due_date', '=', $today)
                ->count(),
            'streak' => $productivity->getStreak($user->id)['current'],
        ];
    }
}
