@extends('layouts.front')

@section('content')
<div class="find_result_wrap" style="width: 400px; margin: 50px auto; padding: 20px; border: 1px solid #ddd; text-align: center;">
    <h2 style="margin-bottom: 20px; font-size: 20px; font-weight: bold;">비밀번호 찾기 결과</h2>
    
    <div class="result_msg" style="margin-bottom: 20px; font-size: 14px; line-height: 1.6;">
        회원님의 임시 비밀번호가 발급되었습니다.<br>
        로그인 후 반드시 비밀번호를 변경해 주세요.
    </div>

    <div class="temp_pw_box" style="margin-bottom: 30px; padding: 15px; background: #f9f9f9; border: 1px solid #eee;">
        <strong style="color: #ff4e00; font-size: 18px; letter-spacing: 1px;">{{ $tempPw }}</strong>
    </div>

    <div class="btn_group">
        <button onclick="location.href='{{ route('member.login') }}'" style="width: 100%; height: 40px; background: #333; color: #fff; border: none; font-size: 14px; cursor: pointer;">로그인 하러가기</button>
    </div>
</div>
@endsection
