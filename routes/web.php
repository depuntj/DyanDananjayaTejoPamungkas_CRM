<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Add a proper home route that redirects to dashboard
Route::get('/home', function () {
    return redirect()->route('dashboard');
})->name('home');
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Lead routes
    Route::resource('leads', LeadController::class);

    // Product routes - only accessible by admin and manager
    Route::middleware(['auth', 'role:admin|manager'])->group(function () {
        Route::resource('products', ProductController::class);

        // User management
        Route::resource('users', UserController::class);
    });

    // Project routes
    Route::resource('projects', ProjectController::class);

    // Project approval routes - only accessible by managers
    Route::middleware(['auth', 'role:manager'])->group(function () {
        Route::post('/projects/{project}/approve', [ProjectController::class, 'approve'])->name('projects.approve');
        Route::post('/projects/{project}/reject', [ProjectController::class, 'reject'])->name('projects.reject');
    });

    // Project conversion route
    Route::post('/projects/{project}/convert', [ProjectController::class, 'convert'])->name('projects.convert');

    // Customer routes
    Route::resource('customers', CustomerController::class)->except(['create', 'store', 'destroy']);
    Route::post('/customers/{customer}/services', [CustomerController::class, 'addService'])->name('customers.services.add');
    Route::put('/customers/{customer}/services/{service}', [CustomerController::class, 'updateService'])->name('customers.services.update');
    Route::delete('/customers/{customer}/services/{service}', [CustomerController::class, 'removeService'])->name('customers.services.remove');
});

require __DIR__.'/auth.php';
