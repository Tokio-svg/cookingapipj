<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\LineController;
// use App\Models\Recipe;

// JWT-auth用ルーティング
Route::group([
    'middleware' => ['auth:api'],
    'prefix' => 'auth'
], function ($router) {
    Route::post('register', [AuthController::class, 'register'])->withoutMiddleware(['auth:api']);
    Route::post('login', [AuthController::class, 'login'])->withoutMiddleware(['auth:api']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('user', [AuthController::class, 'me']);
});

// 投稿レシピ用ルーティング
Route::apiResource('/v1/recipe', RecipeController::class)->only([
    'store', 'show', 'destroy'
]);
Route::apiResource('/v1/search/{category}', SearchController::class)->only([
    'index'
]);

// Line bot用ルーティング
Route::post('/line/webhook', [LineController::class, 'webhook']);
Route::post('/line/test', [LineController::class, 'test']);
