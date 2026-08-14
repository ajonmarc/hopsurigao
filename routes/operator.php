<?php

use App\Http\Controllers\Operator\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware('role:Operator,Admin')->prefix('operator')->name('operator.')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
});