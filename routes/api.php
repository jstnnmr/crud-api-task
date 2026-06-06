<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\TaskController;

// Public API routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected API routes (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('users', UsersController::class);
    Route::apiResource('tasks', TaskController::class);
    
    // Custom Task completion
    Route::patch('/tasks/{id}/complete', [TaskController::class, 'complete'])->name('tasks.complete');
});