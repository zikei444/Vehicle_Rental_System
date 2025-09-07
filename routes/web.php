<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\AdminVehicleController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\AdminReservationController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\ReservationHistoryController;
use App\Http\Controllers\MaintenanceController;

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
// // =================== CUSTOMER ROUTES ===================
// Route::get('/feedback', [RatingController::class, 'index'])->name('ratings.index');
// Route::post('/rental/{rental}/rate', [RatingController::class, 'store'])->name('ratings.store');


// // =================== ADMIN ROUTES ===================
// Route::middleware(['auth', 'admin'])->group(function () {
//     Route::get('/admin/feedback', [RatingController::class, 'manage'])->name('ratings.manage');
//     Route::post('/admin/feedback/{id}/update', [RatingController::class, 'updateStatus'])->name('ratings.updateStatus');
// });
//Route::get('/rental/{rental}/rate', [RatingController::class, 'create'])->name('ratings.create');
Route::get('/ratings/{reservation}/create', [RatingController::class, 'create'])->name('ratings.create');
Route::post('/ratings', [RatingController::class, 'store'])->name('ratings.store');
Route::get('/history', [ReservationHistoryController::class, 'index'])->name('reservation.history');


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
