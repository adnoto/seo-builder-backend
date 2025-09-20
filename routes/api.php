<?php

use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\PageController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('projects', ProjectController::class);
    Route::apiResource('projects.pages', PageController::class);
    Route::post('ai/generate', [PageController::class, 'generateAi'])->name('ai.generate');
});