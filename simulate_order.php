<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Order;

echo "=== Schema Check: fm_order.order_seq ===\n";
$cols = DB::select("DESCRIBE fm_order order_seq");
foreach($cols as $c) {
    echo "Type: " . $c->Type . " | Start: " . $c->Field . "\n";
}

echo "\n=== Simulating Order Save ===\n";

DB::beginTransaction();
try {
    // 1. Generate Seq
    $today = date('Y-m-d');
    $seqId = DB::table('fm_order_sequence')->insertGetId(['regist_date' => $today]);
    $orderSeq = date('YmdHis') . $seqId;
    
    echo "Generated ID: $orderSeq (Length: " . strlen($orderSeq) . ")\n";

    // 2. Create Order
    $order = new Order();
    $order->order_seq = $orderSeq;
    $order->order_user_name = "TestUser";
    $order->order_cellphone = "010-1234-5678";
    $order->order_email = "test@example.com";
    $order->recipient_user_name = "TestRecipient";
    $order->recipient_cellphone = "010-1234-5678";
    $order->recipient_address = "Test Address";
    $order->recipient_address_street = "Test Street";
    $order->recipient_address_detail = "101";
    $order->settleprice = 1000;
    $order->step = 15;
    $order->regist_date = now(); // Use Carbon
    
    // Required Legacy Fields (Mocking controller logic)
    $order->order_phone = "--";
    $order->recipient_phone = "--";
    $order->recipient_zipcode = "12345";
    $order->enuri = 0;
    $order->tax = 0;
    $order->shipping_cost = 0;
    $order->international = 'domestic';
    $order->international_cost = 0;
    $order->total_ea = 1;
    $order->total_type = 1;
    $order->mode = 'order';
    $order->sitetype = 'P';
    $order->skintype = 'P';
    $order->important = '0';
    $order->hidden = 'N';
    $order->ip = '127.0.0.1';

    $order->member_seq = 0;

    echo "Saving Order...\n";
    $order->save();
    echo "Order Saved.\n";

    DB::commit();
    echo "Transaction Committed.\n";
    
    // Verify Persistence
    $check = DB::table('fm_order')->where('order_seq', $orderSeq)->first();
    if ($check) {
        echo "VERIFIED: Order exists in DB.\n";
    } else {
        echo "CRITICAL: Order NOT FOUND after commit!\n";
    }

} catch (Exception $e) {
    DB::rollBack();
    echo "EXCEPTION caught: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
