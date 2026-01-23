<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Create a Persistent Test Order for Browser Verification
$testOrderSeq = 'BROWSER-TEST-' . date('Hi'); // Short enough? FM uses char(25) usually or similar.

try {
    // Check if exists
    $exists = DB::table('fm_order')->where('order_seq', $testOrderSeq)->exists();
    if ($exists) {
        DB::table('fm_order_item_option')->where('order_seq', $testOrderSeq)->delete();
        DB::table('fm_order_item')->where('order_seq', $testOrderSeq)->delete();
        DB::table('fm_order')->where('order_seq', $testOrderSeq)->delete();
    }

    // Insert FM_ORDER
    DB::table('fm_order')->insert([
        'order_seq' => $testOrderSeq,
        'step' => 15, // Order Received
        'order_user_name' => 'Browser Tester',
        'order_cellphone' => '010-9999-8888',
        'recipient_user_name' => 'Safe Tester',
        'recipient_address' => 'Teheran-ro, Seoul',
        'recipient_cellphone' => '010-7777-6666',
        'regist_date' => now(),
        'settleprice' => 50000,
        'payment' => 'bank',
        'bank_account' => 'SafeBank 123-456',
        'depositor' => 'Tester',
        'shipping_cost' => 0,
        'member_seq' => null
    ]);

    // Insert FM_ORDER_ITEM
    $itemSeq = DB::table('fm_order_item')->insertGetId([
        'order_seq' => $testOrderSeq,
        'goods_seq' => 1, 
        'goods_name' => 'Safe Test Product for Browser',
    ]);

    // Insert FM_ORDER_ITEM_OPTION
    DB::table('fm_order_item_option')->insert([
        'item_seq' => $itemSeq,
        'order_seq' => $testOrderSeq,
        'ea' => 1,
        'step' => 15,
        'price' => 50000,
        'supply_price' => 30000,
        'consumer_price' => 60000,
    ]);

    echo "CREATED_ORDER_SEQ={$testOrderSeq}";

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
