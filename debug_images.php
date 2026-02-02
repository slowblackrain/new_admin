<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Goods;
use Illuminate\Support\Facades\DB;

// Find a product that has images
$productWithImage = DB::table('fm_goods_image')->select('goods_seq')->first();

if (!$productWithImage) {
    echo "No images found in fm_goods_image table.\n";
    exit;
}

$goodsSeq = $productWithImage->goods_seq;
echo "Inspecting Product ID: " . $goodsSeq . "\n";

$goods = Goods::with('images')->find($goodsSeq);

if ($goods) {
    echo "Product Name: " . $goods->goods_name . "\n";
    echo "Images Count: " . $goods->images->count() . "\n";
    foreach ($goods->images as $img) {
        echo " - Type: " . $img->image_type . ", Cut: " . $img->cut_number . ", Path: " . $img->image . "\n";
    }
} else {
    echo "Product not found.\n";
}
