<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BoardUserController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\ColumnController;
use App\Http\Controllers\LabelController;
use App\Http\Controllers\TaskController;

// Rotas de autenticação (públicas)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rotas protegidas
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    
    // Rotas de usuários
    Route::apiResource('users', UserController::class);
    
    // Rotas de quadros
    Route::apiResource('boards', BoardController::class);
    Route::post('boards/{board}/connect', [BoardController::class, 'connect']);
    
    // labels de um board específico
    Route::apiResource('boards.labels', LabelController::class)->only(['index']);

    // Rotas de labels
    Route::apiResource('labels', LabelController::class);

    // Rotas de Tasks
    Route::apiResource('tasks', TaskController::class)->except(['edit']);

    // Rotas de Colunas
    Route::apiResource('boards.columns', ColumnController::class)->except(['edit']);
    
    // Rotas de Membros do Quadro
    Route::apiResource('board-users', BoardUserController::class)->except(['edit']);
});
