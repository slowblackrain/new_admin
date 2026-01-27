@extends('layouts.front')

@section('content')
<div class="location_wrap">
    <div class="location_cont">
        <em><a href="/" class="local_home">HOME</a> &gt; 마이페이지 &gt; 배송지 관리</em>
    </div>
</div>

<div id="content" class="container clearbox">
    @include('front.mypage.sidebar')

    <div id="mypage_content" style="float: right; width: 960px;">
        <h3 class="mypage_tit">배송지 관리</h3>

        <div class="btn_area text-right" style="margin-bottom: 10px;">
            <a href="{{ route('mypage.delivery_address.create') }}" class="btn_blue">배송지 등록</a>
        </div>

        <table class="board_table address_table">
            <colgroup>
                <col width="100" />
                <col width="" />
                <col width="150" />
                <col width="150" />
            </colgroup>
            <thead>
                <tr>
                    <th>배송지명</th>
                    <th>주소/연락처</th>
                    <th>받는분</th>
                    <th>관리</th>
                </tr>
            </thead>
            <tbody>
                @forelse($addresses as $address)
                <tr>
                    <td class="text-center">
                        @if($address->default == 'Y')
                            <span class="badge_notice">기본배송지</span><br>
                        @endif
                        {{ $address->shipping_name ?? '배송지' }}
                    </td>
                    <td>
                        ({{ $address->recipient_zipcode }}) {{ $address->recipient_address }} 
                        {{ $address->recipient_address_detail }}<br>
                        <span style="color:#888;">{{ $address->recipient_mobile }} / {{ $address->recipient_phone }}</span>
                    </td>
                    <td class="text-center">{{ $address->recipient_user_name }}</td>
                    <td class="text-center">
                        <a href="{{ route('mypage.delivery_address.edit', $address->address_seq) }}" class="btn_s_white">수정</a>
                        <form action="{{ route('mypage.delivery_address.destroy', $address->address_seq) }}" method="POST" style="display:inline;" onsubmit="return confirm('정말 삭제하시겠습니까?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn_s_white">삭제</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="no_data">등록된 배송지가 없습니다.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<style>
    .mypage_tit { font-size: 24px; font-weight: bold; border-bottom: 2px solid #333; padding-bottom: 15px; margin-bottom: 30px; }
    .btn_blue { background: #007bff; color: #fff; padding: 8px 15px; text-decoration: none; border-radius: 4px; display: inline-block; }
    .btn_s_white { background: #fff; border: 1px solid #ddd; padding: 5px 10px; color: #666; font-size: 12px; cursor: pointer; text-decoration: none; }
    .board_table { width: 100%; border-top: 2px solid #333; border-bottom: 1px solid #ccc; }
    .board_table th { background: #f9f9f9; padding: 15px 0; border-bottom: 1px solid #ddd; font-weight: bold; color: #333; }
    .board_table td { padding: 15px 10px; border-bottom: 1px solid #eee; color: #666; font-size: 13px; line-height: 1.6; }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .badge_notice { background: #333; color: #fff; padding: 2px 5px; font-size: 11px; margin-bottom: 5px; display: inline-block; }
    .no_data { padding: 50px 0; text-align: center; }
</style>
@endsection
