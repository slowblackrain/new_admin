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
        <h3 class="mypage_tit">회원정보 수정</h3>

        <form action="{{ route('mypage.member.update') }}" method="POST">
            @csrf
            @method('PUT')

            <table class="form_table" style="margin-top: 20px;">
                <colgroup>
                    <col width="150" />
                    <col width="" />
                </colgroup>
                <tbody>
                    <tr>
                        <th>이름</th>
                        <td>{{ $user->user_name }}</td>
                    </tr>
                    <tr>
                        <th>아이디</th>
                        <td>{{ $user->userid }}</td>
                    </tr>
                    <tr>
                        <th>이메일 <span class="required">*</span></th>
                        <td>
                            <input type="email" name="email" value="{{ $user->email }}" class="input_text" style="width: 300px;" required>
                            @error('email') <span class="error">{{ $message }}</span> @enderror
                        </td>
                    </tr>
                    <tr>
                        <th>휴대폰 <span class="required">*</span></th>
                        <td>
                            <input type="text" name="cellphone" value="{{ $user->cellphone }}" class="input_text" required>
                        </td>
                    </tr>
                    <tr>
                        <th>일반전화</th>
                        <td>
                            <input type="text" name="phone" value="{{ $user->phone }}" class="input_text">
                        </td>
                    </tr>
                    <tr>
                        <th>주소</th>
                        <td>
                            <input type="text" name="zipcode" value="{{ $user->zipcode }}" class="input_text" style="width: 100px;" placeholder="우편번호">
                            <button type="button" class="btn_s_white" onclick="openDaumPostcode()">주소검색</button><br>
                            <input type="text" name="address" value="{{ $user->address }}" class="input_text" style="width: 400px; margin-top: 5px;" placeholder="기본주소"><br>
                            <input type="text" name="address_detail" value="{{ $user->address_detail }}" class="input_text" style="width: 400px; margin-top: 5px;" placeholder="상세주소">
                            <input type="hidden" name="address_street" value="{{ $user->address_street }}">
                        </td>
                    </tr>
                    <tr>
                        <th>비밀번호 변경</th>
                        <td>
                            <input type="password" name="new_password" class="input_text" placeholder="변경할 경우 입력">
                            <span class="desc">변경시에만 입력해주세요.</span>
                        </td>
                    </tr>
                    <tr>
                        <th>비밀번호 확인</th>
                        <td>
                            <input type="password" name="new_password_confirmation" class="input_text">
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="btn_area text-center" style="margin-top: 30px;">
                <button type="submit" class="btn_blue">정보수정</button>
                <a href="{{ route('mypage.index') }}" class="btn_white">취소</a>
            </div>
        </form>
    </div>
</div>

<script src="//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
<script>
    function openDaumPostcode() {
        new daum.Postcode({
            oncomplete: function(data) {
                var addr = ''; 
                var extraAddr = ''; 

                if (data.userSelectedType === 'R') { 
                    addr = data.roadAddress;
                } else { 
                    addr = data.jibunAddress;
                }

                document.querySelector('input[name=zipcode]').value = data.zonecode;
                document.querySelector('input[name=address]').value = addr;
                document.querySelector('input[name=address_street]').value = data.roadAddress; // Save road address as street
                document.querySelector('input[name=address_detail]').focus();
            }
        }).open();
    }
</script>

<style>
    .mypage_tit { font-size: 24px; font-weight: bold; border-bottom: 2px solid #333; padding-bottom: 15px; margin-bottom: 30px; }
    .form_table { width: 100%; border-top: 2px solid #333; border-bottom: 1px solid #ccc; }
    .form_table th { background: #f9f9f9; padding: 15px 10px; border-bottom: 1px solid #eee; font-weight: bold; color: #333; text-align: left; }
    .form_table td { padding: 10px; border-bottom: 1px solid #eee; }
    .input_text { height: 32px; border: 1px solid #ccc; padding: 0 10px; }
    .required { color: #d00; }
    .error { color: #d00; font-size: 12px; margin-left: 5px; }
    .desc { color: #888; font-size: 12px; margin-left: 5px; }
    .btn_blue { background: #333; color: #fff; padding: 10px 30px; border: none; font-size: 14px; cursor: pointer; }
    .btn_white { background: #fff; border: 1px solid #ccc; padding: 10px 30px; color: #333; text-decoration: none; display: inline-block; font-size: 14px; }
    .btn_s_white { background: #fff; border: 1px solid #ccc; padding: 5px 10px; color: #666; font-size: 12px; cursor: pointer; }
    .text-center { text-align: center; }
</style>
@endsection
