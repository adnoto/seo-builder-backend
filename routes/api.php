<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\PageController;
use Illuminate\Support\Facades\Route;

// Route model bindings - must be outside middleware groups
Route::bind('project', function ($value) {
    return \App\Models\Project::findOrFail($value);
});

Route::bind('page', function ($value) {
    return \App\Models\Page::findOrFail($value);
});

// Public auth routes
Route::post('register', [AuthController::class, 'register'])->name('register');
Route::post('login', [AuthController::class, 'login'])->name('login');

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('user', [AuthController::class, 'user'])->name('user');
    Route::apiResource('projects', ProjectController::class);
    Route::apiResource('projects.pages', PageController::class);
    Route::post('ai/generate', [PageController::class, 'generateAi'])->name('ai.generate');
});