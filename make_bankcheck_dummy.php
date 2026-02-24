<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use App\Models\Mosms;
use Carbon\Carbon;
use Illuminate\Support\Str;

echo "--- Bank Check Dummy Data Generator ---\n";

// 레거시 스키마 제약조건 무시 (strict mode 해제)
\Illuminate\Support\Facades\DB::statement("SET SESSION sql_mode = ''");

// 1. 주문대기(step:15, 무통장결제) 상태인 가짜 주문 생성
$orderSeq = date('YmdHis').'BANK';
$settlePrice = 25000;

$order = new Order();
$order->order_seq = $orderSeq;
$order->regist_date = Carbon::now()->subHours(2); // 2시간 전 주문
$order->member_seq = 1;
$order->order_user_name = '홍길동';
$order->recipient_user_name = '홍길동';
$order->settleprice = $settlePrice;
$order->payment = 'bank';
$order->deposit_yn = 'n';
$order->step = 15; // 입금확인중
$order->bank_account = '국민은행 123-456-7890';
$order->depositor = '홍길동';
$order->pg = '';

// 필수 필드 기본값 (마이그레이션 스키마 안전 장치)
$order->order_email = 'test@example.com';
$order->order_cellphone = '010-1234-5678';
$order->recipient_cellphone = '010-1234-5678';
$order->recipient_zipcode = '12345';
$order->recipient_address = '서울시 강남구';
$order->recipient_address_detail = '테스트빌딩';

// 레거시 스키마 Non-Nullable 필드 대비
$order->tax = 0;
$order->shipping_cost = 0;
$order->typereceipt = 0;

$order->save();
echo "[Success] Dummy Order (step=15) Created. Order Seq: {$orderSeq}, Price: {$settlePrice}\n";

// 2. 금액 틀린 '미확인 입금자' 내역 추가 (금액 부족)
$sms1 = new Mosms();
$sms1->pg_status = 'R'; // 미매칭
$sms1->in_bank = '국민은행';
$sms1->in_price = 20000; // 5천원 금액 부족!
$sms1->in_name = '홍길동'; // 이름은 일치
$sms1->memo = '입금액 다름 SMS 수신';
$sms1->update_time = Carbon::now()->subMinutes(30);
$sms1->save();
echo "[Success] Dummy Mosms (Mismatch Price) Created. Idx: {$sms1->idx}\n";

// 3. 입금자명이 다른 '미확인 입금자' 내역 추가 (정상 금액)
$sms2 = new Mosms();
$sms2->pg_status = 'R'; // 미매칭
$sms2->in_bank = '농협은행';
$sms2->in_price = $settlePrice; // 25000원 금액은 일치!
$sms2->in_name = '김길동'; // 입금자명 오류! (홍길동이어야 함)
$sms2->memo = '타인명 입금 SMS 수신';
$sms2->update_time = Carbon::now()->subMinutes(10);
$sms2->save();
echo "[Success] Dummy Mosms (Mismatch Name) Created. Idx: {$sms2->idx}\n";

echo "--- Script Execution Complete ---\n";
