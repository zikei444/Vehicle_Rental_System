<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\AdminVehicleController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\AdminReservationController;
use App\Http\Controllers\RatingController;

// =================== USER VEHICLE ROUTES ===================
// Show all vehicles
Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles.index');

// Show details of a single vehicle
Route::get('/vehicles/{id}', [VehicleController::class, 'show'])->name('vehicles.show');

// Select a vehicle and go to reservation process
Route::get('vehicles/select/{id}', [VehicleController::class, 'select'])->name('vehicles.select');

// Store a vehicle 
Route::post('/vehicles', [VehicleController::class, 'store'])->name('vehicles.store');

// =================== ADMIN VEHICLE ROUTES ===================
Route::prefix('admin')->group(function () {
    Route::get('/vehicles', [AdminVehicleController::class, 'index'])->name('admin.vehicles.index'); 
    Route::get('/vehicles/create', [AdminVehicleController::class, 'create'])->name('admin.vehicles.create');
    Route::post('/vehicles', [AdminVehicleController::class, 'store'])->name('admin.vehicles.store');
    Route::get('/vehicles/{id}', [AdminVehicleController::class, 'show'])->name('admin.vehicles.show'); 
    Route::get('/vehicles/{id}/edit', [AdminVehicleController::class, 'edit'])->name('admin.vehicles.edit');
    Route::put('/vehicles/{id}', [AdminVehicleController::class, 'update'])->name('admin.vehicles.update');
    Route::delete('/vehicles/{id}', [AdminVehicleController::class, 'destroy'])->name('admin.vehicles.destroy');
});

// Redirect to the reservation process page
Route::get('reservation/process', [ReservationController::class, 'process'])
     ->name('reservation.process');

// Calculate and display cost 
Route::post('/reservation/calculate-ajax', [ReservationController::class, 'calculateAjax'])
    ->name('reservation.calculate.ajax');

// To confirm and proceed to booking 
Route::match(['get', 'post'], '/reservation/confirm', [ReservationController::class, 'confirm'])
    ->name('reservation.confirm');

// Success Payment 
Route::post('/reservation/payment-process', [ReservationController::class, 'paymentProcess'])
    ->name('reservation.payment.process');

// To view current reservation (for customers)
Route::get('/my-reservations', [ReservationController::class, 'myReservations'])
    ->name('reservation.my');

// =================== ADMIN RESERVATION ROUTES ===================
Route::get('/admin/reservations', [AdminReservationController::class, 'reservations'])
    ->name('admin.reservations.index');

Route::patch('/admin/reservations/{id}/update-status', [AdminReservationController::class, 'updateStatus'])
    ->name('admin.reservations.updateStatus');

Route::delete('/admin/reservations/{id}', [AdminReservationController::class, 'destroy'])
    ->name('admin.reservations.destroy');

// =================== CUSTOMER FEEDBACK ROUTES ===================
Route::get('/feedback', [RatingController::class, 'index'])->name('ratings.index');
Route::post('/rental/{rental}/rate', [RatingController::class, 'store'])->name('ratings.store');

// =================== ADMIN FEEDBACK ROUTES ===================
// (No middleware for now – free access)
Route::get('/admin/feedback', [RatingController::class, 'manage'])->name('ratings.manage');
Route::post('/admin/feedback/{id}/update', [RatingController::class, 'updateStatus'])->name('ratings.updateStatus');
