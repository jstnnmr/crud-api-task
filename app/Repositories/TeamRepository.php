<?php

namespace App\Repositories;

use App\Models\Task;
use App\Models\TaskActivity;
use App\Models\TaskInvitation;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class TeamRepository
{
    public function findTasksForUser(int $userId): Collection
    {
        return Task::where(function ($q) use ($userId) {
            $q->whereHas('subject', fn($sq) => $sq->where('user_id', $userId))
              ->orWhereHas('collaborators', fn($cq) => $cq->where('user_id', $userId));
        })->with(['subject', 'category', 'collaborators'])->get();
    }

    public function findTaskForUser(int $taskId, int $userId): ?Task
    {
        return Task::where(function ($q) use ($userId) {
            $q->whereHas('subject', fn($sq) => $sq->where('user_id', $userId))
              ->orWhereHas('collaborators', fn($cq) => $cq->where('user_id', $userId));
        })->with(['subject', 'category', 'collaborators'])->find($taskId);
    }

    public function findOwnedTask(int $taskId, int $userId): ?Task
    {
        return Task::whereHas('subject', fn($q) => $q->where('user_id', $userId))
            ->with(['subject', 'category'])
            ->find($taskId);
    }

    public function findPendingInvitationsByEmail(string $email): Collection
    {
        return TaskInvitation::where('invited_email', $email)
            ->where('status', 'pending')
            ->with(['task.subject', 'inviter'])
            ->get();
    }

    public function findInvitationByToken(string $token): ?TaskInvitation
    {
        return TaskInvitation::where('token', $token)
            ->with(['task.subject', 'inviter'])
            ->first();
    }

    public function logActivity(int $taskId, int $userId, string $action, ?array $changes = null): TaskActivity
    {
        return TaskActivity::create([
            'task_id' => $taskId,
            'user_id' => $userId,
            'action'  => $action,
            'changes' => $changes,
        ]);
    }

    public function getActivities(int $taskId): Collection
    {
        return TaskActivity::where('task_id', $taskId)
            ->with('user')
            ->latest()
            ->get();
    }
}
