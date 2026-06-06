<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Web UI for Authentication
Route::get('/register', [AuthController::class, 'showRegister']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// Dashboard / Data views
Route::get('/', function () {
    return view('welcome'); // Or your dashboard view
});

Route::get('/data', [App\Http\Controllers\UsersController::class, 'dataView']);