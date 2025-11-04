<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UsersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users', [UsersController::class, 'index']);
    Route::delete('/users/{id}', [UsersController::class, 'delete']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/files', [FileUploadController::class, 'index']);
    Route::post('/upload', [FileUploadController::class, 'store']);
    Route::delete('/files/{id}', [FileUploadController::class, 'destroy']);

    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::group(['prefix' => 'admin'], function () {
            Route::post('/register', [AuthController::class, 'register']);
            Route::post('/groups', [AdminController::class, 'createGroup']);
            Route::get('/groups', [AdminController::class, 'index']);
            Route::post('/assign', [AdminController::class, 'assignUserToGroup']);
        });

        Route::put('/users/{user}/limit', [UsersController::class, 'updateLimit']);
        //Route::put('/groups/{group}/limit', [GroupController::class, 'updateLimit']);

        Route::group(['prefix' => 'settings'], function () {
            Route::get('/', [SettingsController::class, 'settings']);
            Route::post('/', [SettingsController::class, 'update']);
        });


    });
});
