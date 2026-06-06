<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TaskService;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * @OA\Get(
     *   path="/api/tasks",
     *   summary="Get all tasks",
     *   description="Returns a list of all tasks",
     *   operationId="getTasks",
     *   tags={"Tasks"},
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\JsonContent()
     *   )
     * )
     */
    public function index()
    {
        $tasks = $this->taskService->getAllTasks();
        if (request()->is('api/*') || request()->wantsJson()) {
            return response()->json($tasks);
        }
        return redirect()->route('users.index');
    }

    /**
     * @OA\Get(
     *   path="/api/tasks/{id}",
     *   summary="Get task by ID",
     *   description="Returns a single task by its ID",
     *   operationId="getTaskById",
     *   tags={"Tasks"},
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\JsonContent()
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="Task not found",
     *     @OA\JsonContent(
     *       @OA\Property(property="success", type="boolean", example=false),
     *       @OA\Property(property="message", type="string", example="Task not found")
     *     )
     *   )
     * )
     */
    public function show(Request $request, $id)
    {
        $task = $this->taskService->getTaskById($id);

        if (!$task) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Task not found'
                ], 404);
            }
            return redirect()->route('users.index')->with('error', 'Task not found');
        }

        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $task
            ]);
        }

        return view('tasks.show', compact('task'));
    }

    /**
     * @OA\Post(
     *   path="/api/tasks",
     *   summary="Create a task",
     *   description="Creates a new task and returns it",
     *   operationId="createTask",
     *   tags={"Tasks"},
     *   @OA\RequestBody(
     *     required=true,
     *     description="Pass required task fields",
     *     @OA\JsonContent(
     *       required={"user_id", "title", "status"},
     *       @OA\Property(property="user_id", type="integer", example=1),
     *       @OA\Property(property="title", type="string", example="My Task"),
     *       @OA\Property(property="description", type="string", example="Task description"),
     *       @OA\Property(property="status", type="string", enum={"pending", "in_progress", "completed"}, example="pending"),
     *       @OA\Property(property="due_date", type="string", format="date", example="2026-12-31")
     *     )
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Task created successfully",
     *     @OA\JsonContent(
     *       @OA\Property(property="success", type="boolean", example=true),
     *       @OA\Property(property="message", type="string", example="Task created successfully")
     *     )
     *   ),
     *   @OA\Response(
     *     response=422,
     *     description="Validation error",
     *     @OA\JsonContent(
     *       @OA\Property(property="success", type="boolean", example=false),
     *       @OA\Property(property="message", type="string", example="Validation failed"),
     *       @OA\Property(property="errors", type="object")
     *     )
     *   )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'     => 'required|exists:users,id',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'required|in:pending,in_progress,completed',
            'due_date'    => 'nullable|date',
        ]);
        if ($validator->fails()) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors'  => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $task = $this->taskService->createTask($validator->validated());
        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Task created successfully',
                'data'    => $task
            ], 201);
        }
        return redirect()->route('users.index')->with('success', 'Task created successfully');
    }

    /**
     * @OA\Put(
     *   path="/api/tasks/{id}",
     *   summary="Update a task",
     *   description="Updates an existing task by its ID",
     *   operationId="updateTask",
     *   tags={"Tasks"},
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\RequestBody(
     *     required=false,
     *     description="Pass fields to update",
     *     @OA\JsonContent(
     *       @OA\Property(property="title", type="string", example="Updated Task"),
     *       @OA\Property(property="description", type="string", example="Updated description"),
     *       @OA\Property(property="status", type="string", enum={"pending", "in_progress", "completed"}, example="in_progress"),
     *       @OA\Property(property="due_date", type="string", format="date", example="2026-12-31")
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Task updated successfully",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Task updated successfully")
     *     )
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="Task not found",
     *     @OA\JsonContent(
     *       @OA\Property(property="success", type="boolean", example=false),
     *       @OA\Property(property="message", type="string", example="Task not found")
     *     )
     *   ),
     *   @OA\Response(
     *     response=422,
     *     description="Validation error",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string")
     *     )
     *   )
     * )
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title'       => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'sometimes|in:pending,in_progress,completed',
            'due_date'    => 'nullable|date',
        ]);

        $task = $this->taskService->updateTask($id, $validated);
        if (!$task) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Task not found'
                ], 404);
            }
            return redirect()->route('users.index')->with('error', 'Task not found');
        }
        if (request()->is('api/*') || request()->wantsJson()) {
            return response()->json(['message' => 'Task updated successfully', 'data' => $task], 200);
        }
        return redirect()->route('users.index')->with('success', 'Task updated successfully');
    }

    /**
     * @OA\Delete(
     *   path="/api/tasks/{id}",
     *   summary="Delete a task",
     *   description="Deletes a task by its ID",
     *   operationId="deleteTask",
     *   tags={"Tasks"},
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Task deleted successfully",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Task deleted successfully")
     *     )
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="Task not found",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Task not found")
     *     )
     *   )
     * )
     */
    public function destroy($id)
    {
        $this->taskService->deleteTask($id);

        if (request()->is('api/*') || request()->wantsJson()) {
            return response()->json(['message' => 'Task deleted successfully'], 200);
        }
        return redirect()->route('users.index')->with('success', 'Task deleted successfully');
    }

    /**
         * @OA\Post(
         *   path="/api/tasks/{id}/complete",
         *   summary="Complete a task",
         *   description="Marks a task as completed and assigns a reward",
         *   operationId="completeTask",
         *   tags={"Tasks"},
         *   @OA\Parameter(
         *     name="id",
         *     in="path",
         *     required=true,
         *     @OA\Schema(type="integer")
         *   ),
         *   @OA\Response(
         *     response=200,
         *     description="Task completed and reward assigned",
         *     @OA\JsonContent(
         *       @OA\Property(property="message", type="string", example="Task completed and reward assigned!"),
         *       @OA\Property(property="data", type="object")
         *     )
         *   ),
         *   @OA\Response(
         *     response=500,
         *     description="Failed to complete task",
         *     @OA\JsonContent(
         *       @OA\Property(property="error", type="string", example="Failed to complete task: some error message")
         *     )
         *   )
         * )
         */
        public function complete(int $id)
    {
        try {
            $task = $this->taskService->completeTask($id);
            return response()->json([
                'message' => 'Task completed and reward assigned!',
                'data' => $task
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to complete task: ' . $e->getMessage()], 500);
        }
    }
}