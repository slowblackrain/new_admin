<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Check Goods Count
$goodsCount = \Illuminate\Support\Facades\DB::table('fm_goods')->count();
echo "Goods Count: " . $goodsCount . "\n";

// Check Order Item Count
$itemCount = \Illuminate\Support\Facades\DB::table('fm_order_item')->count();
echo "Order Items Count: " . $itemCount . "\n";

// Check Order Item Option Count
$optionCount = \Illuminate\Support\Facades\DB::table('fm_order_item_option')->count();
echo "Order Item Options Count: " . $optionCount . "\n";

// Check a sample search
$search = \App\Models\Goods::where('goods_name', 'like', '%box%')->orWhere('goods_name', 'like', '%a%')->limit(5)->get();
echo "Search 'box' or 'a' result count: " . $search->count() . "\n";

if ($search->count() > 0) {
    echo "Sample Item: " . $search->first()->goods_name . "\n";
}
