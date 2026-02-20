<?php
use Illuminate\Contracts\Console\Kernel;
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$user = App\Models\Member::where('userid', 'testseller')->first();
if (!$user) { echo "User testseller not found\n"; exit; }
echo "User Seq: " . $user->member_seq . "\n";
echo "Cart Count: " . App\Models\Cart::where('member_seq', $user->member_seq)->count() . "\n";
$cartItems = App\Models\Cart::where('member_seq', $user->member_seq)->get();
foreach($cartItems as $item) {
    echo "  - Item: " . $item->goods_seq . " (Qty: " . ($item->options->first()->ea ?? 'aka') . ")\n";
}

// Last Order
$order = App\Models\Order::orderBy('order_seq', 'desc')->first();
echo "Last Order: " . ($order ? $order->order_seq . " (" . $order->regist_date . ")" : "None") . "\n";
