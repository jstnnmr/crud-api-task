<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\TaskController;


/* -- resource routes -- */ 
// Route::resource('users', UsersController::class);
// Route::resource('tasks', TaskController::class);
// routes/api.php
Route::resource('users', UsersController::class)
    ->except(['create', 'edit'])
    ->names([
        'index'   => 'api.users.index',
        'store'   => 'api.users.store',
        'show'    => 'api.users.show',
        'update'  => 'api.users.update',
        'destroy' => 'api.users.destroy',
    ]);

Route::resource('tasks', TaskController::class)
    ->except(['create', 'edit'])
    ->names([
        'index'   => 'api.tasks.index',
        'store'   => 'api.tasks.store',
        'show'    => 'api.tasks.show',
        'update'  => 'api.tasks.update',
        'destroy' => 'api.tasks.destroy',
    ]);

/* -- manual routing -- 
User Routes
Route::get('/users', [UsersController::class, 'index']);    
Route::post('/users', [UsersController::class, 'store']);
Route::get('/users/{id}', [UsersController::class, 'show']);
Route::patch('/users/{id}', [UsersController::class, 'update']);
Route::put('/users/{id}', [UsersController::class, 'update']);
Route::delete('/users/{id}', [UsersController::class, 'destroy']);

Task Routes
Route::get('/tasks', [TaskController::class, 'index']);
Route::post('/tasks', [TaskController::class, 'store']);
Route::get('/tasks/{id}', [TaskController::class, 'show']);
Route::put('/tasks/{id}', [TaskController::class, 'update']);
// Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);*/