<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Simulate a POST request to order.store
$requestData = [
    'cart_seq' => [0], // Dummy cart seq
    'order_user_name' => '테스트주문자',
    'order_cellphone' => '010-1234-5678',
    'order_email' => 'test@example.com',
    'recipient_user_name' => '수령인',
    'recipient_cellphone' => '010-9876-5432',
    'recipient_zipcode' => '12345',
    'recipient_address' => '서울시 강남구',
    'payment' => 'bank',
    'typereceipt' => 1, // Tax Invoice
    'co_name' => '도매토피아테스트',
    'busi_no' => '123-45-67890',
    'co_ceo' => '홍길동',
    'co_status' => '도매',
    'co_type' => '소프트웨어',
    'tax_person' => '이순신',
    'tax_email' => 'tax@example.com',
    'bank_account' => '국민은행 123-456-7890 도매토피아',
    'depositor' => '테스트주문자'
];

$request = Illuminate\Http\Request::create('/order/store', 'POST', $requestData);

// To avoid actually placing a real order that requires cart items and pricing logic,
// let's just test the DB insert logic directly as it would run in the controller.

$orderSeq = date('ymdHi') . rand(1000, 9999);
$settleprice = 50000;

try {
    // 1. Create a dummy Order
    $order = new \App\Models\Order();
    $order->order_seq = $orderSeq;
    $order->order_user_name = $requestData['order_user_name'];
    $order->settleprice = $settleprice;
    $order->original_settleprice = $settleprice;
    $order->payment = 'bank';
    $order->regist_date = now();
    $order->typereceipt = $requestData['typereceipt'];
    $order->save();

    // 2. Run the exact insert block from the controller
    if ($order->typereceipt == 1) { // 세금계산서
        Illuminate\Support\Facades\DB::table('fm_sales')->insert([
            'typereceipt' => 1,
            'type' => 2, // 수동 신청
            'tstep' => 1, // 발급 신청 접수
            'order_seq' => $order->order_seq,
            'member_seq' => $order->member_seq ?? 0,
            'price' => $order->settleprice, 
            'supply' => round($order->settleprice / 1.1),
            'surtax' => $order->settleprice - round($order->settleprice / 1.1),
            'co_name' => $requestData['co_name'],
            'busi_no' => str_replace('-', '', $requestData['busi_no']),
            'co_ceo' => $requestData['co_ceo'],
            'co_status' => $requestData['co_status'],
            'co_type' => $requestData['co_type'],
            'person' => $requestData['tax_person'],
            'email' => $requestData['tax_email'],
            'regdate' => now(),
        ]);
        echo "Successfully inserted Tax Invoice for Order: " . $orderSeq . "\n";
    }

    // 3. Verify it was saved
    $salesRecord = Illuminate\Support\Facades\DB::table('fm_sales')
        ->where('order_seq', $orderSeq)
        ->first();
        
    print_r($salesRecord);

    // 4. Cleanup dummy data
    Illuminate\Support\Facades\DB::table('fm_order')->where('order_seq', $orderSeq)->delete();
    Illuminate\Support\Facades\DB::table('fm_sales')->where('order_seq', $orderSeq)->delete();
    echo "\nCleanup complete.";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
