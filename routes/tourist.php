<?php


use App\Http\Controllers\Tourist\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware('role:Tourist,Admin')->prefix('tourist')->name('tourist.')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
});