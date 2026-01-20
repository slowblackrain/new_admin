<?php
// debug_view_time.php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Manually fetch a product to test Controller logic
// We need to set up the app environment first
$app->boot();

use App\Models\Goods;
use App\Models\Category;

$start = microtime(true);

echo "Starting debug...\n";

// Find a valid goods_seq
$goods = Goods::active()->first();
if (!$goods) {
    die("No active goods found.\n");
}
$goodsSeq = $goods->goods_seq;
echo "Testing Goods Seq: $goodsSeq\n";

// Replicate Controller Logic
$product = Goods::active()->with(['option', 'images', 'inputs'])->where('goods_seq', $goodsSeq)->firstOrFail();

$firstOption = $product->option->first();
$priceInfo = [];
if ($firstOption) {
    $basePrice = $firstOption->price;
    $mtypeDiscount = $product->mtype_discount ?? 0;

    // Test potentially slow logic
    $priceInfo['ori_price'] = $basePrice;
    $priceInfo['price'] = $basePrice - $mtypeDiscount;
}

$contentArr = explode('|', $product->goods_contents2 ?? '');

// Categories
$categories = Category::whereRaw('length(category_code) = 4')->orderBy('position')->limit(20)->get();

$end = microtime(true);
$duration = $end - $start;

echo "Logic completed in " . number_format($duration, 4) . " seconds.\n";
echo "Product Name: " . $product->goods_name . "\n";
echo "Category Count: " . $categories->count() . "\n";
