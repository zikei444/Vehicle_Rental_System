<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\ReservationController;

// Vehical Routes: 
// Vehicle selection page
Route::get('vehicles', [VehicleController::class, 'index']);

// Select a vehicle and go to reservation process
Route::get('vehicles/select/{id}', [VehicleController::class, 'select']);

// Reservation Routes: 
// Redirect to the reservation process page
Route::get('reservation/process', [ReservationController::class, 'process'])
     ->name('reservation.process');

// Calculate and back to same page
Route::post('reservation/calculate', [ReservationController::class, 'calculate'])
->name('reservation.calculate');


     