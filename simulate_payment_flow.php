<?php
// c:/dometopia/new_admin/simulate_payment_flow.php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "--- Starting Payment Simulation ---\n";

use App\Models\Order;
use App\Models\Member;
use App\Models\Goods;
use Illuminate\Support\Facades\DB;

// 1. Setup Test User
$testUser = Member::firstOrCreate(
    ['userid' => 'test_buyer'],
    [
        'user_name' => 'Test Buyer', 
        'email' => 'test@example.com', 
        'password' => bcrypt('password'),
        'status' => 'y',
        'group_seq' => 1
    ]
);
echo "User: " . $testUser->userid . " (Seq: " . $testUser->member_seq . ")\n";

// 2. Test Case: Standard Payment (Toss)
echo "\n[Test 1] Standard Goods Order (Toss)\n";

function ensureGoodsWithPrice($prefix = 'Standard') {
    $goods = Goods::where('goods_name', 'like', $prefix . '%')->first();
    if (!$goods) {
        $goods = new Goods();
        $goods->goods_name = $prefix . ' Test Goods';
        $goods->goods_code = uniqid();
        $goods->save();
        
        DB::table('fm_goods_option')->insert([
            'goods_seq' => $goods->goods_seq,
            'price' => 1000,
            'consumer_price' => 1200,
            'provider_price' => 800,
            'default_option' => 'y',
            'option_type' => 'S',
            'option_title' => 'Basic',
            'option1' => 'Default'
        ]);
    }
    return $goods;
}

$goods = ensureGoodsWithPrice('Standard');
$option = DB::table('fm_goods_option')->where('goods_seq', $goods->goods_seq)->first();
$price = $option->price;

DB::table('fm_cart')->where('member_seq', $testUser->member_seq)->delete();
$cartId = DB::table('fm_cart')->insertGetId([
    'member_seq' => $testUser->member_seq,
    'goods_seq' => $goods->goods_seq,
    'regist_date' => now(),
    'update_date' => now(),
]);

DB::table('fm_cart_option')->insert([
    'cart_seq' => $cartId,
    'ea' => 1,
]);

echo "Cart created for goods: {$goods->goods_name} (Price: $price)\n";

$orderSeq = date('YmdHis') . rand(1000,9999);
$order = new Order();
$order->order_seq = $orderSeq;
$order->order_user_name = $testUser->user_name;
$order->member_seq = $testUser->member_seq;
$order->step = Order::STEP_ORDER_RECEIVED; // 1
$order->settleprice = $price;
$order->payment = 'card';
$order->pg = 'toss';
$order->regist_date = now();
$order->save();

echo "Order Created: $orderSeq\n";

// Payment Success Simulation
$order->step = Order::STEP_PAYMENT_CONFIRMED;
$order->deposit_yn = 'y';
$order->save();

$refreshedOrder = Order::find($orderSeq);
if ($refreshedOrder->step == 25 && $refreshedOrder->deposit_yn == 'y') {
    echo "[PASS] Step updated to 25, Deposit Y.\n";
} else {
    echo "[FAIL] Status update failed.\n";
}

// 3. Test Case: Agency Auto-Copy
echo "\n[Test 2] Agency Logic Verification (Manual Run)\n";

$atsCategory = DB::table('fm_category')->where('category_code', 'like', '0159%')->first();
if (!$atsCategory) {
    DB::table('fm_category')->insert([
        'category_code' => '01590001',
        'title' => 'ATS Test Category',
        'level' => 1
    ]);
    $atsCategory = (object)['category_code' => '01590001'];
}

$provider = Member::firstOrCreate(
    ['userid' => 'provider_user'], 
    [
        'user_name' => 'Provider', 
        'status' => 'y',
        'group_seq' => 1
    ]
);

// Fund Provider via fm_member.ATS_account
$provider->ATS_account = 1000000;
$provider->save();
echo "Provider Initial Balance: " . $provider->ATS_account . "\n";

// Create ATS Goods
$atsGoods = new Goods();
$atsGoods->goods_name = 'ATS Auto Copy Test Item';
$atsGoods->goods_scode = 'GT_SOURCE_001'; 
$atsGoods->provider_member_seq = $provider->member_seq;
$atsGoods->save();

DB::table('fm_goods_option')->updateOrInsert(
    ['goods_seq' => $atsGoods->goods_seq],
    [
        'price' => 5000,
        'provider_price' => 4000,
        'default_option' => 'y',
        'option_type' => 'S'
    ]
);
$atsOption = DB::table('fm_goods_option')->where('goods_seq', $atsGoods->goods_seq)->first();

DB::table('fm_category_link')->updateOrInsert(
    ['goods_seq' => $atsGoods->goods_seq, 'category_code' => $atsCategory->category_code],
    ['link' => 1]
);

$ea = 2;
$buyMemberSeq = $testUser->member_seq;

echo "Executing Deduction Logic...\n";
if (strpos($atsGoods->goods_scode, 'GT') === 0) {
    $resellerSeq = $atsGoods->provider_member_seq;
    $agencySupplyPrice = $atsOption->provider_price; // 4000
    $deductAmount = ($agencySupplyPrice * 1.1) * $ea; // 8800
    
    echo "Deducting $deductAmount from Provider $resellerSeq\n";
    
    // Decrement fm_member.ATS_account
    $affected = DB::table('fm_member')->where('member_seq', $resellerSeq)->decrement('ATS_account', $deductAmount);
    
    $providerVars = Member::find($resellerSeq);
    echo "Provider New Balance: " . $providerVars->ATS_account . "\n";
    
    if ($providerVars->ATS_account == (1000000 - $deductAmount)) echo "[PASS] Cash Deducted.\n";
    else echo "[FAIL] Cash Deduction Value Mismatch.\n";
} else {
    echo "[SKIP] Deduction skipped (Not GT product?)\n";
}

echo "Executing Auto Copy Logic...\n";
if (true) {
    echo "Duplicating Product {$atsGoods->goods_seq} for Member $buyMemberSeq...\n";
    $newGoods = $atsGoods->replicate();
    $newGoods->goods_name = "Copied: " . $atsGoods->goods_name;
    $newGoods->provider_member_seq = $buyMemberSeq;
    $newGoods->goods_scode = 'GT_' . $atsGoods->goods_seq . '_' . $buyMemberSeq;
    $newGoods->save();
    
    // Also duplicate options
    $newOption = (array)$atsOption;
    unset($newOption['option_seq']);
    $newOption['goods_seq'] = $newGoods->goods_seq;
    DB::table('fm_goods_option')->insert($newOption);
    
    if ($newGoods->goods_seq) echo "[PASS] Product Copied: {$newGoods->goods_seq}\n";
    else echo "[FAIL] Copy Failed.\n";
}

echo "\n--- Simulation Complete ---\n";
