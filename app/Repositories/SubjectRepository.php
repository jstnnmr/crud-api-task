<?php

namespace App\Repositories;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Collection;

class SubjectRepository
{
    public function getAllByUser(int $userId, int $perPage = 5): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Subject::where('user_id', $userId)
            ->withCount('tasks')
            ->withCount(['tasks as completed_tasks_count' => fn($q) => $q->where('status', 'completed')])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function findByIdAndUser(int $id, int $userId): ?Subject
    {
        return Subject::where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }

    public function findByIdAndUserWithTasks(int $id, int $userId): ?Subject
    {
        return Subject::where('id', $id)
            ->where('user_id', $userId)
            ->with(['tasks.category'])
            ->first();
    }

    public function create(array $data): Subject
    {
        return Subject::create($data);
    }

    public function update(Subject $subject, array $data): Subject
    {
        $subject->update($data);
        return $subject;
    }

    public function delete(Subject $subject): bool
    {
        return $subject->delete();
    }
}