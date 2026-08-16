<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\InclusionController;
use App\Http\Controllers\Admin\TourDateController;
use App\Http\Controllers\Admin\TimeController;
use App\Http\Controllers\Admin\ReminderController;
use App\Http\Controllers\Admin\PickupLocationController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\BookingScanController;
use App\Http\Controllers\Admin\PickupScheduleController;


use Illuminate\Support\Facades\Route;

Route::middleware('role:Admin,Operator')->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');


    Route::resource('users', UserController::class);
    Route::delete('users-bulk-destroy', [UserController::class, 'bulkDestroy'])->name('users.bulk.destroy');

    Route::resource('packages', PackageController::class);
    Route::delete('packages-bulk-destroy', [PackageController::class, 'bulkDestroy'])->name('packages.bulk.destroy');

    Route::resource('inclusions', InclusionController::class);
    Route::delete('inclusions-bulk-destroy', [InclusionController::class, 'bulkDestroy'])->name('inclusions.bulk.destroy');

    Route::resource('tour-dates', TourDateController::class);
    Route::delete('tour-dates-bulk-destroy', [TourDateController::class, 'bulkDestroy'])->name('tour-dates.bulk.destroy');

    Route::resource('times', TimeController::class);
    Route::delete('times-bulk-destroy', [TimeController::class, 'bulkDestroy'])->name('times.bulk.destroy');

    Route::resource('reminders', ReminderController::class);
    Route::delete('reminders-bulk-destroy', [ReminderController::class, 'bulkDestroy'])->name('reminders.bulk.destroy');

    Route::resource('pickup-locations', PickupLocationController::class);
    Route::delete('pickup-locations-bulk-destroy', [PickupLocationController::class, 'bulkDestroy'])->name('pickup-locations.bulk.destroy');


    Route::resource('bookings', BookingController::class);
    Route::delete('bookings-bulk-destroy', [BookingController::class, 'bulkDestroy'])->name('bookings.bulk.destroy');


    Route::put('bookings/{booking}/confirm-payment', [BookingController::class, 'confirmPayment'])
        ->name('bookings.confirm-payment');


    Route::put('bookings/{booking}/status', [BookingController::class, 'updateStatus'])
        ->name('bookings.update-status');

    Route::get('notifications/bookings', [NotificationController::class, 'bookings'])
        ->name('notifications.bookings');


    Route::get('bookings-scan', [BookingScanController::class, 'index'])->name('bookings-scan.index');
    Route::post('bookings-scan/verify', [BookingScanController::class, 'verify'])->name('bookings-scan.verify');

    Route::resource('pickup-schedules', PickupScheduleController::class);
    Route::delete('pickup-schedules-bulk-destroy', [PickupScheduleController::class, 'bulkDestroy'])
        ->name('pickup-schedules.bulk.destroy');
});
