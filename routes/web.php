<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController; 
use App\Http\Controllers\Api\ProductController; 
use App\Http\Controllers\Api\OrderController; 
use App\Http\Controllers\Api\RentalController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/


Route::get('/', function () {
    return view('welcome');
})->name('home');

// Dashboard 
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/rentals', [RentalController::class, 'index'])->name('rentals.index');
    
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::middleware(['auth', 'admin'])
     ->prefix('admin')
     ->name('admin.')
     ->group(function () {
    
    // Admin Dashboard
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])
         ->name('dashboard');
    
    // Users Management
    Route::resource('users', App\Http\Controllers\Admin\UserController::class);
    Route::get('users/sellers', [App\Http\Controllers\Admin\UserController::class, 'sellers'])
         ->name('users.sellers');
    Route::get('users/customers', [App\Http\Controllers\Admin\UserController::class, 'customers'])
         ->name('users.customers');
    
    // Products Management
    Route::resource('products', App\Http\Controllers\Admin\ProductController::class);
    Route::resource('categories', App\Http\Controllers\Admin\CategoryController::class);
    
    // Orders Management
    Route::resource('orders', App\Http\Controllers\Admin\OrderController::class);
    Route::resource('rentals', App\Http\Controllers\Admin\RentalController::class);
    
    // Payments
    Route::get('payments', [App\Http\Controllers\Admin\PaymentController::class, 'index'])
         ->name('payments.index');
    Route::get('payments/{payment}', [App\Http\Controllers\Admin\PaymentController::class, 'show'])
         ->name('payments.show');
    
    // Reports
    Route::get('reports', [App\Http\Controllers\Admin\ReportController::class, 'index'])
         ->name('reports.index');
    Route::get('reports/export', [App\Http\Controllers\Admin\ReportController::class, 'export'])
         ->name('reports.export');
    
    // Logs
    Route::get('logs', [App\Http\Controllers\Admin\LogController::class, 'index'])
         ->name('logs.index');
    Route::get('logs/{log}', [App\Http\Controllers\Admin\LogController::class, 'show'])
         ->name('logs.show');
});

require __DIR__.'/auth.php';