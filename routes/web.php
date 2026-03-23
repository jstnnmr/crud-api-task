<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UsersController;
use App\Http\Controllers\TaskController;

Route::resource('/users', UsersController::class)->except(['create', 'edit']);
Route::resource('/tasks', TaskController::class)->except(['create', 'edit']); 


Route::get('/', function () {
    return response()->json([
        'message' => 'Welcome to the CRUD API Task',
        'endpoints' => [
            'GET /users' => 'List all users',
            'POST /users' => 'Create a new user',
            'GET /users/{id}' => 'Get a specific user',
            'PUT /users/{id}' => 'Update a specific user',  
            'DELETE /users/{id}' => 'Delete a specific user',
            'GET /tasks' => 'List all tasks',
            'POST /tasks' => 'Create a new task',
            'GET /tasks/{id}' => 'Get a specific task', 
            'PUT /tasks/{id}' => 'Update a specific task',
            'DELETE /tasks/{id}' => 'Delete a specific task',
        ]
    ]);        
});