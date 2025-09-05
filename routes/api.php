<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RatingApiController;

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

Route::prefix('ratings')->group(function () {
    Route::post('/', [RatingApiController::class, 'store']);           // Submit rating
    Route::put('/approve/{id}', [RatingApiController::class, 'approve']); // Admin approve
    Route::get('/', [RatingApiController::class, 'index']);           // Get approved ratings
});
Route::middleware('auth:sanctum')->post('/ratings', [RatingController::class, 'store']);
