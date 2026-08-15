<?php


use App\Http\Controllers\Tourist\DashboardController;
use App\Http\Controllers\Tourist\PackageController;
use App\Http\Controllers\Tourist\BookingController;
use App\Http\Controllers\Tourist\PaymentController;
use Illuminate\Support\Facades\Route;

Route::middleware('role:Tourist,Admin')->prefix('tourist')->name('tourist.')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');



    Route::get('packages', [PackageController::class, 'index'])->name('packages.index');
    Route::get('packages/{package}', [PackageController::class, 'show'])->name('packages.show');

    Route::get('bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('bookings/create', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::delete('bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy');
    Route::put('bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');


  // Payment routes
        Route::get('payments/create', [PaymentController::class, 'create'])->name('payments.create');
        Route::post('payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');


      
    Route::get('notifications/bookings', [\App\Http\Controllers\Tourist\NotificationController::class, 'bookings'])
        ->name('notifications.bookings');
});
