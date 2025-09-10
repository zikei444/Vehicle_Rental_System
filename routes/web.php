<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\AdminVehicleController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\AdminReservationController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\VehicleReviewController;
use App\Http\Controllers\AdminRatingController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProfileController;

 
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

// =================== USERS RESERVATION ROUTES ===================
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

// To view reservation his (for customers)
Route::get('/my-reservations/history', [ReservationController::class, 'reservationHistory'])
    ->name('reservations.history');

// =================== ADMIN RESERVATION ROUTES ===================
Route::get('/admin/reservations', [AdminReservationController::class, 'reservations'])
    ->name('admin.reservations.index');

Route::patch('/admin/reservations/{id}/update-status', [AdminReservationController::class, 'updateStatus'])
    ->name('admin.reservations.updateStatus');

Route::delete('/admin/reservations/{id}', [AdminReservationController::class, 'destroy'])
    ->name('admin.reservations.destroy');



// // =================== CUSTOMER FEEDBACK ROUTES ===================

// 车辆评分页面
Route::prefix('vehicles/{vehicle}')->group(function () {
    // 显示评分列表
    Route::get('/ratings', [VehicleReviewController::class, 'showRatings']);

    // 显示平均评分
    Route::get('/ratings/average', [VehicleReviewController::class, 'showAverage']);
});
Route::get('/vehicles/{vehicle}/rating-list', [VehicleReviewController::class, 'showRatings']);

// 显示单条 reservation 的评分详情
Route::get('/ratings/view/{reservation}', [VehicleReviewController::class, 'viewRating'])
    ->name('ratings.viewRating');

Route::post('/ratings', [VehicleReviewController::class, 'store'])->name('ratings.store');

//看rating

Route::get('/reservations/{reservation}/ratings', [VehicleReviewController::class, 'showReservationRating'])
    ->name('reservation.ratings.show');

// To create ratings... ADDED BY ZK !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
Route::get('/ratings/create/{vehicle}/review-form', [VehicleReviewController::class, 'create'])
    ->name('rating.create');

// // =================== ADMIN MANAGE FEEDBACK ===================

Route::prefix('admin')->group(function () {
    Route::get('/ratings', [AdminRatingController::class, 'index'])->name('ratings_admin.index');
    Route::post('/ratings/{rating}/approve', [AdminRatingController::class, 'approve'])->name('ratings_admin.approve');
    Route::post('/ratings/{rating}/reject', [AdminRatingController::class, 'reject'])->name('ratings_admin.reject');
    Route::post('/ratings/{rating}/reply', [AdminRatingController::class, 'reply'])->name('ratings_admin.reply');

});

// // =================== ADMIN MANAGE MAINTENANCE ===================

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

// ================== REGISTRATION =========================
// Redirect to registration page
Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register');

// Submit registration form
Route::post('/register', [RegisterController::class, 'register'])->name('register.submit');

// ================== LOGIN =============================
// Redirect to login page
Route::get('/login', [App\Http\Controllers\LoginController::class, 'showLoginForm'])->name('login');

// Submit login form
Route::post('/login', [App\Http\Controllers\LoginController::class, 'login'])->name('login.submit');

// Logout
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');


//=================== DASHBOARD =============================
Route::get('/customer/dashboard', function () {
    return view('dashboards.customerSide');
})->name('customer.dashboard')->middleware('auth');


Route::get('/admin/dashboard', function () {
    return view('dashboards.adminSide');
})->name('admin.dashboard')->middleware('auth');


// ============================== ADMIN ONLY =================================
// Admin dashboard
    // Redirect to Customer Account Management Page
    Route::get('/admin/customers', [CustomerController::class, 'index'])->name('admin.customerManagement');

    // Display customer account list
    Route::get('/admin/profile', [CustomerController::class, 'index'])->name('admin.customerManagement');

    // Update customer account
    Route::put('/admin/customers/{id}', [CustomerController::class, 'update'])->name('admin.customer.update');


;

// ========================= UPDATE PROFILE (CUSTOMER) ======================
// Edit profile
Route::get('/profile', [ProfileController::class, 'edit'])
    ->name('profile.edit')
    ->middleware('auth');

// Update (Save button) profile
Route::put('/profile', [ProfileController::class, 'update'])
    ->name('profile.update')
    ->middleware('auth');

