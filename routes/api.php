<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RatingApiController;
use App\Http\Controllers\Api\VehicleApiController;
use App\Http\Controllers\Api\MaintenanceApiController;
use App\Http\Controllers\Api\ReservationApiController;
use App\Http\Controllers\Api\UserApiController;

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

// Vehicles API
Route::get('/vehicles', [VehicleApiController::class, 'index']); // Show all vehicle 
Route::get('/vehicles/{id}', [VehicleApiController::class, 'show']); // Show specific vehicle 
Route::post('/vehicles', [VehicleApiController::class, 'store']); // Create
Route::put('/vehicles/{id}', [VehicleApiController::class, 'update']); // Update 
Route::patch('/vehicles/{id}/update-status', [VehicleApiController::class, 'updateStatus']); // Update Status 
Route::delete('/vehicles/{id}', [VehicleApiController::class, 'destroy']); // Delete vehicle

// Ratings api
Route::post('/ratings', [RatingApiController::class, 'store']); // submit rating

Route::prefix('vehicles/{vehicle}')->group(function () {
    Route::get('/ratings', [RatingApiController::class, 'index']);   // get approved rating
    Route::get('/ratings/average', [RatingApiController::class, 'rating']); // get average rating
});

Route::delete('/ratings/{id}', [RatingApiController::class, 'destroy']);//new delete
Route::put('/ratings/{id}', [RatingApiController::class, 'update']);// Update an existing rating

Route::prefix('ratings')->group(function () {
    Route::get('/summary/{vehicleId}', [RatingApiController::class, 'summary']);
    Route::get('/{vehicleId}', [RatingApiController::class, 'index']);
});

// Reservation Api
Route::get('/reservations', [ReservationApiController::class, 'index']);  // semua
Route::get('/reservations/{id}', [ReservationApiController::class, 'show']);  // satu id
Route::post('/reservations', [ReservationApiController::class, 'store']);   // tambah
Route::put('/reservations/{id}', [ReservationApiController::class, 'update']);   // update
Route::delete('/reservations/{id}', [ReservationApiController::class, 'destroy']);  // buang satu
Route::get('/customers/{customerId}/reservations', [ReservationApiController::class, 'byCustomer']);  // based on cust id


// Maintenance API
Route::get('/maintenances', [MaintenanceApiController::class, 'index']);
Route::get('/maintenances/{id}', [MaintenanceApiController::class, 'show']);
Route::post('/maintenances', [MaintenanceApiController::class, 'store']);
Route::put('/maintenances/{id}', [MaintenanceApiController::class, 'update']);
Route::get('/vehicles/{vehicleId}/maintenances', [MaintenanceApiController::class, 'byVehicle']);

// User API
// List all customers
Route::get('/customers', [UserApiController::class, 'index']);

// Get single customer by ID
Route::get('/customers/{id}', [UserApiController::class, 'show']);

// Create a new customer
Route::post('/customers', [UserApiController::class, 'store']);

// Update customer info
Route::put('/customers/{id}', [UserApiController::class, 'update']);

// Delete customer
Route::delete('/customers/{id}', [UserApiController::class, 'destroy']);

// Get customer id
Route::get('/user/customer-id', [UserApiController::class, 'getCustomerId']);

