<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Goods;
use App\Models\GoodsOption;
use App\Services\PricingService;
use Illuminate\Support\Facades\Auth;

// Mock User class for testing
class MockUser extends \App\Models\Member {
    public $member_seq;
    public $mtype;
    public $group_seq;
}

// Helper to test
function testPricing($scenario, $user, $goodsId, $qty, $service) {
    echo "\n--- Scenario: $scenario ---\n";
    if ($user) {
        Auth::setUser($user);
        echo "User: [seq:{$user->member_seq}, mtype:{$user->mtype}, group:{$user->group_seq}]\n";
    } else {
        Auth::logout();
        echo "User: Guest\n";
    }

    $product = Goods::with('option')->find($goodsId);
    if (!$product) {
        echo "Product $goodsId not found.\n";
        return;
    }

    $option = $product->option->first();
    $priceInfo = $service->calculatePrice($product, $option, $qty);

    echo "Product: {$product->goods_name} (Price: {$product->price}, MtypeDisc: {$product->mtype_discount})\n";
    echo "Qty: $qty\n";
    echo "Calculated Unit Price: " . number_format($priceInfo['price']) . "\n";
    echo "Discount Type: " . $priceInfo['discount_type'] . "\n";
    
    // Validation Logic
    $isWholesale = ($priceInfo['price'] < $product->price);
    
    if (!$user && $isWholesale) {
        echo "Result: FAIL (Guest got wholesale price)\n";
    } elseif ($user && $user->mtype != 'business' && $priceInfo['total_price'] < 100000 && $isWholesale) {
        echo "Result: FAIL (Retail Member (<100k) got wholesale price)\n";
    } elseif ($user && $user->mtype == 'business' && !$isWholesale) {
        echo "Result: FAIL (Business Member did NOT get wholesale price)\n";
    } else {
        echo "Result: PASS (Maybe)\n";
    }
}

$service = new PricingService();

// Pick a product with mtype_discount > 0
$testGoodsId = \Illuminate\Support\Facades\DB::table('fm_goods')
    ->where('mtype_discount', '>', 100)
    ->value('goods_seq');

if (!$testGoodsId) die("No test product found.");

// 1. Guest Test
testPricing("Guest Purchase 1ea", null, $testGoodsId, 1, $service);

// 2. Retail Member Test (< 99k)
$retailUser = new MockUser();
$retailUser->member_seq = 999991;
$retailUser->mtype = 'member'; // or empty
$retailUser->group_seq = 1;
testPricing("Retail Member Purchase 1ea", $retailUser, $testGoodsId, 1, $service);

// 3. Business Member Test
$bizUser = new MockUser();
$bizUser->member_seq = 999992;
$bizUser->mtype = 'business';
$bizUser->group_seq = 1;
testPricing("Business Member Purchase 1ea", $bizUser, $testGoodsId, 1, $service);

// 4. Retail Member > 99k Rule Test
// Product Price is ~31,720. 4 quantity = ~126,880 (>99,999)
// Expect: Wholesale Price
testPricing("Retail Member Bulk Purchase (>99k)", $retailUser, $testGoodsId, 4, $service);

// 5. Volume Discount Test (Dynamic Threshold)
// Need to find a product with volume discount
$volGoodsId = \Illuminate\Support\Facades\DB::table('fm_goods')
    ->where('fifty_discount_ea', '>', 0)
    ->where('fifty_discount', '>', 0)
    ->limit(1)
    ->value('goods_seq');

if ($volGoodsId) {
    $volGoods = Goods::find($volGoodsId);
    $threshold = $volGoods->fifty_discount_ea;
    echo "\n--- Volume Discount Test (Threshold: $threshold) ---\n";
    testPricing("Volume Purchase ($threshold ea)", $retailUser, $volGoodsId, $threshold, $service);
}
