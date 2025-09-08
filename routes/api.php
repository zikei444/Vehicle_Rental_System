<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RatingApiController;
use App\Http\Controllers\Api\VehicleApiController;
use App\Http\Controllers\Api\MaintenanceApiController;
use App\Http\Controllers\Api\ReservationApiController;

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
    Route::get('/ratings', [RatingApiController::class, 'index']);   // 获取approved评论
    Route::post('/ratings', [RatingApiController::class, 'store']);  // 提交评论+评分
    Route::get('/ratings/average', [RatingApiController::class, 'rating']); // 获取平均分
});


// Reservation Api
Route::get('/reservations', [ReservationApiController::class, 'index']);  // semua
Route::get('/reservations/{id}', [ReservationApiController::class, 'show']);  // satu id
Route::post('/reservations', [ReservationApiController::class, 'store']);   // tambah
Route::put('/reservations/{id}', [ReservationApiController::class, 'update']);   // update
Route::delete('/reservations/{id}', [ReservationApiController::class, 'destroy']);  // buang satu

// Maintenance API
Route::prefix('maintenances')->group(function () {
    Route::get('/',        [MaintenanceApiController::class, 'index']);   // List all maintenances
    Route::post('/',       [MaintenanceApiController::class, 'store']);   // Create maintenance
    Route::get('/{id}',    [MaintenanceApiController::class, 'show']);    // Get details
    Route::put('/{id}',    [MaintenanceApiController::class, 'update']);  // Edit & Update
    Route::delete('/{id}', [MaintenanceApiController::class, 'destroy']); // Delete
});