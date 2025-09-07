<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RatingApiController;
use App\Http\Controllers\Api\VehicleApiController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Route::prefix('ratings')->group(function () {
//     Route::post('/', [RatingApiController::class, 'store']);           // Submit rating
//     Route::put('/approve/{id}', [RatingApiController::class, 'approve']); // Admin approve
//     Route::get('/', [RatingApiController::class, 'index']);           // Get approved ratings
// });
//Route::middleware('auth:sanctum')->post('/ratings', [RatingController::class, 'store']);

// Vehicles API
Route::get('/vehicles', [VehicleApiController::class, 'index']);
Route::get('/vehicles/{id}', [VehicleApiController::class, 'show']);
Route::post('/vehicles/update-status', [VehicleApiController::class, 'updateStatus']);

// Ratings api

Route::prefix('vehicles/{vehicle}')->group(function () {
    Route::get('/ratings', [RatingApiController::class, 'index']);   // 获取评论列表
    Route::post('/ratings', [RatingApiController::class, 'store']);  // 提交评分 + 评论
    Route::get('/ratings/average', [RatingApiController::class, 'average']); // 获取平均分
});
