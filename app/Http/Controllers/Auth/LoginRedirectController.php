<?php
// app/Http/Controllers/Auth/LoginRedirectController.php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoginRedirectController extends Controller
{
    public function redirect()
    {
        $user = auth()->user();

        // Redirect based on role
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($user->role === 'operator') {
            return redirect()->route('operator.dashboard');
        }

        return redirect()->route('user.dashboard');
    }
}