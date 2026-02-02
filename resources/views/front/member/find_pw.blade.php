@extends('layouts.front')

@section('content')
<div class="find_pw_wrap" style="width: 400px; margin: 50px auto; padding: 20px; border: 1px solid #ddd;">
    <h2 style="text-align: center; margin-bottom: 20px; font-size: 20px; font-weight: bold;">비밀번호 찾기</h2>
    
    <form action="{{ route('member.find_pw_result') }}" method="POST">
        @csrf
        <div class="form_group" style="margin-bottom: 15px;">
            <label for="userid" style="display: block; margin-bottom: 5px;">아이디</label>
            <input type="text" name="userid" id="userid" required style="width: 100%; height: 35px; border: 1px solid #ccc; padding: 0 5px; box-sizing: border-box;">
        </div>
        <div class="form_group" style="margin-bottom: 15px;">
            <label for="user_name" style="display: block; margin-bottom: 5px;">이름</label>
            <input type="text" name="user_name" id="user_name" required style="width: 100%; height: 35px; border: 1px solid #ccc; padding: 0 5px; box-sizing: border-box;">
        </div>
        <div class="form_group" style="margin-bottom: 15px;">
            <label for="email" style="display: block; margin-bottom: 5px;">이메일</label>
            <input type="email" name="email" id="email" required style="width: 100%; height: 35px; border: 1px solid #ccc; padding: 0 5px; box-sizing: border-box;">
        </div>

        @if($errors->any())
            <div style="color: red; margin-bottom: 10px; font-size: 12px; text-align: center;">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="btn_group">
            <button type="submit" style="width: 100%; height: 40px; background: #333; color: #fff; border: none; font-size: 14px; cursor: pointer;">비밀번호 찾기</button>
        </div>
        
        <div class="links" style="margin-top: 10px; text-align: center; font-size: 12px;">
            <a href="{{ route('member.find_id') }}" style="color: #666; text-decoration: underline;">아이디 찾기</a> | 
            <a href="{{ route('member.login') }}" style="color: #666; text-decoration: underline;">로그인</a>
        </div>
    </form>
</div>
@endsection
