<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Front\OrderController;
use App\Services\Agency\AgencySettlementService;

// Setup
$request = Illuminate\Http\Request::capture();
$app->instance('request', $request);
Illuminate\Support\Facades\Facade::clearResolvedInstance('request');

$memberSeq = 346218; // Reseller
$user = DB::table('fm_member')->where('member_seq', $memberSeq)->first();
Auth::loginUsingId($memberSeq);
echo "Logged in as Member: {$memberSeq}\n";

// 1. Prepare Product (FFF)
$goodsSeq = 1000074;
$goods = DB::table('fm_goods')->where('goods_seq', $goodsSeq)->first();

// Ensure Supply
$option = DB::table('fm_goods_option')->where('goods_seq', $goodsSeq)->first();
$supply = DB::table('fm_goods_supply')->where('option_seq', $option->option_seq)->first();
if (!$supply) {
    DB::table('fm_goods_supply')->insert([
        'goods_seq' => $goodsSeq,
        'option_seq' => $option->option_seq,
        'stock' => 100,
        'badstock' => 0,
        'supply_price' => $option->provider_price,
        'total_stock' => 100,
        'suboption_seq' => 0,
        'ablestock15' => 100
    ]);
} else {
    DB::table('fm_goods_supply')->where('option_seq', $option->option_seq)->update(['stock' => 100]);
}

// 2. Add to Cart
DB::table('fm_cart')->where('member_seq', $memberSeq)->delete();
$cartSeq = DB::table('fm_cart')->insertGetId([
    'goods_seq' => $goodsSeq,
    'member_seq' => $memberSeq,
    'session_id' => 'test_session',
    'distribution' => 'cart',
    'regist_date' => now(),
    'update_date' => now(),
    'ip' => '127.0.0.1'
]);
DB::table('fm_cart_option')->insert([
    'cart_seq' => $cartSeq,
    'ea' => 1,
    'option1' => $option->option1,
    'shipping_method' => 'prepay' // Standard
]);

// 3. Create Order
$orderController = app(OrderController::class);
$orderReq = new \Illuminate\Http\Request();
$orderReq->setMethod('POST');
$orderReq->merge([
    'cart_seq' => [$cartSeq],
    'payment' => 'bank',
    'depositor' => 'Tester',
    'order_user_name' => 'Tester',
    'order_cellphone' => '010-1234-5678',
    'order_email' => 'test@test.com',
    'recipient_user_name' => 'Tester',
    'recipient_cellphone' => '010-1234-5678',
    'recipient_zipcode' => '12345',
    'recipient_address' => 'Test Address',
    'recipient_address_street' => 'Test Street',
    'recipient_address_detail' => 'Test Detail',
    'memo' => 'Test Memo'
]);

echo "Submitting Order...\n";
try {
    $response = $orderController->store($orderReq);
    echo "Order Response Status: " . $response->getStatusCode() . "\n";
} catch (\Exception $e) {
    echo "Order Failed: " . $e->getMessage() . "\n";
    exit;
}

$lastOrder = DB::table('fm_order')->orderBy('order_seq', 'desc')->first();
echo "Order Created: " . $lastOrder->order_seq . "\n";

// 4. Verify Settlement (Initial)
$settlement = DB::table('fm_account_provider_ats')
    ->where('member_seq', $memberSeq)
    ->where('acc_date', date('Y-m'))
    ->first();
    
echo "Initial Settlement - SellPrice: {$settlement->sell_price}, Margin: {$settlement->margin}\n";

// 5. Simulate Refund
echo "Simulating Refund...\n";
// Update Order Status to Refund Request -> Complete (Skipping steps for E2E speed)
// In reality, RefundController would handle this. We will call service directly here to verify logic.
$service = app(AgencySettlementService::class);
$refundPrice = $lastOrder->settleprice; 

// Call Refund Logic
$service->refundStockSales($memberSeq, date('Y-m'), $refundPrice);

// 6. Verify Refund Rollback
$settlementAfter = DB::table('fm_account_provider_ats')
    ->where('member_seq', $memberSeq)
    ->where('acc_date', date('Y-m'))
    ->first();

echo "After Refund - SellPrice: {$settlementAfter->sell_price}, Margin: {$settlementAfter->margin}\n";

if ($settlement->sell_price - $refundPrice == $settlementAfter->sell_price) {
    echo "SUCCESS: Refund Rollback Verified.\n";
} else {
    echo "FAILURE: Refund Rollback Failed.\n";
}
