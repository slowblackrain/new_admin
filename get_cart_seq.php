<?php
use Illuminate\Contracts\Console\Kernel;
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$user = App\Models\Member::where('userid', 'testseller')->first();
if (!$user) { echo "User testseller not found\n"; exit; }

$cartItems = App\Models\Cart::where('member_seq', $user->member_seq)->get();
foreach($cartItems as $item) {
    echo "CartSeq: " . $item->cart_seq . " | Goods: " . $item->goods_seq . "\n";
}
