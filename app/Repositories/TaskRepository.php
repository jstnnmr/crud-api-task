<?php

namespace App\Repositories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

class TaskRepository
{
    public function getAllByUser(int $userId, ?string $status = null, ?string $sort = null, int $perPage = 10): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Task::whereHas('subject', fn($q) => $q->where('user_id', $userId))
            ->with(['subject', 'category', 'collaborators']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($sort === 'due_date') {
            $query->orderBy('due_date', 'asc');
        } elseif ($sort === 'priority') {
            $query->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END");
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query->paginate($perPage);
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