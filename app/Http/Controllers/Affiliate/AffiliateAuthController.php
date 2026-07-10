<?php

namespace App\Http\Controllers\Affiliate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AffiliateAuthController extends Controller
{
    /**
     * Show the application's login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        // If already logged in, redirect to dashboard
        if (Auth::guard('admin')->check()) {
            return redirect()->route('affiliate.dashboard');
        }

        return view('affiliate.auth.login');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'manager_id' => 'required',
            'password' => 'required',
        ]);

        $admin = \App\Models\Admin::where('manager_id', $credentials['manager_id'])->first();

        // 1. Check Bcrypt (only if it looks like a hash to prevent runtime error)
        if ($admin && str_starts_with($admin->mpasswd, '$2y$') && \Illuminate\Support\Facades\Hash::check($credentials['password'], $admin->mpasswd)) {
            Auth::guard('admin')->login($admin, $request->filled('remember'));
            $request->session()->regenerate();
            return redirect()->intended(route('affiliate.dashboard'));
        }

        // 2. Legacy Check (md5 or sha256(md5))
        if ($admin && (md5($credentials['password']) === $admin->mpasswd || hash('sha256', md5($credentials['password'])) === $admin->mpasswd)) {
            
            Auth::guard('admin')->login($admin, $request->filled('remember'));
            $request->session()->regenerate();
            return redirect()->intended(route('affiliate.dashboard'));
        }

        return back()->withErrors([
            'manager_id' => '제공된 자격 증명이 기록과 일치하지 않습니다.',
        ])->onlyInput('manager_id');
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function validateLogin(Request $request)
    {
        $request->validate([
            'manager_id' => 'required|string',
            'password' => 'required|string',
        ]);
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('affiliate.login');
    }
}
