@extends('layouts.front')

@section('content')
<div class="location_wrap">
    <div class="location_cont">
        <em><a href="/" class="local_home">HOME</a> &gt; 마이페이지 &gt; 
        @if($type == 'cancel') 주문취소
        @elseif($type == 'return') 반품신청
        @elseif($type == 'exchange') 교환신청
        @endif
        </em>
    </div>
</div>

<div class="content_wrap clearbox" style="padding-bottom: 100px;">
    @include('front.mypage.sidebar')

    <div id="mypage_content" style="float: right; width: 960px;">
        <div class="cart_title_area">
            <h3>
                @if($type == 'cancel') 주문취소 신청
                @elseif($type == 'return') 반품 신청
                @elseif($type == 'exchange') 교환 신청
                @endif
            </h3>
        </div>

        <form action="{{ route('mypage.claim.store', $order->order_seq) }}" method="POST">
            @csrf
            <input type="hidden" name="type" value="{{ $type }}">

            <div class="cart_list_area">
                <h4>대상 상품 선택</h4>
                <table class="cart_table">
                    <colgroup>
                        <col width="50" />
                        <col width="*" />
                        <col width="100" />
                        <col width="100" />
                    </colgroup>
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="check_all" checked onclick="toggleAll(this)"></th>
                            <th>상품정보</th>
                            <th>수량</th>
                            <th>상품금액</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr>
                            <td>
                                <input type="checkbox" name="items[]" value="{{ $item->item_seq }}" checked class="item_check">
                            </td>
                            <td style="text-align:left;">
                                {{ $item->goods_name }}
                                <div style="font-size:12px; color:#888;">{{ $item->options->first()->option1 ?? '' }}</div>
                            </td>
                            <td>{{ $item->options->first()->ea ?? 1 }}</td>
                            <td>{{ number_format($item->options->first()->price ?? 0) }}원</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                
                <h4 style="margin-top:30px;">
                    @if($type == 'cancel') 취소 사유
                    @else 신청 사유
                    @endif
                </h4>
                <div style="padding:15px; border:1px solid #ddd; background:#f9f9f9;">
                    <select name="reason" class="input_select" style="width:200px; margin-bottom:10px;">
                        <option value="">사유 선택</option>
                        @if($type == 'cancel')
                            <option value="단순변심">단순변심</option>
                            <option value="주문실수">주문실수</option>
                            <option value="서비스불만">서비스불만</option>
                            <option value="배송지연">배송지연</option>
                        @else
                            <option value="파손/불량">파손/불량</option>
                            <option value="오배송">오배송</option>
                            <option value="단순변심">단순변심</option>
                        @endif
                    </select>
                    <br>
                    <textarea name="reason_detail" style="width:100%; height:100px; padding:10px;" placeholder="상세 사유를 입력해주세요."></textarea>
                </div>

                @if($order->payment == 'bank' && ($type == 'cancel' || $type == 'return'))
                <h4 style="margin-top:30px;">환불 계좌 정보</h4>
                <div style="padding:15px; border:1px solid #ddd;">
                    <input type="text" name="refund_bank" placeholder="은행명" style="width:100px; padding:5px;">
                    <input type="text" name="refund_account" placeholder="계좌번호" style="width:200px; padding:5px;">
                    <input type="text" name="refund_depositor" placeholder="예금주" style="width:100px; padding:5px;">
                </div>
                @endif

                <div class="btn_area_center">
                    <button type="submit" class="btn_black">신청하기</button>
                    <a href="{{ route('mypage.order.view', $order->order_seq) }}" class="btn_white">취소</a>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function toggleAll(source) {
    checkboxes = document.getElementsByClassName('item_check');
    for(var i=0, n=checkboxes.length;i<n;i++) {
        checkboxes[i].checked = source.checked;
    }
}
</script>

<style>
    .cart_table { width:100%; border-collapse:collapse; border-top:2px solid #333; }
    .cart_table th { background:#f9f9f9; padding:10px; border-bottom:1px solid #ddd; }
    .cart_table td { padding:10px; border-bottom:1px solid #ddd; text-align:center; }
    .btn_area_center { margin-top:30px; text-align:center; }
    .btn_black { background:#333; color:#fff; padding:10px 30px; border:none; cursor:pointer; font-weight:bold; }
    .btn_white { background:#fff; color:#333; padding:9px 29px; border:1px solid #ddd; text-decoration:none; display:inline-block; font-weight:bold; }
</style>
@endsection
