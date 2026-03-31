<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TasksController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/api/tasks',[TasksController::class,'store']);

Route::get('/api/tasks', [TasksController::class,'index']);

Route::patch('/api/tasks/{id}/status', [TasksController::class,'updateStatus']);

Route::delete('/tasks/{id}', [TasksController::class,'destroy']);

Route::get('/tasks/report', [TasksController::class, 'report']);