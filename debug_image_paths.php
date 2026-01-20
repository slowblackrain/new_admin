<?php
// debug_image_paths.php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());
$app->boot();

use App\Models\Goods;

$no = 5;
$product = Goods::with(['images'])->where('goods_seq', $no)->first();

if (!$product) {
    echo "Product $no not found.\n";
    exit;
}

echo "Product: " . $product->goods_name . "\n";
foreach ($product->images as $img) {
    echo "Filesystem Path stored in DB: " . $img->image . "\n";
    $url = "http://dometopia.com/data/goods/" . $img->image;
    echo "Testing URL: $url\n";

    // Check headers
    $headers = @get_headers($url);
    if ($headers) {
        echo "Headers: " . $headers[0] . "\n";
    } else {
        echo "Failed to fetch headers.\n";
    }
}
