@extends('layouts.front')

@section('content')
<div class="location_wrap">
    <div class="location_cont">
        <em><a href="/" class="local_home">HOME</a> &gt; 마이페이지 &gt; 회원정보 수정</em>
    </div>
</div>

<div id="content" class="container clearbox">
    @include('front.mypage.sidebar')

    <div id="mypage_content" style="float: right; width: 960px;">
        <h3 class="mypage_tit">비밀번호 확인</h3>

        <div style="border: 1px solid #ddd; padding: 50px; text-align: center; margin-top: 30px;">
            <p style="font-size: 14px; margin-bottom: 20px;">
                회원님의 소중한 개인정보 보호를 위해 비밀번호를 다시 한번 입력해주세요.
            </p>

            <form action="{{ route('mypage.member.verify_password') }}" method="POST">
                @csrf
                <div style="display: inline-block; text-align: left;">
                    <div style="margin-bottom: 10px;">
                        <span style="display: inline-block; width: 80px; font-weight: bold;">아이디</span>
                        <span>{{ Auth::user()->userid }}</span>
                    </div>
                    <div>
                        <label for="password" style="display: inline-block; width: 80px; font-weight: bold;">비밀번호</label>
                        <input type="password" name="password" id="password" class="input_text" style="width: 200px;" required>
                    </div>
                </div>

                @if($errors->has('password'))
                    <div style="color: red; margin-top: 10px;">{{ $errors->first('password') }}</div>
                @endif

                <div style="margin-top: 20px;">
                    <button type="submit" class="btn_blue">확인</button>
                    <a href="{{ route('mypage.index') }}" class="btn_white">취소</a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .mypage_tit { font-size: 24px; font-weight: bold; border-bottom: 2px solid #333; padding-bottom: 15px; }
    .input_text { height: 32px; border: 1px solid #ccc; padding: 0 10px; }
    .btn_blue { background: #333; color: #fff; padding: 10px 30px; border: none; font-size: 14px; cursor: pointer; }
    .btn_white { background: #fff; border: 1px solid #ccc; padding: 10px 30px; color: #333; text-decoration: none; display: inline-block; font-size: 14px; }
</style>
@endsection
