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
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Models\Rating;

// Home route
Route::get('/', [HomeController::class, 'index'])->name('home');

// =================== USER VEHICLE ROUTES ===================
// Show all vehicles 
Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles.index');

// Show details of a single vehicle 
Route::get('/vehicles/{id}', [VehicleController::class, 'show'])->name('vehicles.show');

// Select a vehicle and go to reservation process
Route::get('/vehicles/select/{id}', [VehicleController::class, 'select'])->name('vehicles.select');

// Store a new vehicle 
Route::post('/vehicles', [VehicleController::class, 'store'])->name('vehicles.store');


// =================== ADMIN VEHICLE ROUTES ===================
// Show all vehicles 
Route::get('/admin/vehicles', [AdminVehicleController::class, 'index'])->name('admin.vehicles.index');

// Show form to create a new vehicle 
Route::get('/admin/vehicles/create', [AdminVehicleController::class, 'create'])->name('admin.vehicles.create');

// Save new vehicle into database 
Route::post('/admin/vehicles', [AdminVehicleController::class, 'store'])->name('admin.vehicles.store');

// Show details of a single vehicle 
Route::get('/admin/vehicles/{id}', [AdminVehicleController::class, 'show'])->name('admin.vehicles.show');

// Show edit form for an existing vehicle 
Route::get('/admin/vehicles/{id}/edit', [AdminVehicleController::class, 'edit'])->name('admin.vehicles.edit');

// Update an existing vehicle 
Route::put('/admin/vehicles/{id}', [AdminVehicleController::class, 'update'])->name('admin.vehicles.update');

// Delete a vehicle 
Route::delete('/admin/vehicles/{id}', [AdminVehicleController::class, 'destroy'])->name('admin.vehicles.destroy');




// =================== USERS RESERVATION ROUTES ===================
Route::middleware(['frameguard'])->group(function () {
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

    // To view reservation history (for customers)
    Route::get('/my-reservations/history', [ReservationController::class, 'reservationHistory'])
        ->name('reservations.history');

    // To mark as complete 
    Route::post('/reservations/{id}/complete', [ReservationController::class, 'complete'])
        ->name('reservations.complete');

    // To mark as cancel 
    Route::post('/reservations/{id}/cancel', [ReservationController::class, 'cancel'])
        ->name('reservations.cancel');
});

// =================== ADMIN RESERVATION ROUTES ===================
Route::middleware(['frameguard'])->group(function () {
    Route::get('/admin/reservations', [AdminReservationController::class, 'reservations'])
        ->name('admin.reservations.index');

    Route::patch('/admin/reservations/{id}/update-status', [AdminReservationController::class, 'updateStatus'])
        ->name('admin.reservations.updateStatus');

    Route::delete('/admin/reservations/{id}', [AdminReservationController::class, 'destroy'])
        ->name('admin.reservations.destroy');
});



// // =================== CUSTOMER FEEDBACK ROUTES ===================
// notification observer
//view all 
Route::get('/notifications/fetch', [NotificationController::class, 'fetch'])->name('notifications.fetch');
//mark as read
Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');

Route::prefix('vehicles/{vehicle}')->group(function () {
    // show all rating
    Route::get('/ratings', [VehicleReviewController::class, 'showRatings']);
    // show average
    Route::get('/ratings/average', [VehicleReviewController::class, 'showAverage']);
});
Route::get('/vehicles/{vehicle}/rating-list', [VehicleReviewController::class, 'showRatings']);

// show each reservation rating
Route::get('/ratings/view/{reservation}', [VehicleReviewController::class, 'viewRating'])    ->name('ratings.viewRating');

Route::post('/ratings', [VehicleReviewController::class, 'store'])->name('ratings.store');

//show rating
Route::get('/reservations/{reservation}/ratings', [VehicleReviewController::class, 'showReservationRating'])    ->name('reservation.ratings.show');

// To create ratings
Route::get('/ratings/create/{vehicle}/review-form', [VehicleReviewController::class, 'create'])    ->name('rating.create');

// // =================== ADMIN MANAGE FEEDBACK ===================
//Apply Throttle to Admin Routes！！！！
Route::prefix('admin')->middleware(['auth', 'admin', 'throttle:10,1'])->group(function () {
    // Admin Dashboard 
    Route::get('/ratings/dashboard', [AdminRatingController::class, 'dashboard'])->name('ratings_admin.dashboard');
    Route::get('/ratings/dashboard/details/{vehicle}', [AdminRatingController::class, 'vehicleRatingsDetails'])->name('ratings_admin.details');
    // Manage ratings
    Route::get('/ratings/manage', [AdminRatingController::class, 'index'])->name('ratings_admin.index'); // Manage Ratings
    Route::post('/ratings/{rating}/approve', [AdminRatingController::class, 'approve'])->name('ratings_admin.approve');
    Route::post('/ratings/{rating}/reject', [AdminRatingController::class, 'reject'])->name('ratings_admin.reject');
    // admin reply
    Route::post('/ratings/{rating}/reply', [AdminRatingController::class, 'reply'])->name('ratings_admin.reply');
    Route::delete('/ratings/{id}', [AdminRatingController::class, 'destroy'])->name('ratings.destroy');

});
// get new reply 
Route::get('/ratings/{id}/reply', function ($id) {
    $rating = Rating::findOrFail($id);
    return response()->json([
        'reply' => $rating->adminreply,
    ]);
})->middleware('auth');

// get count unread notification
Route::get('/notifications/count', function () {
    $user = Auth::user();
    return response()->json([
        'count' => $user->unreadNotifications->count(),
    ]);
})->middleware('auth');

Route::get('/notifications/unread', function () {
    $user = auth()->user();

    // Only customers
    if (!$user->customer) return response()->json([]);

    $notifications = $user->unreadNotifications->map(function ($n) {
        return [
            'id' => $n->id,
            'message' => $n->data['message'] ?? 'New notification',
            'url' => $n->data['url'] ?? '#'
        ];
    });

    // Optional: mark as read automatically
    $user->unreadNotifications->markAsRead();

    return response()->json($notifications);
})->middleware('auth')->name('notifications.unread');

Route::get('/notifications/count', function () {
    $user = Auth::user();
    return response()->json([
        'count' => $user->unreadNotifications->count(),
    ]);
})->middleware('auth');

// unread to read
Route::get('/admin/notifications/unread', function () {
    $user = auth()->user();

    $notifications = $user->unreadNotifications()->get()->map(function ($n) {
        return [
            'id' => $n->id,
            'message' => $n->data['message'],
            'url' => $n->data['url'] ?? '#'
        ];
    });

    $user->unreadNotifications->markAsRead();

    return response()->json($notifications);
})->middleware('auth')->name('admin.notifications.unread');

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
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');

// Submit login form
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');

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
Route::middleware(['auth', 'admin'])->group(function () {
    // Redirect to Customer Account Management Page
    Route::get('/admin/customers', [CustomerController::class, 'index'])
        ->name('admin.customerManagement');

    // Display customer account list
    Route::get('/admin/profile', [CustomerController::class, 'index'])
        ->name('admin.customerManagement');

    // Update customer account
    Route::put('/admin/customers/{id}', [CustomerController::class, 'update'])
        ->name('admin.customer.update');
});


// ========================= UPDATE PROFILE (CUSTOMER) ======================
// Edit profile
Route::middleware('auth')->group(function () {

    // Show profile edit form
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    // Update profile (Save button)
    Route::put('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    // Delete account
    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

});

