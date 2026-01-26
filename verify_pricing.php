<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Simulate Request Data
$data = [
    'goodsName' => 'Test Price Tier Product ' . time(),
    'goodsCode' => rand(100000000, 2000000000), // Fit in INT(10)
    'goodsView' => 'look',
    'goodsStatus' => 'normal',
    'optionUse' => '0',
    'fifty_discount' => 9500,
    'fifty_discount_ea' => 50,
    'hundred_discount' => 9000,
    'hundred_discount_ea' => 100,
    'consumerPrice' => [15000],
    'price' => [10000],
    'supplyPrice' => [8000],
    'stock' => [100],
];

echo "Simulating Store Request...\n";

// We can't easily dispatch a real Request object to Controller in CLI without Route setup, 
// so we will manually insert into DB to verify Schema mapping works as expected if we were using Models directly.
// OR we can use the Controller if we create a Request object.

// Let's test Model saving logic directly to verify DB columns accept these values.
try {
    Illuminate\Support\Facades\DB::beginTransaction();

    $goods = new App\Models\Goods();
    $goods->goods_name = $data['goodsName'];
    $goods->goods_code = $data['goodsCode'];
    
    // Legacy Tiered Pricing
    $goods->fifty_discount = $data['fifty_discount'];
    $goods->fifty_discount_ea = $data['fifty_discount_ea'];
    $goods->hundred_discount = $data['hundred_discount'];
    $goods->hundred_discount_ea = $data['hundred_discount_ea'];
    
    $goods->save();
    
    echo "Goods Saved. Pricing Tiers:\n";
    echo "50 Discount: " . $goods->fifty_discount . "\n";
    echo "100 Discount: " . $goods->hundred_discount . "\n";

    $option = new App\Models\GoodsOption();
    $option->goods_seq = $goods->goods_seq;
    $option->consumer_price = $data['consumerPrice'][0];
    $option->price = $data['price'][0];
    $option->provider_price = $data['supplyPrice'][0];
    $option->save();

    echo "Option Saved. Prices:\n";
    echo "Consumer: " . $option->consumer_price . "\n";
    echo "Wholesale: " . $option->price . "\n";
    echo "Provider: " . $option->provider_price . "\n";

    Illuminate\Support\Facades\DB::rollBack(); // Don't actually keep junk data
    echo "Rollback Success. Verification Complete.\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    Illuminate\Support\Facades\DB::rollBack();
}
