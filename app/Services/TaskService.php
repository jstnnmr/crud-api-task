<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use App\Models\Category;
use App\Models\TaskActivity;
use App\Repositories\TaskRepository;
use App\Support\ServiceReturn;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TaskService
{
    public function __construct(
        protected TaskRepository $taskRepository
    ) {}

    public function getAll(int $userId): ServiceReturn
    {
        $tasks = $this->taskRepository->getAllByUser(userId: $userId);
        return ServiceReturn::success(data: $tasks);
    }

    public function getById(int $id, int $userId): ServiceReturn
    {
        $task = $this->taskRepository->findByIdAndUser(id: $id, userId: $userId);
        if (!$task) {
            return ServiceReturn::error(message: 'Task not found', status: 404);
        }
        return ServiceReturn::success(data: $task);
    }

    public function createTask(int $userId, array $data): ServiceReturn
    {
        // Resolve category — pick existing id or create from typed name
        $data['category_id'] = $this->resolveCategory(
            userId: $userId,
            categoryId: $data['category_id'] ?? null,
            categoryName: $data['category_name'] ?? null
        );
        unset($data['category_name']);

        $task = $this->taskRepository->create(data: $data);

        TaskActivity::create([
            'task_id' => $task->id,
            'user_id' => $userId,
            'action'  => 'created',
        ]);

        return ServiceReturn::success(data: $task, status: 201);
    }

    public function updateTask(int $id, int $userId, array $data): ServiceReturn
    {
        $task = $this->taskRepository->findByIdAndUser(id: $id, userId: $userId);
        if (!$task) {
            return ServiceReturn::error(message: 'Task not found', status: 404);
        }

        $original = $task->toArray();

        // Resolve category
        $data['category_id'] = $this->resolveCategory(
            userId: $userId,
            categoryId: $data['category_id'] ?? null,
            categoryName: $data['category_name'] ?? null
        ) ?? $task->category_id;
        unset($data['category_name']);

        // Award points if being marked completed
        if (($data['status'] ?? null) === 'completed' && $task->status !== 'completed') {
            $points = Task::POINTS[$data['priority'] ?? $task->priority];
            $data['points_earned'] = $points;
            $data['completed_at']  = Carbon::now();
            User::where('id', $userId)->increment('total_points', $points);
        }

        $task = $this->taskRepository->update(id: $id, data: $data);

        $changes = collect($data)->only(['title', 'description', 'priority', 'status', 'due_date', 'category_id'])
            ->filter(fn($val, $key) => $original[$key] ?? null !== $val)
            ->toArray();

        if (!empty($changes)) {
            TaskActivity::create([
                'task_id' => $task->id,
                'user_id' => $userId,
                'action'  => 'updated',
                'changes' => $changes,
            ]);
        }

        return ServiceReturn::success(data: $task);
    }

    public function deleteTask(int $id, int $userId): ServiceReturn
    {
        $task = $this->taskRepository->findOwnedById(id: $id, userId: $userId);
        if (!$task) {
            return ServiceReturn::error(message: 'Task not found', status: 404);
        }

        // Deduct points if deleting a completed task
        if ($task->status === 'completed' && $task->points_earned > 0) {
            User::where('id', $userId)->decrement('total_points', $task->points_earned);
        }

        $this->taskRepository->delete(id: $id);
        return ServiceReturn::success(message: 'Task deleted');
    }

    public function completeTask(int $id, int $userId): ServiceReturn
    {
        $task = $this->taskRepository->findByIdAndUser(id: $id, userId: $userId);
        if (!$task) {
            return ServiceReturn::error(message: 'Task not found', status: 404);
        }

        if ($task->status === 'completed') {
            return ServiceReturn::error(message: 'Task already completed', status: 422);
        }

        $points = Task::POINTS[$task->priority];

        $task = $this->taskRepository->update(id: $id, data: [
            'status'        => 'completed',
            'points_earned' => $points,
            'completed_at'  => Carbon::now(),
        ]);

        User::where('id', $userId)->increment('total_points', $points);

        TaskActivity::create([
            'task_id' => $task->id,
            'user_id' => $userId,
            'action'  => 'completed',
        ]);

        return ServiceReturn::success(
            data: ['task' => $task, 'points_earned' => $points],
            message: "Task completed! +{$points} pts"
        );
    }

    private function resolveCategory(int $userId, ?int $categoryId, ?string $categoryName): ?int
    {
        if ($categoryId) return $categoryId;
        if ($categoryName) {
            return Category::firstOrCreate(
                ['user_id' => $userId, 'name' => $categoryName]
            )->id;
        }
        return null;
    }
}