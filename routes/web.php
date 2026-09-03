<?php
// routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\AiAssistantController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\ProductivityController;

// Auth
Route::get('/register', [AuthController::class, 'showRegister']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/verify-email', [AuthController::class, 'showVerifyForm']);
Route::post('/verify-email', [AuthController::class, 'verify']);
Route::post('/verify-email/resend', [AuthController::class, 'resendCode']);
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Forgot Password
Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotForm']);
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink']);
Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ForgotPasswordController::class, 'reset']);

Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Subjects (web)
    Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects.index');
    Route::post('/subjects', [SubjectController::class, 'store'])->name('subjects.store');
    Route::get('/subjects/{id}', [SubjectController::class, 'show'])->name('subjects.show');
    Route::put('/subjects/{id}', [SubjectController::class, 'update'])->name('subjects.update');
    Route::delete('/subjects/{id}', [SubjectController::class, 'destroy'])->name('subjects.destroy');

    // Categories (web)
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{id}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    // Account
    Route::get('/account', [AccountController::class, 'index']);
    Route::post('/account/profile', [AccountController::class, 'updateProfile']);
    Route::post('/account/password', [AccountController::class, 'updatePassword']);
    Route::post('/account/password/confirm', [AccountController::class, 'confirmPasswordChange']);
    Route::post('/account/photo', [AccountController::class, 'updatePhoto']);

    // AI Assistant
    Route::get('/ai', [AiAssistantController::class, 'index']);
    Route::post('/ai/chat', [AiAssistantController::class, 'chat'])->middleware('throttle:30,1');
    Route::post('/ai/chat/stream', [AiAssistantController::class, 'stream'])->middleware('throttle:30,1');
    Route::get('/ai/sessions', [AiAssistantController::class, 'sessions']);
    Route::get('/ai/sessions/active', [AiAssistantController::class, 'activeSession']);
    Route::get('/ai/sessions/{session}/messages/latest', [AiAssistantController::class, 'latestMessage']);
    Route::get('/ai/sessions/{session}', [AiAssistantController::class, 'showSession']);
    Route::post('/ai/sessions', [AiAssistantController::class, 'createSession']);
    Route::delete('/ai/sessions/{session}', [AiAssistantController::class, 'deleteSession']);
    Route::post('/ai/sessions/{session}/fork', [AiAssistantController::class, 'forkSession']);
    Route::post('/ai/subtasks/accept', [AiAssistantController::class, 'acceptSubtasks'])->middleware('throttle:30,1');

    // Notes
    Route::get('/notes', [NoteController::class, 'index']);
    Route::post('/notes', [NoteController::class, 'store']);
    Route::put('/notes/{id}', [NoteController::class, 'update']);
    Route::delete('/notes/{id}', [NoteController::class, 'destroy']);
    Route::post('/notes/{id}/invite', [NoteController::class, 'invite']);
    Route::post('/notes/{id}/collaborators/{collaboratorId}/remove', [NoteController::class, 'removeCollaborator']);

    // Productivity
    Route::get('/productivity', [ProductivityController::class, 'index']);

    // My Tasks & Team
    Route::get('/my-tasks', [TeamController::class, 'myTasks']);
    Route::get('/team', [TeamController::class, 'index']);
    Route::post('/team/invite', [TeamController::class, 'invite']);
    Route::post('/team/invitations/{token}/accept', [TeamController::class, 'acceptInvitation']);
    Route::post('/team/invitations/{token}/decline', [TeamController::class, 'declineInvitation']);
    Route::post('/team/tasks/{taskId}/collaborators/{collaboratorId}/remove', [TeamController::class, 'removeCollaborator']);

    // Data view — subject-centered dashboard
    Route::get('/data', [SubjectController::class, 'data'])->name('subjects.data');

    // Tasks (web)
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::put('/tasks/{id}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::patch('/tasks/{id}/complete', [TaskController::class, 'complete'])->name('tasks.complete');
});