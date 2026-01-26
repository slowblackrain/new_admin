<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$goods = Illuminate\Support\Facades\DB::table('fm_goods')
    ->leftJoin('fm_goods_option', 'fm_goods.goods_seq', '=', 'fm_goods_option.goods_seq')
    ->where('fm_goods.fifty_discount_ea', '>', 0)
    ->select('fm_goods.goods_name', 'fm_goods_option.consumer_price', 'fm_goods_option.price', 'fm_goods.fifty_discount', 'fm_goods.fifty_discount_ea', 'fm_goods.hundred_discount', 'fm_goods.hundred_discount_ea')
    ->limit(5)
    ->get();

foreach($goods as $g) {
    echo "Name: " . mb_substr($g->goods_name, 0, 30) . "...\n";
    echo "  Consumer: " . number_format($g->consumer_price) . "\n";
    echo "  Wholesale: " . number_format($g->price) . "\n";
    
    $fiftyTotal = $g->price * $g->fifty_discount_ea;
    echo "  50 Tier: " . number_format($g->fifty_discount) . " won / " . $g->fifty_discount_ea . " ea\n";
    echo "     -> Total Value: " . number_format($fiftyTotal) . "\n";
    
    $hundredTotal = $g->price * $g->hundred_discount_ea;
    echo "  100 Tier: " . number_format($g->hundred_discount) . " won / " . $g->hundred_discount_ea . " ea\n";
    echo "     -> Total Value: " . number_format($hundredTotal) . "\n";
    echo "--------------------------------------------------\n";
}
