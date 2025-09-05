<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\AdminReservationController;
use App\Http\Controllers\RatingController;

// Vehical Routes: 
// Vehicle selection page
Route::get('vehicles', [VehicleController::class, 'index']);

// Select a vehicle and go to reservation process
Route::get('vehicles/select/{id}', [VehicleController::class, 'select']);

// Reservation Routes: 
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

// To view current reservation
Route::get('/my-reservations', [ReservationController::class, 'myReservations'])->name('reservation.my');

// Show all reservations
Route::get('/admin/reservations', [AdminReservationController::class, 'reservations'])
    ->name('admin.reservations.index');

// Update reservation status via AJAX
Route::patch('/admin/reservations/{id}/update-status', [AdminReservationController::class, 'updateStatus'])
    ->name('admin.reservations.updateStatus');

// Delete reservation
Route::delete('/admin/reservations/{id}', [AdminReservationController::class, 'destroy'])
    ->name('admin.reservations.destroy');

// =================== CUSTOMER ROUTES ===================
Route::get('/feedback', [RatingController::class, 'index'])->name('ratings.index');
Route::post('/rental/{rental}/rate', [RatingController::class, 'store'])->name('ratings.store');


// =================== ADMIN ROUTES ===================
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/feedback', [RatingController::class, 'manage'])->name('ratings.manage');
    Route::post('/admin/feedback/{id}/update', [RatingController::class, 'updateStatus'])->name('ratings.updateStatus');
});
     