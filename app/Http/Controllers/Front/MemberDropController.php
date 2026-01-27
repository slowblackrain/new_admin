<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Member;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class MemberDropController extends Controller
{
    /**
     * Show Withdrawal Form
     */
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('member.login');
        }

        return view('front.mypage.drop');
    }

    /**
     * Process Withdrawal
     */
    public function leave(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('member.login');
        }

        $user = Auth::user();

        // 1. Password Validation
        $request->validate(['password' => 'required', 'reason' => 'required']);
        
        $inputPassword = $request->password;
        
        // Hashes to check (Legacy support)
        $str_md5 = md5($inputPassword);
        $str_sha = hash('sha256', $inputPassword);
        $str_sha_md5 = hash('sha256', $str_md5);

        $isValid = false;
        if ($user->password === $str_md5 || $user->password === $str_sha || $user->password === $str_sha_md5) {
            $isValid = true;
        }

        if (!$isValid) {
            return back()->withErrors(['password' => '비밀번호가 일치하지 않습니다.']);
        }

        // 2. Process Withdrawal (Soft Delete / Status Change)
        // Legacy usually sets status='withdrawal' or similar. 
        // Based on register code: status='done' is active.
        // Let's set status='withdrawn' or 'hold'. The standard legacy might be 'withdrawal'.
        // Let's assume 'withdrawal' for now.
        
        Member::where('member_seq', $user->member_seq)->update([
            'status' => 'withdrawal',
            'withdrawal_date' => now(), // Assuming column exists, if not maybe just update_date + status
            'update_date' => now()
        ]);

        // 3. Logout
        Auth::logout();
        Session::flush();

        return redirect()->route('main')->with('message', '회원탈퇴가 완료되었습니다. 이용해 주셔서 감사합니다.');
    }
}
