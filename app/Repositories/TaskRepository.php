<?php

namespace App\Repositories;

use App\Models\Task;

class TaskRepository
{
    public function create(array $data): Task
    {
        return Task::create($data);
    }

    public function findById($id)
    {
        return Task::find($id);
    }

    public function delete($id)
    {
        $task = Task::find($id);
        if (!$task) return null;
        return $task->delete();
    }

    public function update($id, array $data)
    {
        $task = Task::find($id);
        if (!$task) return null;
        $task->update($data);
        return $task;
    }
    
    public function getAll()
    {
        return Task::all();
    }
}