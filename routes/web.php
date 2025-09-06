<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\AdminReservationController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\ReservationHistoryController;
use App\Http\Controllers\MaintenanceController;

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

// // =================== CUSTOMER ROUTES ===================
// Route::get('/feedback', [RatingController::class, 'index'])->name('ratings.index');
// Route::post('/rental/{rental}/rate', [RatingController::class, 'store'])->name('ratings.store');


// // =================== ADMIN ROUTES ===================
// Route::middleware(['auth', 'admin'])->group(function () {
//     Route::get('/admin/feedback', [RatingController::class, 'manage'])->name('ratings.manage');
//     Route::post('/admin/feedback/{id}/update', [RatingController::class, 'updateStatus'])->name('ratings.updateStatus');
// });
Route::get('/rental/{rental}/rate', [RatingController::class, 'create'])->name('ratings.create');
// Show feedback/rating form
Route::get('/ratings/create/{reservation}', [RatingController::class, 'create'])->name('ratings.create');
// Submit the feedback/rating form
Route::post('/ratings/store/{reservation}', [RatingController::class, 'store'])->name('ratings.store');
Route::get('/reservations/history', [ReservationHistoryController::class, 'index'])
     //->middleware('auth')
     ->name('reservations.history');  
     
// Maintenance Route:
Route::prefix('admin')->group(function () {
    // Show all maintenance records
    Route::get('/maintenance', [MaintenanceController::class, 'index'])
        ->name('maintenance.index');

    // Create a new schedule maintenance record
    Route::get('/maintenance/create', [MaintenanceController::class, 'create'])
        ->name('maintenance.create');

    // Save a new maintenance record and set vehicle to "Under Maintenance"
    Route::post('/maintenance', [MaintenanceController::class, 'store'])
        ->name('maintenance.store');

    // Edit an existing maintenance record
    // {maintenance} uses route-model binding to App\Models\Maintenance
    Route::get('/maintenance/{maintenance}/edit', [MaintenanceController::class, 'edit'])
        ->name('maintenance.edit');

    // Update record; if status is Completed/Cancelled, switch vehicle back to "Available"
    Route::put('/maintenance/{maintenance}', [MaintenanceController::class, 'update'])
        ->name('maintenance.update');

    // Delete the selected record (controller handles any necessary vehicle state cleanup)
    Route::delete('/maintenance/{maintenance}', [MaintenanceController::class, 'destroy'])
        ->name('maintenance.destroy');
});