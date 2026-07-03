<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'manager_id' => 'required',
            'mpasswd' => 'required',
        ]);

        $admin = Admin::where('manager_id', $credentials['manager_id'])->first();

        // 1. Check Bcrypt (only if it looks like a hash to prevent runtime error)
        if ($admin && str_starts_with($admin->mpasswd, '$2y$') && \Illuminate\Support\Facades\Hash::check($credentials['mpasswd'], $admin->mpasswd)) {
            Auth::guard('admin')->login($admin);
            return redirect()->intended(route('admin.dashboard'));
        }

        // 2. Legacy Check (md5 or sha256(md5))
        if ($admin && (md5($credentials['mpasswd']) === $admin->mpasswd || hash('sha256', md5($credentials['mpasswd'])) === $admin->mpasswd)) {
            Auth::guard('admin')->login($admin);
            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'manager_id' => 'The provided credentials do not match our records.',
        ]);
    }

    public function logout()
    {
        Auth::guard('admin')->logout();
        return redirect()->route('admin.login');
    }
}
