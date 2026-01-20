<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Member;
use Illuminate\Support\Facades\Session;

class MemberController extends Controller
{
    public function login()
    {
        return view('front.member.login');
    }

    public function join()
    {
        return view('front.member.join');
    }

    public function login_process(Request $request)
    {
        $credentials = $request->validate([
            'userid' => 'required',
            'password' => 'required',
        ]);

        $userid = $credentials['userid'];
        $password = $credentials['password'];

        // 1. Generate PHP-side hashes
        $str_md5 = md5($password);
        $str_sha = hash('sha256', $password);

        $str_sha_md5 = hash('sha256', $str_md5);

        // Removed legacy DB password() check as it may not be supported in modern MySQL.
        $str_password = '';
        $str_oldpassword = '';
        // 3. Query User
        // Legacy: where A.userid=? and (A.password=? or ...)
        $member = Member::where('userid', $userid)
            ->where(function ($query) use ($str_md5, $str_sha, $str_sha_md5) {
                $query->where('password', $str_md5)
                    ->orWhere('password', $str_sha)
                    ->orWhere('password', $str_sha_md5);
            })
            ->first();

        if ($member) {
            // Login Success
            \Illuminate\Support\Facades\Auth::login($member);

            return redirect()->route('main');
        } else {
            return back()->withErrors(['userid' => 'Invalid credentials.']);
        }
    }

    public function agreement()
    {
        return view('front.member.agreement');
    }

    public function register(Request $request)
    {
        $type = $request->query('type', 'general');
        return view('front.member.register', compact('type'));
    }

    public function register_process(Request $request)
    {
        // 1. Validation
        $validated = $request->validate([
            'userid' => 'required|unique:fm_member,userid|min:4|max:20',
            'password' => 'required|min:4',
            'username' => 'required',
            'email' => 'required|email|unique:fm_member,email',
            'cellphone' => 'required',
        ]);

        // 2. Hash Password (SHA-256 standard for new users)
        $passwordHash = hash('sha256', $validated['password']);

        // 3. Create Member
        $member = Member::create([
            'userid' => $validated['userid'],
            'password' => $passwordHash,
            'user_name' => $validated['username'],
            'nickname' => $validated['username'],
            'email' => $validated['email'],
            'phone' => '',
            'cellphone' => $validated['cellphone'],
            'zipcode' => '',
            'address' => '',
            'address_street' => '',
            'address_detail' => '',

            'regist_date' => now(),
            'update_date' => now(),

            'status' => 'done',
            'gubun_seq' => 1,
            'group_seq' => 1,
            'group_set_date' => '1000-01-01 00:00:00',

            'rute' => 'none',
            'mailing' => 'n',
            'sms' => 'n',
            'sex' => 'male',
            'birthday' => '1000-01-01',
            'anniversary' => '',

            'lastlogin_date' => now(),
            'grade_update_date' => '1000-01-01 00:00:00',
            'marketplace' => '',

            'account_cnt' => 0,
            'Personal_ccn' => '',
        ]);

        // 4. Login after registration
        \Illuminate\Support\Facades\Auth::login($member);

        return redirect()->route('main')->with('message', '회원가입이 완료되었습니다.');
    }

    public function logout()
    {
        \Illuminate\Support\Facades\Auth::logout();
        return redirect()->route('main');
    }

    public function check_id(Request $request)
    {
        $userid = $request->input('userid');

        if (empty($userid)) {
            return response()->json(['result' => 'empty', 'msg' => '아이디를 입력해주세요.']);
        }

        // 4-20 chars validation logic if needed here, but simple check first
        $exists = Member::where('userid', $userid)->exists();

        if ($exists) {
            return response()->json(['result' => 'duplicate', 'msg' => '이미 사용중인 아이디입니다.']);
        } else {
            return response()->json(['result' => 'success', 'msg' => '사용 가능한 아이디입니다.']);
        }
    }
}
