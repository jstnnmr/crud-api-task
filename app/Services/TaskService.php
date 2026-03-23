<?php  

namespace App\Services;
use App\Repositories\TaskRepository;

class TaskService
{
    protected $taskRepository;

    public function __construct(TaskRepository $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    public function createTask(array $data)
    {
        return $this->taskRepository->create($data);
    }

    public function getTaskById(int $id)
    {
        return $this->taskRepository->findById($id);
    }

    public function updateTask(int $id, array $data)
    {
        return $this->taskRepository->update($id, $data);
    }

    public function deleteTask(int $id)
    {
        return $this->taskRepository->delete($id);
    }

    public function getAllTasks()
    {
        return $this->taskRepository->getAll();
    }
}