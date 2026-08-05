<?php
// app/Http/Controllers/DashboardController.php
namespace App\Http\Controllers;

use Inertia\Inertia;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();

        // Check user role and render appropriate dashboard
        if ($user->role === 'admin') {
            return Inertia::render('admin/Dashboard');
        }

        if ($user->role === 'operator') {
            return Inertia::render('operator/Dashboard');
        }

        // Default for regular users
        return Inertia::render('user/Dashboard');
    }
}