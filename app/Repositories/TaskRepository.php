<?php

namespace App\Repositories;

use App\Models\Task;

class TaskRepository
{
    public function create(array $data): Task
    {
        return Task::create($data);
    }

    public function findById(int $id): Task
    {
        return Task::findOrFail($id);
    }

    public function update(int $id, array $data): Task
    {
        $task = Task::findOrFail($id);
        $task->update($data);
        return $task;
    }

    public function delete(int $id): bool
    {
        return Task::destroy($id);
    }

    public function getAll()
    {
        return Task::all();
    }
}