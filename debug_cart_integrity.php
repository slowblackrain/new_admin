<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

echo "--- fm_cart Schema ---\n";
$cols = DB::select("DESCRIBE fm_cart");
foreach ($cols as $c) {
    echo "{$c->Field} ({$c->Type})\n";
}

echo "\n--- Latest Cart Item Analysis ---\n";
$latest = DB::table('fm_cart')->orderBy('cart_seq', 'desc')->first();
if ($latest) {
    echo "Latest Cart Seq: {$latest->cart_seq}\n";
    echo "Goods Seq: {$latest->goods_seq}\n";
    
    $goods = DB::table('fm_goods')->where('goods_seq', $latest->goods_seq)->first();
    if ($goods) {
        echo "Goods Found: {$goods->goods_name} (Status: {$goods->goods_status}, View: {$goods->goods_view})\n";
    } else {
        echo "Goods NOT FOUND!\n";
    }
} else {
    echo "No Cart Items Found.\n";
}

echo "\n--- Scope Test Simulation ---\n";
// Simulate what the controller does, passing explicit ID/Session matching the latest row
if ($latest) {
    $countMember = Cart::where('member_seq', $latest->member_seq)->count();
    echo "Query by Member ({$latest->member_seq}): Found $countMember items\n";
    
    $countSession = Cart::where('session_id', $latest->session_id)->count();
    echo "Query by Session ({$latest->session_id}): Found $countSession items\n";
}
