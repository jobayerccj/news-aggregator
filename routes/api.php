<?php

use App\Http\Controllers\Api\V1\ArticleController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\UserPreferenceController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->namespace('App\Http\Controllers\Api\V1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::apiResource('articles', ArticleController::class)->only(['index', 'show']);

        Route::get('/user/preferences', [UserPreferenceController::class, 'index']);
        Route::post('/user/preferences', [UserPreferenceController::class, 'update']);
        Route::get('/user/articles', [UserPreferenceController::class, 'getUsersPreferredArticles']);
    });
});
