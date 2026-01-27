@extends('layouts.front')

@section('content')
<div class="location_wrap">
    <div class="location_cont">
        <em><a href="/" class="local_home">HOME</a> &gt; 마이페이지 &gt; 회원탈퇴</em>
    </div>
</div>

<div id="content" class="container clearbox">
    @include('front.mypage.sidebar')

    <div id="mypage_content" style="float: right; width: 960px;">
        <h3 class="mypage_tit">회원탈퇴</h3>

        <div style="border: 1px solid #ddd; padding: 30px; margin-top: 30px;">
            <div style="margin-bottom: 20px; font-size: 14px; color: #666; line-height: 1.6;">
                <strong style="color: #d00; display: block; margin-bottom: 10px;">[회원탈퇴 약관]</strong>
                1. 탈퇴시 회원님의 정보는 상품 반품 및 A/S를 위해 전자상거래 등에서의 소비자 보호에 관한 법률에 의거한<br>
                고객정보 보호정책에 따라 관리됩니다.<br>
                2. 탈퇴시 보유하고 계신 적립금과 쿠폰은 모두 소멸되며 복구되지 않습니다.<br>
                3. 탈퇴 후 재가입시 신규 회원 혜택이 적용되지 않을 수 있습니다.<br>
            </div>

            <form action="{{ route('mypage.member.leave') }}" method="POST" onsubmit="return confirm('정말로 탈퇴하시겠습니까?');">
                @csrf
                <table class="form_table">
                    <colgroup>
                        <col width="150" />
                        <col width="" />
                    </colgroup>
                    <tbody>
                        <tr>
                            <th>아이디</th>
                            <td>{{ Auth::user()->userid }}</td>
                        </tr>
                        <tr>
                            <th>비밀번호</th>
                            <td>
                                <input type="password" name="password" class="input_text" required>
                                <span class="desc">본인 확인을 위해 비밀번호를 입력해주세요.</span>
                            </td>
                        </tr>
                        <tr>
                            <th>탈퇴사유</th>
                            <td>
                                <textarea name="reason" style="width: 95%; height: 80px; padding: 10px; border: 1px solid #ccc; resize: none;" required placeholder="탈퇴 사유를 간략하게 남겨주시면 서비스 개선에 반영하겠습니다."></textarea>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="btn_area text-center" style="margin-top: 30px;">
                    <button type="submit" class="btn_gray">회원탈퇴</button>
                    <a href="{{ route('mypage.index') }}" class="btn_white">취소</a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .mypage_tit { font-size: 24px; font-weight: bold; border-bottom: 2px solid #333; padding-bottom: 15px; margin-bottom: 30px; }
    .form_table { width: 100%; border-top: 2px solid #333; border-bottom: 1px solid #ccc; }
    .form_table th { background: #f9f9f9; padding: 15px 10px; border-bottom: 1px solid #eee; font-weight: bold; color: #333; text-align: left; }
    .form_table td { padding: 10px; border-bottom: 1px solid #eee; }
    .input_text { height: 32px; border: 1px solid #ccc; padding: 0 10px; }
    .desc { color: #888; font-size: 12px; margin-left: 5px; }
    .btn_gray { background: #666; color: #fff; padding: 10px 30px; border: none; font-size: 14px; cursor: pointer; }
    .btn_white { background: #fff; border: 1px solid #ccc; padding: 10px 30px; color: #333; text-decoration: none; display: inline-block; font-size: 14px; }
    .text-center { text-align: center; }
</style>
@endsection
