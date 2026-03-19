<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;

class TaskController extends Controller
{
    public function index (Request $request)
    {

    $tasks = Task::when($request->has('user_id'), function ($query) use ($request) {
                    $query->where('user_id', $request->user_id);
                })
                ->when($request->has('status'), function ($query) use ($request) {
                    $query->where('status', $request->status);
                })
                ->when($request->has('sort') && $request->sort === 'due_date', function ($query) {
                    $query->orderBy('due_date');
                })
                ->get();

        // $tasks = Task::where('user_id', $request->user_id)
        //     ->when($request->has('status'), function ($query) use ($request) {
        //         $query->where('status', $request->status);
        //     })
        //     ->when($request->has('sort') && $request->sort === 'due_date', function ($query) {
        //         $query->orderBy('due_date');
        //     })
        //     ->get();


        // //filter response
        // if ($request->has('status')) {
        //     $tasks->where('status', $request->status);
        // }

        // if ($request->has('user_id')) {
        //     $tasks->where('user_id', $request->user_id);
        // }

        // if ($request->has('sort') && $request->sort === 'due_date') {
        //     $tasks->orderBy('due_date');
        // }
        
        //return response as JSON
        //dd($request->expectsJson(),$request->headers);
            if ($request->expectsJson()) {
            return response()->json($tasks, 200);
        }

        // Otherwise, return the standard HTML view
        return view('tasks.data', compact('tasks'));
    }

    // POST /api/tasks - Create a task
    public function store(Request $request)
    {
        $task = Task::create([
            'user_id'     => $request->user_id,
            'title'       => $request->title,
            'description' => $request->description,
            'status'      => $request->status,
            'due_date'    => $request->due_date,
        ]);

            return response()->json([
                'message' => 'Task created successfully',
                'data'    => $task
            ], 201);
    }

    // GET /api/tasks/{id} - View a single task
    public function show($id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json([
                'message' => 'Task not found'
            ], 404);
        }

        return response()->json($task, 200);
    }

    // PUT /api/tasks/{id} - Update a task
    public function update(Request $request, $id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json([
                'message' => 'Task not found'
            ], 404);
        }

        $request->validate([
        'user_id'     => 'required|exists:users,id',
        'title'       => 'required|string|max:255',
        'description' => 'nullable|string',
        'status'      => 'required|in:pending,in_progress,completed',
        'due_date'    => 'nullable|date',
    ]);

        return response()->json([
            'message' => 'Task updated successfully',
            'data'    => $task
        ], 200);
    }

    // DELETE /api/tasks/{id} - Delete a task
    public function destroy($id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json([
                'message' => 'Task not found'
            ], 404);
        }

        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully'
        ], 200);
    }

}
