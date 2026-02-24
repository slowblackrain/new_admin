<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Member;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

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

            return redirect()->route('home');
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
        $isBusiness = $request->input('type') === 'business';

        $rules = [
            'userid' => 'required|unique:fm_member,userid|min:4|max:20',
            'password' => 'required|min:4',
            'username' => 'required',
            'email' => 'required|email|unique:fm_member,email',
            'cellphone' => 'required',
        ];

        if ($isBusiness) {
            $rules['bname'] = 'required';
            $rules['bno'] = 'required';
            $rules['bceo'] = 'required';
            $rules['bno_file'] = 'required|file|mimes:jpeg,png,jpg,pdf|max:5120';
        }

        // 1. Validation
        $validated = $request->validate($rules);

        // 2. Hash Password (SHA-256 standard for new users)
        $passwordHash = hash('sha256', $validated['password']);

        // Check Admin policy for B2B Auto Approve (Assuming it's stored in fm_config.b2b_auto_approve or similar, default false)
        // Here we default to group 1 (General/Wait) if not auto-approved.
        // If config requires, it could be group 2 (Dealer) etc.
        // Let's assume manual approval: group 1, status 'done', mtype 'b2b'.
        $mtype = $isBusiness ? 'b2b' : 'person';
        
        $config = DB::table('fm_config')->first();
        // Fallback to manual approval (group 1) if b2b_auto_approve is not set or false
        $groupSeq = 1; 
        if ($isBusiness && isset($config->b2b_auto_approve) && $config->b2b_auto_approve == 1) {
            // Assume group 2 is a basic business/dealer group
            $groupSeq = 2; 
        }

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
            'company' => $isBusiness ? $validated['bname'] : '',
            'mtype' => $mtype,

            'regist_date' => now(),
            'update_date' => now(),

            'status' => 'done',
            'gubun_seq' => 1,
            'group_seq' => $groupSeq,
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
            'admin_memo' => $isBusiness && $groupSeq == 1 ? 'B2B 가입 승인 대기' : '',

            'account_cnt' => 0,
            'Personal_ccn' => '',
        ]);

        // 4. Handle B2B Business Fields and Upload
        if ($isBusiness) {
            $bnoFilePath = '';
            if ($request->hasFile('bno_file')) {
                // Store file in storage/app/public/business_licenses
                $path = $request->file('bno_file')->store('business_licenses', 'public');
                $bnoFilePath = '/storage/' . $path;
            }

            // Insert into fm_member_business
            DB::table('fm_member_business')->insert([
                'member_seq' => $member->member_seq,
                'bname' => $validated['bname'],
                'bceo' => $validated['bceo'],
                'bno' => $validated['bno'],
                'bno_file' => $bnoFilePath,
                'bstatus' => 'done', // Or default
                'bzipcode' => '',
                'baddress' => '',
                'baddress_detail' => '',
                'baddress_street' => '',
            ]);
        }

        // 5. Login after registration
        \Illuminate\Support\Facades\Auth::login($member);

        $msg = $isBusiness && $groupSeq == 1 
            ? '기업 회원가입이 완료되었습니다. 관리자 승인 후 도매 혜택이 적용됩니다.' 
            : '회원가입이 완료되었습니다.';

        return redirect()->route('home')->with('message', $msg);
    }

    public function logout()
    {
        \Illuminate\Support\Facades\Auth::logout();
        return redirect()->route('home');
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
    public function find_id()
    {
        return view('front.member.find_id');
    }

    public function find_id_result(Request $request)
    {
        $request->validate([
            'user_name' => 'required',
            'email' => 'required|email',
        ]);

        $member = Member::where('user_name', $request->user_name)
            ->where('email', $request->email)
            ->first();

        if (!$member) {
            return back()->withErrors(['msg' => '일치하는 회원 정보를 찾을 수 없습니다.']);
        }

        // Mask ID (e.g., ab***)
        $len = strlen($member->userid);
        $visibleLen = $len > 3 ? 3 : 1;
        $maskedId = substr($member->userid, 0, $visibleLen) . str_repeat('*', $len - $visibleLen);

        return view('front.member.find_id_result', compact('maskedId', 'member'));
    }

    public function find_pw()
    {
        return view('front.member.find_pw');
    }

    public function find_pw_result(Request $request)
    {
        $request->validate([
            'userid' => 'required',
            'user_name' => 'required',
            'email' => 'required|email',
        ]);

        $member = Member::where('userid', $request->userid)
            ->where('user_name', $request->user_name)
            ->where('email', $request->email)
            ->first();

        if (!$member) {
            return back()->withErrors(['msg' => '일치하는 회원 정보를 찾을 수 없습니다.']);
        }

        // Generate Temp Password
        $tempPw = Str::random(8);
        $hashedPw = hash('sha256', $tempPw);

        $member->password = $hashedPw;
        $member->save();

        // In production, send Email/SMS. For Dev, show it.
        return view('front.member.find_pw_result', compact('tempPw', 'member'));
    }
}
