<?php
// routes/web.php
use App\Http\Controllers\Auth\LoginRedirectController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::inertia('/', 'Welcome')->name('home');

// LOGIN REDIRECT - This catches the default /dashboard route
Route::middleware(['auth'])
    ->get('/dashboard', [LoginRedirectController::class, 'redirect'])
    ->name('dashboard');

// Admin routes - ADDED NAME()
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')  // This is already correct
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard'); // This creates admin.dashboard
    });

// Operator routes - ADDED NAME()
Route::middleware(['auth', 'role:operator'])
    ->prefix('operator')
    ->name('operator.')  // This is already correct
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard'); // This creates operator.dashboard
    });

// User routes - ADDED NAME()
Route::middleware(['auth', 'role:user'])
    ->prefix('user')
    ->name('user.')  // This is already correct
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard'); // This creates user.dashboard
    });

require __DIR__ . '/settings.php';