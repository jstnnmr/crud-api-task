<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TaskService;
use OpenApi\Attributes as OA;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    #[OA\Get(path: '/api/tasks', summary: 'Get all tasks', tags: ['Tasks'],
        responses: [new OA\Response(response: 200, description: 'Success')]
    )]
    public function index()
    {
        $tasks = $this->taskService->getAllTasks();
        if (request()->is('api/*') || request()->wantsJson()) {
            return response()->json($tasks);
        }
        return redirect()->route('users.index');
    }

    #[OA\Get(path: '/api/tasks/{id}', summary: 'Get task by ID', tags: ['Tasks'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Success'),
            new OA\Response(response: 404, description: 'Task not found')
        ]
    )]
    public function show(Request $request, $id)
    {
        $task = $this->taskService->getTaskById($id);

        //Check if task exists FIRST
        if (!$task) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Task not found'
                ], 404);
            }
            return redirect()->route('users.index')->with('error', 'Task not found');
        }

        //Task exists, return the data
        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $task
            ]);
        }

        return view('tasks.show', compact('task'));
    }

    #[OA\Post(path: '/api/tasks', summary: 'Create a task', tags: ['Tasks'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['user_id', 'title', 'status'],
                properties: [
                    new OA\Property(property: 'user_id', type: 'integer', example: 1),
                    new OA\Property(property: 'title', type: 'string', example: 'My Task'),
                    new OA\Property(property: 'description', type: 'string', example: 'Task description'),
                    new OA\Property(property: 'status', type: 'string', enum: ['pending', 'in_progress', 'completed'], example: 'pending'),
                    new OA\Property(property: 'due_date', type: 'string', format: 'date', example: '2026-12-31'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Task created successfully'),
            new OA\Response(response: 422, description: 'Validation error')
        ]
    )]
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

    #[OA\Put(path: '/api/tasks/{id}', summary: 'Update a task', tags: ['Tasks'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'Updated Task'),
                    new OA\Property(property: 'description', type: 'string', example: 'Updated description'),
                    new OA\Property(property: 'status', type: 'string', enum: ['pending', 'in_progress', 'completed']),
                    new OA\Property(property: 'due_date', type: 'string', format: 'date', example: '2026-12-31'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Task updated successfully'),
            new OA\Response(response: 404, description: 'Task not found'),
            new OA\Response(response: 422, description: 'Validation error')
        ]
    )]
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

    #[OA\Delete(path: '/api/tasks/{id}', summary: 'Delete a task', tags: ['Tasks'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Task deleted successfully'),
            new OA\Response(response: 404, description: 'Task not found')
        ]
    )]
    public function destroy($id)
    {
        $this->taskService->deleteTask($id);

        if (request()->is('api/*') || request()->wantsJson()) {
            return response()->json(['message' => 'Task deleted successfully'], 200);
        }
        return redirect()->route('users.index')->with('success', 'Task deleted successfully');
    }
}