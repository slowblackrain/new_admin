@extends('layouts.front')

@section('content')
    <div class="location_wrap">
        <div class="location_cont">
            <em><a href="/" class="local_home">HOME</a> &gt; 주문 완료</em>
        </div>
    </div>

    <div class="content_wrap">
        <div class="cart_title_area">
            <h3>주문 완료</h3>
        </div>

        <div style="padding: 100px 0; text-align: center;">
            <div style="margin-bottom: 30px;">
                <img src="/images/icon_check.png" alt="완료"
                    style="width: 60px; height: 60px; display: inline-block; background: #ccc; border-radius: 50%;">
                <!-- 임시 아이콘 -->
            </div>

            <h2 style="font-size: 24px; font-weight: bold; margin-bottom: 20px;">주문이 정상적으로 접수되었습니다.</h2>
            <p style="font-size: 16px; color: #666; margin-bottom: 40px;">
                주문번호: <strong style="color: #d00;">{{ $order->order_seq }}</strong>
            </p>

            <div style="width: 600px; margin: 0 auto; border: 1px solid #ddd; padding: 30px; text-align: left;">
                <h4
                    style="font-size: 18px; font-weight: bold; border-bottom: 1px solid #333; padding-bottom: 10px; margin-bottom: 20px;">
                    결제 정보 확인</h4>

                <table style="width: 100%; border-collapse: collapse;">
                    <colgroup>
                        <col width="120" />
                        <col width="*" />
                    </colgroup>
                    <tbody>
                        <tr>
                            <th style="padding: 10px 0; color: #666;">결제 금액</th>
                            <td style="padding: 10px 0; text-align: right; font-weight: bold; color: #333;">
                                {{ number_format($order->settleprice) }}원
                            </td>
                        </tr>
                        <tr>
                            <th style="padding: 10px 0; color: #666;">결제 수단</th>
                            <td style="padding: 10px 0; text-align: right; color: #333;">
                                @if($order->payment == 'bank')
                                    무통장 입금
                                @else
                                    신용카드
                                @endif
                            </td>
                        </tr>
                        @if($order->payment == 'bank')
                            <tr>
                                <th style="padding: 10px 0; color: #666;">입금 계좌</th>
                                <td style="padding: 10px 0; text-align: right; color: #333;">{{ $order->bank_account }}</td>
                            </tr>
                            <tr>
                                <th style="padding: 10px 0; color: #666;">입금자명</th>
                                <td style="padding: 10px 0; text-align: right; color: #333;">{{ $order->depositor }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="btn_area_center" style="margin-top: 50px;">
                <a href="{{ route('home') }}" class="btn_cancel" style="background: #333;">홈으로</a>
                <!-- 추후 마이페이지 주문내역 링크로 수정 예정 -->
                <a href="{{ route('mypage.order.list') }}" class="btn_order_all" style="margin-left: 10px;">주문내역 확인</a>
            </div>
        </div>
    </div>

    <style>
        .btn_area_center {
            margin: 40px 0;
            text-align: center;
        }

        .btn_order_all {
            padding: 15px 50px;
            background: #d00;
            color: #fff;
            font-size: 18px;
            font-weight: bold;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .btn_cancel {
            padding: 15px 50px;
            background: #666;
            color: #fff;
            font-size: 18px;
            font-weight: bold;
            text-decoration: none;
            margin-left: 10px;
            display: inline-block;
        }
    </style>
@endsection