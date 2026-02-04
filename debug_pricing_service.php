<?php
use App\Models\Goods;
use App\Services\PricingService;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

$goodsSeq = 210770;
$product = Goods::with(['option'])->where('goods_seq', $goodsSeq)->first();

if (!$product) {
    echo "Product not found\n";
    exit;
}

echo "Product Price Attribute: " . ($product->price ?? 'null') . "\n";
echo "Option Count: " . $product->option->count() . "\n";

if ($product->option->count() > 0) {
    echo "First Option Price: " . $product->option->first()->price . "\n";
} else {
    echo "No options found\n";
}

$service = new PricingService();
$priceInfo = $service->getProductPricingInfo($product);

echo "Service Result:\n";
print_r($priceInfo);
