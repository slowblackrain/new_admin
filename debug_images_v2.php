<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Goods;
use Illuminate\Support\Facades\DB;

// 1. Count Total Products
echo "Total Goods: " . Goods::count() . "\n";

// 2. Count Total Images
echo "Total Images: " . DB::table('fm_goods_image')->count() . "\n";

// 3. Find a goods_seq that exists in both
$validSeq = DB::table('fm_goods')
    ->join('fm_goods_image', 'fm_goods.goods_seq', '=', 'fm_goods_image.goods_seq')
    ->select('fm_goods.goods_seq')
    ->first();

if ($validSeq) {
    echo "Found Valid Goods Seq with Image: " . $validSeq->goods_seq . "\n";
    $goods = Goods::with('images')->find($validSeq->goods_seq);
    if ($goods) {
        echo "Product: " . $goods->goods_name . "\n";
        foreach ($goods->images as $img) {
             echo " - Type: '" . $img->image_type . "' (len:".strlen($img->image_type)."), Path: " . $img->image . "\n";
        }
    }
} else {
    echo "No overlapping goods found between fm_goods and fm_goods_image.\n";
    // List some goods_seq from images table
    $imgSeqs = DB::table('fm_goods_image')->limit(5)->pluck('goods_seq');
    echo "Sample IDs from fm_goods_image: " . $imgSeqs->implode(', ') . "\n";
}
