<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\LegacyGoodsHelper;
use Illuminate\Support\Facades\DB;

// Get a goods seq
$goods = DB::table('fm_goods')->orderBy('goods_seq', 'desc')->first();
$goodsSeq = $goods->goods_seq;

echo "Testing LegacyGoodsHelper for Goods: $goodsSeq\n";

try {
    $offerInfo = LegacyGoodsHelper::getOfferInfoHtml($goodsSeq);
    echo "Offer Info HTML length: " . strlen($offerInfo) . "\n";
    // echo "Offer Info Preview: " . substr($offerInfo, 0, 100) . "...\n";

    $priceInfo = LegacyGoodsHelper::getDiscountPriceHtml($goodsSeq);
    echo "Price Info HTML length: " . strlen($priceInfo) . "\n";
    // echo "Price Info Preview: " . substr($priceInfo, 0, 100) . "...\n";
    
    echo "Success!\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
