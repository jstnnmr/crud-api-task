<?php
// routes/api.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\TeamController;

// Forgot Password (no auth required)
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink']);
Route::post('/reset-password', [ForgotPasswordController::class, 'reset']);

// Email Verification (no auth required)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/verify-email', [AuthController::class, 'verify']);
Route::post('/verify-email/resend', [AuthController::class, 'resendCode']);

Route::middleware('auth:sanctum')->group(function () {

    // Account
    Route::get('account', [AccountController::class, 'show']);
    Route::put('account', [AccountController::class, 'updateProfile']);
    Route::put('account/password', [AccountController::class, 'updatePassword']);
    Route::post('account/password/confirm', [AccountController::class, 'confirmPasswordChange']);
    Route::post('account/photo', [AccountController::class, 'updatePhoto']);

    // Notes
    Route::get('notes', [NoteController::class, 'list']);
    Route::post('notes', [NoteController::class, 'store']);
    Route::get('notes/{id}', [NoteController::class, 'show']);
    Route::put('notes/{id}', [NoteController::class, 'update']);
    Route::delete('notes/{id}', [NoteController::class, 'destroy']);
    Route::post('notes/{id}/invite', [NoteController::class, 'invite']);
    Route::delete('notes/{id}/collaborators/{collaboratorId}', [NoteController::class, 'removeCollaborator']);

    // Team
    Route::get('my-tasks', [TeamController::class, 'getMyTasks']);
    Route::get('team/invitations', [TeamController::class, 'getInvitations']);
    Route::post('team/invite', [TeamController::class, 'invite']);
    Route::post('team/invitations/{token}/accept', [TeamController::class, 'acceptInvitation']);
    Route::post('team/invitations/{token}/decline', [TeamController::class, 'declineInvitation']);
    Route::get('team/tasks/{taskId}/collaborators', [TeamController::class, 'getCollaborators']);
    Route::delete('team/tasks/{taskId}/collaborators/{collaboratorId}', [TeamController::class, 'removeCollaborator']);
    Route::get('team/tasks/{taskId}/activities', [TeamController::class, 'getActivities']);

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