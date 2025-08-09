<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ColumnController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API маршруты для задач и колонок
Route::middleware('auth')->group(function () {
    Route::prefix('spaces/{space}')->group(function () {
        // Маршруты для задач
        Route::get('/tasks', [TaskController::class, 'index']);
        Route::post('/tasks', [TaskController::class, 'store']);
        Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus']);
        Route::put('/tasks/{task}', [TaskController::class, 'update']);
        Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);
        Route::patch('/tasks/positions', [TaskController::class, 'updatePositions']);
        
        // Маршруты для колонок
        Route::get('/columns', [ColumnController::class, 'index']);
        Route::get('/columns/hidden', [ColumnController::class, 'hiddenColumns']);
        Route::post('/columns', [ColumnController::class, 'store']);
        Route::put('/columns/{column}', [ColumnController::class, 'update']);
        Route::delete('/columns/{column}', [ColumnController::class, 'destroy']);
        Route::patch('/columns/positions', [ColumnController::class, 'updatePositions']);
        Route::post('/columns/{columnId}/restore', [ColumnController::class, 'restore']);
    });
    
    // Отдельные маршруты для колонок (без привязки к пространству)
    Route::post('/columns/{column}/move', [ColumnController::class, 'move']);
    Route::put('/columns/{column}', [ColumnController::class, 'updateSimple']);
    
    // Отдельные маршруты для задач (без привязки к пространству)
    Route::put('/tasks/{task}', [TaskController::class, 'updateApi']);
    Route::post('/tasks/{task}/content', [TaskController::class, 'updateContent']);
    Route::post('/tasks/{task}/upload', [TaskController::class, 'uploadFile']);
});
