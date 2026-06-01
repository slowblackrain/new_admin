@extends('layouts.front')

@section('content')
    <div class="doto-member-bg" style="background: #f7f7f7; padding: 60px 0; min-height: 600px;">
        <div id="doto_login" class="container"
            style="width: 789px; margin: 0 auto; background: #fff; padding: 50px 40px; border: 1px solid #ddd; box-sizing: border-box;">

            {{-- Title Section --}}
            <div class="login-title" style="text-align: center; margin-bottom: 35px;">
                <h2 style="font-size: 28px; font-weight: bold; color: #111; letter-spacing: 2px; margin: 0 0 10px 0;">로 그 인</h2>
                <p style="color: #666; font-size: 13px; line-height: 1.6; margin: 0;">
                    가입하신 도매토피아 <strong>아이디</strong>와 <strong>비밀번호</strong>를 입력해주세요.<br>
                    비밀번호는 대소문자를 구분합니다.
                </p>
            </div>

            {{-- Centralized Form Container --}}
            <div style="width: 393px; margin: 0 auto;">
                <form name="loginForm" method="post" action="{{ route('member.login_process') }}">
                    @csrf
                    
                    {{-- return_url preservation --}}
                    @if(request('return_url'))
                        <input type="hidden" name="return_url" value="{{ request('return_url') }}">
                    @endif

                    <div class="login-info-wrap" style="width: 100%;">
                        {{-- Username Input --}}
                        <input type="text" name="userid" placeholder="아이디"
                            style="width: 100%; height: 45px; padding: 0 15px; margin-bottom: 10px; box-sizing: border-box; border: 1px solid #ccc; font-size: 14px; border-radius: 2px; outline: none; transition: border 0.2s;"
                            onfocus="this.style.border='1px solid #2b77f3'" onblur="this.style.border='1px solid #ccc'">
                        
                        {{-- Password Input --}}
                        <input type="password" name="password" placeholder="비밀번호"
                            style="width: 100%; height: 45px; padding: 0 15px; margin-bottom: 12px; box-sizing: border-box; border: 1px solid #ccc; font-size: 14px; border-radius: 2px; outline: none; transition: border 0.2s;"
                            onfocus="this.style.border='1px solid #2b77f3'" onblur="this.style.border='1px solid #ccc'">

                        {{-- Remember ID Checkbox --}}
                        <div style="margin-bottom: 20px; display: flex; align-items: center; justify-content: flex-start;">
                            <input type="checkbox" name="idsave" id="idsave" value="checked"
                                style="width: 15px; height: 15px; margin: 0 6px 0 0; vertical-align: middle; cursor: pointer;">
                            <label for="idsave" style="font-size: 13px; color: #555; cursor: pointer; user-select: none; vertical-align: middle; line-height: 15px;">아이디 저장</label>
                        </div>

                        {{-- Login Submit Button --}}
                        <button type="submit"
                            style="width: 100%; height: 50px; background: #2b77f3; color: #fff; font-size: 16px; font-weight: bold; border: none; border-radius: 3px; cursor: pointer; transition: background 0.2s;"
                            onmouseover="this.style.background='#1a5ecb'" onmouseout="this.style.background='#2b77f3'">로그인</button>

                        {{-- Non-member Settle / Buy Button (Only displays when return_url is present) --}}
                        @if(request('return_url'))
                            @php
                                $nonMemberOrderUrl = request('return_url');
                                if ($nonMemberOrderUrl) {
                                    $separator = Str::contains($nonMemberOrderUrl, '?') ? '&' : '?';
                                    $nonMemberOrderUrl .= $separator . 'guest=1';
                                }
                            @endphp
                            <div style="margin-top: 10px; width: 100%;">
                                <a href="{{ $nonMemberOrderUrl }}"
                                    style="display: flex; align-items: center; justify-content: center; width: 100%; height: 50px; background: #7f8c8d; color: #fff; font-size: 16px; font-weight: bold; text-decoration: none; border-radius: 3px; box-sizing: border-box; transition: background 0.2s;"
                                    onmouseover="this.style.background='#6c7a7b'" onmouseout="this.style.background='#7f8c8d'">
                                    비회원으로 구매하기
                                </a>
                            </div>
                        @endif
                    </div>
                </form>

                {{-- Find PW & Join Links --}}
                <div class="login-nav-wrap" style="margin-top: 20px; text-align: center; font-size: 13px; color: #888; border-bottom: 1px solid #eee; padding-bottom: 25px;">
                    <a href="{{ route('member.find_id') }}" style="color: #666; text-decoration: none;">아이디/비밀번호 찾기</a>
                    <span style="margin: 0 10px; color: #ddd;">|</span>
                    <a href="{{ route('member.agreement') }}" style="color: #666; text-decoration: none; font-weight: bold;">회원가입</a>
                </div>

                {{-- Social Easy Login Section --}}
                <div class="snsjoin" style="width: 100%; text-align: center; margin-top: 25px;">
                    <div style="font-size: 12px; color: #999; margin-bottom: 15px; letter-spacing: 1px;">- 간편 로그인 -</div>
                    
                    <ul class="sns_list" style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px;">
                        {{-- Naver Login --}}
                        <li>
                            <a href="#" onclick="alert('준비 중입니다.'); return false;"
                                style="display: flex; align-items: center; justify-content: center; width: 100%; height: 48px; background: #03cf5d; color: #fff; font-size: 14px; font-weight: bold; text-decoration: none; border-radius: 3px; transition: opacity 0.2s;"
                                onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                                <i class="fab fa-neos" style="margin-right: 8px; font-size: 16px;"></i> 네이버 아이디로 로그인
                            </a>
                        </li>
                        
                        {{-- Kakao Login --}}
                        <li>
                            <a href="#" onclick="alert('준비 중입니다.'); return false;"
                                style="display: flex; align-items: center; justify-content: center; width: 100%; height: 48px; background: #fee500; color: #191919; font-size: 14px; font-weight: bold; text-decoration: none; border-radius: 3px; transition: opacity 0.2s;"
                                onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                                <i class="fas fa-comment" style="margin-right: 8px; font-size: 16px;"></i> 카카오톡 아이디로 로그인
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
@endsection