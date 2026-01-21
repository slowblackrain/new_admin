<?php

namespace App\Http\Controllers\Seller\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    // Middleware is now handled in routes/web.php or via static method
    // public function __construct()
    // {
    //    $this->middleware('guest:seller')->except('logout');
    // }

    public function showLoginForm()
    {
        return view('seller.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'provider_id' => 'required',
            'provider_passwd' => 'required',
        ]);

        $credentials = [
            'provider_id' => $request->provider_id,
            'password' => $request->provider_passwd, // Auth checks 'password', but our model maps it
        ];

        // Attempt to log the seller in
        // Note: Models/Seller must handle MD5/custom hashing if not using bcrypt
        if (Auth::guard('seller')->attempt($credentials, $request->remember)) {
            return redirect()->intended(route('seller.dashboard'));
        }

        return redirect()->back()->withInput($request->only('provider_id', 'remember'))->withErrors([
            'provider_id' => '아이디 또는 비밀번호가 일치하지 않습니다.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('seller')->logout();
        $request->session()->invalidate();
        return redirect()->route('seller.login');
    }
}
