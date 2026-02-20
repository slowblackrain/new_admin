<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$items = DB::table('fm_order_item_option')
    ->limit(10)
    ->orderBy('item_option_seq', 'desc')
    ->get();

foreach($items as $item) {
    if (!$item->goods_code) continue;

    $goodsPrice = DB::table('fm_goods_option')
        ->where('goods_seq', $item->goods_code)
        ->where('default_option', 'y')
        ->value('price');
        
    $member = DB::table('fm_order')
        ->join('fm_member', 'fm_order.member_seq', '=', 'fm_member.member_seq')
        ->where('fm_order.order_seq', $item->order_seq)
        ->select('fm_member.mtype', 'fm_member.group_seq')
        ->first();

    echo "Order: {$item->order_seq}, ItemPaid: {$item->price}, ItemOrg: {$item->org_price}, GoodsDbPrice: {$goodsPrice}, MType: " . ($member->mtype ?? 'none') . "\n";
}
