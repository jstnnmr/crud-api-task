<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\TaskController;

Route::apiResource('users', UsersController::class);
Route::apiResource('tasks', TaskController::class);