@extends('layouts.front')

@section('content')
<div class="location_wrap">
    <div class="location_cont">
        <em><a href="/" class="local_home">HOME</a> &gt; 마이페이지 &gt; 배송지 등록/수정</em>
    </div>
</div>

<div id="content" class="container clearbox">
    @include('front.mypage.sidebar')

    <div id="mypage_content" style="float: right; width: 960px;">
        <h3 class="mypage_tit">배송지 {{ isset($address) ? '수정' : '등록' }}</h3>

        <form action="{{ isset($address) ? route('mypage.delivery_address.update', $address->address_seq) : route('mypage.delivery_address.store') }}" method="POST">
            @csrf
            @if(isset($address))
                @method('PUT')
            @endif

            <table class="form_table">
                <colgroup>
                    <col width="150" />
                    <col width="" />
                </colgroup>
                <tbody>
                    <tr>
                        <th>배송지명</th>
                        <td><input type="text" name="shipping_name" class="input_text" value="{{ $address->shipping_name ?? '' }}" placeholder="예: 집, 회사"></td>
                    </tr>
                    <tr>
                        <th>받는분 <span class="required">*</span></th>
                        <td><input type="text" name="recipient_user_name" class="input_text" value="{{ $address->recipient_user_name ?? '' }}" required></td>
                    </tr>
                    <tr>
                        <th>휴대폰 <span class="required">*</span></th>
                        <td><input type="text" name="recipient_mobile" class="input_text" value="{{ $address->recipient_mobile ?? '' }}" required></td>
                    </tr>
                    <tr>
                        <th>일반전화</th>
                        <td><input type="text" name="recipient_phone" class="input_text" value="{{ $address->recipient_phone ?? '' }}"></td>
                    </tr>
                    <tr>
                        <th>주소 <span class="required">*</span></th>
                        <td>
                            <input type="text" name="recipient_zipcode" class="input_text" style="width: 100px;" value="{{ $address->recipient_zipcode ?? '' }}" required placeholder="우편번호">
                            <button type="button" class="btn_s_white" onclick="openDaumPostcode()">주소검색</button><br>
                            <input type="text" name="recipient_address" class="input_text" style="width: 400px; margin-top: 5px;" value="{{ $address->recipient_address ?? '' }}" required placeholder="기본주소"><br>
                            <input type="text" name="recipient_address_detail" class="input_text" style="width: 400px; margin-top: 5px;" value="{{ $address->recipient_address_detail ?? '' }}" placeholder="상세주소">
                        </td>
                    </tr>
                    <tr>
                        <th>기본배송지</th>
                        <td>
                            <label>
                                <input type="checkbox" name="default" value="Y" {{ (isset($address) && $address->default == 'Y') ? 'checked' : '' }}>
                                기본 배송지로 설정
                            </label>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="btn_area text-center" style="margin-top: 30px;">
                <button type="submit" class="btn_blue">저장하기</button>
                <a href="{{ route('mypage.delivery_address.index') }}" class="btn_white">취소</a>
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

                document.querySelector('input[name=recipient_zipcode]').value = data.zonecode;
                document.querySelector('input[name=recipient_address]').value = addr;
                document.querySelector('input[name=recipient_address_detail]').focus();
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
    .btn_blue { background: #333; color: #fff; padding: 10px 30px; border: none; font-size: 14px; cursor: pointer; }
    .btn_white { background: #fff; border: 1px solid #ccc; padding: 10px 30px; color: #333; text-decoration: none; display: inline-block; font-size: 14px; }
    .btn_s_white { background: #fff; border: 1px solid #ccc; padding: 5px 10px; color: #666; font-size: 12px; cursor: pointer; }
    .text-center { text-align: center; }
</style>
@endsection
