<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Member;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class MemberModifyController extends Controller
{
    /**
     * Step 1: Password Check Form
     */
    public function checkPassword()
    {
        if (!Auth::check()) {
            return redirect()->route('member.login');
        }

        return view('front.mypage.check_password');
    }

    /**
     * Step 1 Process: Verify Password
     */
    public function verifyPassword(Request $request)
    {
        $request->validate(['password' => 'required']);

        $user = Auth::user();
        $inputPassword = $request->password;

        // Hashes to check (Legacy support)
        $str_md5 = md5($inputPassword);
        $str_sha = hash('sha256', $inputPassword);
        $str_sha_md5 = hash('sha256', $str_md5);

        $isValid = false;

        // Check against current stored password
        if ($user->password === $str_md5 || $user->password === $str_sha || $user->password === $str_sha_md5) {
            $isValid = true;
        }

        if ($isValid) {
            // Store a session flag to allow access to edit page
            session(['password_verified' => true]);
            return redirect()->route('mypage.member.edit');
        } else {
            return back()->withErrors(['password' => '비밀번호가 일치하지 않습니다.']);
        }
    }

    /**
     * Step 2: Edit Form
     */
    public function edit()
    {
        if (!Auth::check()) {
            return redirect()->route('member.login');
        }

        // Security Check: Must have passed verifyPassword
        if (!session('password_verified')) {
            return redirect()->route('mypage.member.check_password');
        }

        $user = Auth::user();
        return view('front.mypage.my_info', compact('user'));
    }

    /**
     * Step 2 Process: Update Member Info
     */
    public function update(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('member.login');
        }

        $user = Auth::user();

        // Validation
        $validated = $request->validate([
            'email' => 'required|email|unique:fm_member,email,' . $user->member_seq . ',member_seq',
            'cellphone' => 'required',
            // Add other fields as necessary
        ]);

        $data = [
            'email' => $request->email,
            'cellphone' => $request->cellphone,
            'phone' => $request->phone,
            'zipcode' => $request->zipcode,
            'address' => $request->address,
            'address_street' => $request->address_street,
            'address_detail' => $request->address_detail,
            'update_date' => now(),
        ];

        // Handle Password Change
        if ($request->filled('new_password')) {
            $request->validate([
                'new_password' => 'min:4|confirmed', // expects new_password_confirmation
            ]);

            // New passwords stored as SHA-256
            $data['password'] = hash('sha256', $request->new_password);
        }

        Member::where('member_seq', $user->member_seq)->update($data);

        // Clear verification flag
        session()->forget('password_verified');

        return redirect()->route('mypage.index')->with('success', '회원정보가 수정되었습니다.');
    }
}
