<?php  

namespace App\Services;
use App\Repositories\TaskRepository;
use App\Models\Transaction; // <--- ADD THIS
use Illuminate\Support\Facades\DB; // <--- ADD THIS

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

    public function completeTask(int $id)
    {
        return DB::transaction(function () use ($id) {
            $task = $this->taskRepository->findById($id);

            // 1. Update status via repository
            $this->taskRepository->update($id, [
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // 2. Add transaction record
            Transaction::create([
                'user_id' => $task->child_id,
                'amount' => $task->coins_earned,
                'description' => "Completed task: {$task->title}",
                'type' => 'earning',
            ]);

            // 3. Update child's balance
            $task->child->increment('coin_balance', $task->coins_earned);

            return $task;
        });
    }
}