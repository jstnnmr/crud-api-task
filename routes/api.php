<?php
// routes/api.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TaskController;

Route::middleware('auth:sanctum')->group(function () {

    // Subjects
    Route::apiResource('subjects', SubjectController::class)->names('api.subjects');

    // Categories
    Route::apiResource('categories', CategoryController::class)->names('api.categories');

    // Tasks
    Route::apiResource('tasks', TaskController::class)->names('api.tasks');
    Route::patch('tasks/{id}/complete', [TaskController::class, 'complete']);

    // User stats
    Route::get('me/stats', fn() => response()->json([
        'total_points' => auth()->user()->total_points,
        'tasks_completed' => auth()->user()->tasks()->where('status', 'completed')->count(),
        'tasks_pending' => auth()->user()->tasks()->where('status', 'pending')->count(),
    ]));
});