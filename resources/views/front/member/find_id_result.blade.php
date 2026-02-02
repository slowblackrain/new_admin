@extends('layouts.front')

@section('content')
<div class="find_result_wrap" style="width: 400px; margin: 50px auto; padding: 20px; border: 1px solid #ddd; text-align: center;">
    <h2 style="margin-bottom: 20px; font-size: 20px; font-weight: bold;">아이디 찾기 결과</h2>
    
    <div class="result_msg" style="margin-bottom: 30px; font-size: 14px;">
        회원님의 아이디는 <strong style="color: #ff4e00; font-size: 16px;">{{ $maskedId }}</strong> 입니다.
    </div>

    <div class="btn_group">
        <button onclick="location.href='{{ route('member.login') }}'" style="width: 100px; height: 35px; background: #333; color: #fff; border: none; cursor: pointer;">로그인</button>
        <button onclick="location.href='{{ route('member.find_pw') }}'" style="width: 100px; height: 35px; background: #fff; color: #333; border: 1px solid #ccc; cursor: pointer;">비밀번호 찾기</button>
    </div>
</div>
@endsection
