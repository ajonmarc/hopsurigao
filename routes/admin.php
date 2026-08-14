<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;

use Illuminate\Support\Facades\Route;

Route::middleware('role:Admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('users', UserController::class);
    Route::delete('users-bulk-destroy', [UserController::class, 'bulkDestroy'])->name('users.bulk.destroy');

 
});
