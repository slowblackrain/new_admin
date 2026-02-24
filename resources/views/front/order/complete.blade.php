@extends('layouts.front')

@section('content')
<div class="location_wrap hidden-mobile">
    <div class="location_cont">
        <em><a href="/" class="local_home">HOME</a> &gt; 주문 완료</em>
    </div>
</div>

<div class="content_wrap">
    <div class="cart_title_area hidden-mobile">
        <h3>주문 완료</h3>
    </div>

    <!-- UI Polished Complete Section -->
    <div class="complete_area">
        <!-- Success Icon -->
        <div class="complete_icon_wrap">
            <i class="far fa-check-circle success_icon"></i>
        </div>

        <h2 class="complete_title">주문이 정상적으로 접수되었습니다.</h2>
        <p class="complete_orderno">
            주문번호 <strong class="orderno_highlight">{{ $order->order_seq }}</strong>
        </p>

        <!-- Order Detail Card -->
        <div class="complete_card shadow-sm">
            <h4 class="card_header">결제 정보 확인</h4>
            
            <div class="card_body">
                <div class="info_row">
                    <span class="info_label">결제 금액</span>
                    <span class="info_value price">{{ number_format($order->settleprice) }}원</span>
                </div>
                
                <div class="info_row">
                    <span class="info_label">결제 수단</span>
                    <span class="info_value">
                        @if($order->payment == 'bank')무통장 입금
                        @elseif($order->payment == 'card')신용카드
                        @else{{ $order->payment }}
                        @endif
                    </span>
                </div>
                
                @if($order->payment == 'bank')
                <div class="info_row bank_info_row bg-light">
                    <span class="info_label">입금 계좌</span>
                    <span class="info_value fw-bold text-dark account_num">{{ $order->bank_account }}</span>
                </div>
                <div class="info_row bank_info_row bg-light">
                    <span class="info_label">입금자명</span>
                    <span class="info_value text-dark">{{ $order->depositor }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="btn_area_complete">
            <a href="{{ route('mypage.order.list') }}" class="btn_base btn_red btn_large">주문내역 확인</a>
            <a href="{{ route('home') }}" class="btn_base btn_line btn_large">쇼핑 계속하기</a>
        </div>
    </div>
</div>

<style>
/* Scoped Styles for Complete Page */
.complete_area {
    padding: 60px 20px 80px;
    text-align: center;
    max-width: 600px;
    margin: 0 auto;
}

.complete_icon_wrap {
    margin-bottom: 25px;
}

.success_icon {
    font-size: 70px;
    color: #ff5722; /* Dometopia Primary */
    animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
}

@keyframes popIn {
    0% { transform: scale(0); opacity: 0; }
    80% { transform: scale(1.1); opacity: 1; }
    100% { transform: scale(1); opacity: 1; }
}

.complete_title {
    font-size: 26px;
    font-weight: 700;
    color: #333;
    margin-bottom: 15px;
    letter-spacing: -0.5px;
}

.complete_orderno {
    font-size: 16px;
    color: #666;
    margin-bottom: 35px;
}

.orderno_highlight {
    color: #ff5722;
    font-size: 18px;
    margin-left: 5px;
    background: #ffeee8;
    padding: 2px 8px;
    border-radius: 4px;
}

/* Card UI */
.complete_card {
    background: #fff;
    border: 1px solid #eaeaea;
    border-radius: 12px;
    overflow: hidden;
    text-align: left;
    margin-bottom: 40px;
}

.shadow-sm {
    box-shadow: 0 4px 15px rgba(0,0,0,0.03);
}

.card_header {
    background: #fcfcfc;
    padding: 18px 25px;
    font-size: 18px;
    font-weight: 700;
    color: #111;
    border-bottom: 1px solid #eaeaea;
    margin: 0;
}

.card_body {
    padding: 10px 25px;
}

.info_row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 0;
    border-bottom: 1px dashed #f0f0f0;
}

.info_row:last-child {
    border-bottom: none;
}

.info_label {
    color: #777;
    font-size: 15px;
}

.info_value {
    color: #333;
    font-size: 15px;
    font-weight: 500;
}

.info_value.price {
    font-size: 20px;
    font-weight: 700;
    color: #111;
}

.bank_info_row {
    margin: 10px -25px 0 -25px;
    padding: 15px 25px;
}

.bg-light {
    background-color: #f8f9fa;
    border-top: 1px solid #f0f0f0;
    border-bottom: none;
}

.fw-bold { font-weight: 700 !important; }
.text-dark { color: #111 !important; }
.account_num { letter-spacing: 0.5px; }

/* Buttons */
.btn_area_complete {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.btn_large {
    display: block;
    width: 100%;
    padding: 16px 0;
    font-size: 16px;
    font-weight: 700;
    border-radius: 8px;
    text-align: center;
    box-sizing: border-box;
    transition: all 0.2s;
}

.btn_red {
    background: #ff5722;
    color: #fff;
    border: 1px solid #ff5722;
}

.btn_red:hover {
    background: #e64a19;
    color: #fff;
}

.btn_line {
    background: #fff;
    color: #333;
    border: 1px solid #ddd;
}

.btn_line:hover {
    background: #f9f9f9;
}

/* Responsive */
@media (min-width: 768px) {
    .btn_area_complete {
        flex-direction: row;
        justify-content: center;
        gap: 10px;
    }
    
    .btn_large {
        width: 200px;
    }
    
    .complete_area {
        padding: 80px 20px 100px;
    }
}
</style>
@endsection