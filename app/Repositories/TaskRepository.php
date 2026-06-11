<?php

namespace App\Repositories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

class TaskRepository
{
    public function getAllByUser(int $userId): Collection
    {
        return Task::whereHas('subject', fn($q) => $q->where('user_id', $userId))
            ->with(['subject', 'category'])
            ->get();
    }

    public function findByIdAndUser(int $id, int $userId): ?Task
    {
        return Task::where(function ($q) use ($userId) {
            $q->whereHas('subject', fn($sq) => $sq->where('user_id', $userId))
              ->orWhereHas('collaborators', fn($cq) => $cq->where('user_id', $userId));
        })->where('id', $id)->first();
    }

    public function findOwnedById(int $id, int $userId): ?Task
    {
        return Task::whereHas('subject', fn($q) => $q->where('user_id', $userId))
            ->where('id', $id)
            ->first();
    }

    public function create(array $data): Task
    {
        return Task::create($data);
    }

    public function update(int $id, array $data): Task
    {
        $task = Task::findOrFail($id);
        $task->update($data);
        return $task;
    }

    public function delete(int $id): bool
    {
        $task = Task::findOrFail($id);
        return $task->delete();
    }

    public function getAll(): Collection
    {
        return Task::with(['subject', 'category'])->get();
    }

    public function findById(int $id): Task
    {
        return Task::findOrFail($id);
    }
}