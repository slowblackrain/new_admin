<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// settleprice > 0 인 최근 일반 주문 조회
$pairingGoods = config('payment.pairing_goods', []);

$order = DB::table('fm_order as o')
    ->join('fm_order_item as i', 'o.order_seq', '=', 'i.order_seq')
    ->whereNotIn('i.goods_seq', $pairingGoods)
    ->where('o.settleprice', '>', 0)
    ->select('o.*')
    ->orderByDesc('o.regist_date')
    ->first();

if ($order) {
    echo "ORDER_SEQ: " . $order->order_seq . "\n";
    echo "SETTLEPRICE: " . $order->settleprice . "\n";
    echo "STEP: " . $order->step . "\n";
    echo "PAYMENT: " . $order->payment . "\n";
} else {
    echo "NO ORDER FOUND\n";
}
