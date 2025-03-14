<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Unauthenticated Routes
Route::get('/', function () {
    return redirect()->route('login');
})->name('root');

Route::get('/home', function () {
    return redirect()->route('dashboard');
})->name('home');

// Authenticated Routes with Global Auth Middleware
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Leads Routes
    Route::prefix('leads')->name('leads.')->group(function () {
        Route::get('/', [LeadController::class, 'index'])->name('index');
        Route::get('/create', [LeadController::class, 'create'])->name('create');
        Route::post('/', [LeadController::class, 'store'])->name('store');
        Route::get('/{lead}', [LeadController::class, 'show'])->name('show');
        Route::get('/{lead}/edit', [LeadController::class, 'edit'])->name('edit');
        Route::put('/{lead}', [LeadController::class, 'update'])->name('update');
        Route::delete('/{lead}', [LeadController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/create', [ProductController::class, 'create'])->name('create');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::get('/{product}', [ProductController::class, 'show'])->name('show');
        Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
        Route::put('/{product}', [ProductController::class, 'update'])->name('update');
        Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
    });
    Route::get('/api/products', function() {
    return App\Models\Product::where('is_active', true)->get();
})->middleware('auth')->name('api.products');

    Route::middleware(['role:admin'])->group(function () {
        Route::resource('users', UserController::class);
        Route::put('users/{user}/password', [UserController::class, 'updatePassword'])->name('users.password.update');
    });
    // Projects Routes
    Route::prefix('projects')->name('projects.')->group(function () {
        Route::get('/', [ProjectController::class, 'index'])->name('index');
        Route::get('/create', [ProjectController::class, 'create'])->name('create');
        Route::post('/', [ProjectController::class, 'store'])->name('store');
        Route::get('/{project}', [ProjectController::class, 'show'])->name('show');
        Route::get('/{project}/edit', [ProjectController::class, 'edit'])->name('edit');
        Route::put('/{project}', [ProjectController::class, 'update'])->name('update');
        Route::delete('/{project}', [ProjectController::class, 'destroy'])->name('destroy');

        // Manager-only Project Actions
        Route::middleware(['role:manager'])->group(function () {
            Route::post('/{project}/approve', [ProjectController::class, 'approve'])
                ->name('approve');
            Route::post('/{project}/reject', [ProjectController::class, 'reject'])
                ->name('reject');
        });
        Route::middleware(['role:admin'])->group(function () {
            Route::post('/{project}/approve', [ProjectController::class, 'approve'])
                ->name('approve');
            Route::post('/{project}/reject', [ProjectController::class, 'reject'])
                ->name('reject');
        });

        // Project Conversion (accessible to authenticated users)
        Route::post('/{project}/convert', [ProjectController::class, 'convert'])
            ->name('convert');
    });

    // Customers Routes
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('index');
        Route::get('/{customer}', [CustomerController::class, 'show'])->name('show');
        Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->name('edit');
        Route::put('/{customer}', [CustomerController::class, 'update'])->name('update');

        // Customer Services Routes - Fixed routes
        Route::post('/{customer}/services', [CustomerController::class, 'addService'])
            ->name('services.add');
        Route::put('/{customer}/services/{service}', [CustomerController::class, 'updateService'])
            ->name('services.update');
        Route::delete('/{customer}/services/{service}', [CustomerController::class, 'removeService'])
            ->name('services.remove');

        Route::get('/api/products', function() {
            return App\Models\Product::where('is_active', true)->get();
        })->middleware('auth')->name('api.products');
    });
});

// Authentication Routes
require __DIR__.'/auth.php';
