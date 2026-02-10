<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Front\CartController;
use App\Http\Controllers\Front\OrderController;
use App\Models\Goods;

// 1. Login as Member (Reseller acting as Buyer)
$memberSeq = 346218;
$member = \App\Models\Member::find($memberSeq);
if (!$member) die("Member not found");
Auth::login($member);
echo "Logged in as Member: $memberSeq\n";

// 2. Setup Test Product (Reseller Stock - FFF)
// Using goods_seq from previous test or find one owned by 346218
$goodsSeq = 1000074; // Assumed from previous output
$goods = Goods::find($goodsSeq);

if (!$goods) {
    // Try to find any goods owned by this reseller
    $goods = Goods::where('provider_member_seq', $memberSeq)->first();
    if (!$goods) die("No Reseller Goods found. Run Phase 3 test first.");
}

echo "Using Reseller Goods: {$goods->goods_seq} ({$goods->goods_name})\n";

// Ensure it is marked as FFF (Stock) not GT (Dropship)
// We manually update scode for testing if needed, though copy logic should have handled it.
// Phase 3 copy logic sets 'GT...' prefix usually. 
// Wait, Implementation Plan said 'FFF' for stock. 
// Let's force it to 'FFF_TEST' to trigger the new logic.
DB::table('fm_goods')->where('goods_seq', $goods->goods_seq)->update([
    'goods_scode' => 'FFF_TEST_ITEM',
    'provider_member_seq' => $memberSeq // Ensure ownership
]);
echo "Forced goods_scode to FFF_TEST_ITEM for testing.\n";

// Ensure Stock
$option = DB::table('fm_goods_option')->where('goods_seq', $goods->goods_seq)->first();
$supply = DB::table('fm_goods_supply')->where('option_seq', $option->option_seq)->first();

if (!$supply) {
    DB::table('fm_goods_supply')->insert([
        'goods_seq' => $goods->goods_seq,
        'option_seq' => $option->option_seq,
        'stock' => 100,
        'badstock' => 0,
        'supply_price' => $option->provider_price,
        'total_stock' => 100,
        'suboption_seq' => 0,
        'ablestock15' => 100,
        'safe_stock' => 0,
        'total_supply_price' => 0,
        'total_badstock' => 0
    ]);
    echo "Inserted Missing Supply Record.\n";
} else {
    DB::table('fm_goods_supply')->where('goods_seq', $goods->goods_seq)->update([
        'stock' => 100,
        'badstock' => 0,
        'ablestock15' => 100
    ]);
    echo "Updated Supply Stock.\n";
}

// 3. Clear Cart
DB::table('fm_cart')->where('member_seq', $memberSeq)->delete();

// 4. Add to Cart
// Use standard CartController or direct DB insert
$cartController = app(CartController::class);
$req = new \Illuminate\Http\Request();
$req->merge([
    'goods_seq' => $goods->goods_seq,
    'option_seq' => $option->option_seq, 
    'ea' => 1
]);
// Use simple DB insert to avoid validation complexity
$cartSeq = DB::table('fm_cart')->insertGetId([
    'member_seq' => $memberSeq,
    'goods_seq' => $goods->goods_seq,
    // 'option_seq' => $option->option_seq, // Removed
    // 'ea' => 1, // Removed
    'regist_date' => now()
]);
// Cart Option
DB::table('fm_cart_option')->insert([
    'cart_seq' => $cartSeq,
    'ea' => 1,
    'title1' => 'Option1',
    'option1' => $option->option1
]);

echo "Added to Cart (Seq: $cartSeq)\n";

// 5. Place Order
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
    echo $e->getTraceAsString();
    exit;
}

// 6. Verify Settlement (Admin Logic + DB)
$lastOrder = DB::table('fm_order')->orderBy('order_seq', 'desc')->first();
echo "Order Created: " . $lastOrder->order_seq . "\n";

// Emulate Deposit Verification (Admin View relies on deposit_yn='y')
DB::table('fm_order')->where('order_seq', $lastOrder->order_seq)->update([
    'deposit_yn' => 'y',
    'deposit_date' => now(),
    'step' => 25 // Payment Confirmed
]);
// Insert item steps too for join query
DB::table('fm_order_item_option')->where('order_seq', $lastOrder->order_seq)->update([
    'step' => 25
]);

// 6.1 Check Ledger (fm_account_provider_ats)
$settlement = DB::table('fm_account_provider_ats')
    ->where('member_seq', $memberSeq)
    ->where('acc_date', date('Y-m'))
    ->first();

if ($settlement) {
    echo "Settlement Record Found!\n";
    echo "Sell Price: " . $settlement->sell_price . "\n";
    echo "Margin: " . $settlement->margin . "\n";
    echo "Offer Price: " . $settlement->offer_price . "\n";
    
    if ($settlement->margin == $settlement->sell_price) {
        echo "SUCCESS: Margin equals Sell Price (Stock Sales Verified).\n";
    } else {
        echo "FAILURE: Margin mismatch.\n";
    }
} else {
    echo "FAILURE: No Settlement Record found.\n";
}

// 6.2 Check Admin Logic (Query from ATSController)
$year = date('Y');
$month = date('m');
$adminStats = DB::table('fm_order as o')
    ->selectRaw('
        day(o.deposit_date) as day,
        sum(sio.price * (sio.ea - sio.refund_ea)) as settleprice_sum
    ')
    ->join('fm_order_item as si', 'o.order_seq', '=', 'si.order_seq')
    ->join('fm_order_item_option as sio', 'si.item_seq', '=', 'sio.item_seq')
    ->join('fm_goods as sg', 'si.goods_seq', '=', 'sg.goods_seq')
    ->where('o.deposit_yn', 'y')
    ->whereYear('o.deposit_date', $year)
    ->whereMonth('o.deposit_date', $month)
    ->where('sg.provider_member_seq', $memberSeq)
    ->where('o.order_seq', $lastOrder->order_seq) // Filter specifically for this order
    ->groupByRaw('day')
    ->first();

if ($adminStats) {
    echo "Admin View Verification: SUCCESS\n";
    echo "Admin Settle Price: " . $adminStats->settleprice_sum . "\n";
} else {
    echo "Admin View Verification: FAILURE (No data found with Admin Query)\n";
}
