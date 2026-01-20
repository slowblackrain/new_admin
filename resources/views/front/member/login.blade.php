@extends('layouts.front')

@section('content')
    <div class="doto-member-bg" style="background: #f7f7f7; padding: 50px 0;">
        <div id="doto_login" class="container"
            style="width: 800px; margin: 0 auto; background: #fff; padding: 40px; border: 1px solid #ddd;">

            <div class="login-title" style="text-align: center; margin-bottom: 30px;">
                <h2 style="font-size: 28px; font-weight: bold; color: #333;">로 그 인</h2>
                <p style="color: #666; margin-top: 10px;">가입하신 도매토피아 <strong>아이디</strong>와 <strong>비밀번호</strong>를 입력해주세요.
                </p>
            </div>

            <div class="login-form-wrap" style="display: flex; gap: 40px; justify-content: center;">
                {{-- Login Form --}}
                <div class="fleft" style="width: 400px;">
                    <form name="loginForm" method="post" action="{{ route('member.login_process') }}">
                        @csrf
                        <div class="login-info-wrap">
                            <input type="text" name="userid" placeholder="아이디"
                                style="width: 100%; padding: 10px; margin-bottom: 10px; box-sizing: border-box; border: 1px solid #ddd;">
                            <input type="password" name="password" placeholder="비밀번호"
                                style="width: 100%; padding: 10px; margin-bottom: 10px; box-sizing: border-box; border: 1px solid #ddd;">

                            <div style="margin-bottom: 15px;">
                                <label><input type="checkbox" name="idsave"> 아이디 저장</label>
                            </div>

                            <button type="submit"
                                style="width: 100%; padding: 15px; background: #333; color: #fff; font-size: 18px; font-weight: bold; border: none; cursor: pointer;">로그인</button>
                        </div>
                    </form>

                    <div class="login-nav-wrap" style="margin-top: 15px; text-align: center; font-size: 13px;">
                        <a href="#">아이디/비밀번호 찾기</a> | <a href="{{ route('member.agreement') }}">회원가입</a>
                    </div>
                </div>

                {{-- Right Side / Banner --}}
                <div class="fright" style="width: 300px; padding-top: 20px;">
                    <div class="non-member-wrap" style="background: #f9f9f9; padding: 20px; text-align: center;">
                        <h3>비회원 주문조회</h3>
                        <p style="font-size: 12px; color: #888; margin: 10px 0;">비회원은 주문번호와 이메일로<br>조회할 수 있습니다.</p>
                        <a href="#" class="btn"
                            style="display: inline-block; padding: 5px 10px; border: 1px solid #ccc; font-size: 12px; text-decoration: none; color: #333;">조회하기</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection