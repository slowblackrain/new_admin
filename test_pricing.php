<?php
use App\Models\Goods;
use App\Models\GoodsOption;
use App\Models\Member;
use Illuminate\Support\Facades\Auth;
use App\Services\PricingService;

$service = new PricingService();

// Mock a Product
$product = new Goods();
$product->goods_seq = 1000;
// price is legacy 'somae_price' (retail)
$product->price = 50000; 
$product->consumer_price = 60000;
// Wholesale discount amount (meaning domae_price = 50000 - 10000 = 40000)
$product->mtype_discount = 10000; 
// Tier 1 Volume
$product->fifty_discount_ea = 50;
$product->fifty_discount = 15000; // Price will be 35000
// Staff CBM Info
$product->goods_scode = 'GDF12345';
$product->multi_discount_cbm = '0|0|0|0|0|0|30000|0|0|0|0|0|0|0|0|0|2000'; // index 6 is 30000, 16 is 2000 => Staff price 32000

$option = new GoodsOption();
$option->price = 50000;
$option->consumer_price = 60000;

function runTest($name, $userCallback, $qty, $product, $option, $service) {
    $user = $userCallback();
    $member = new Member();
    $member->member_seq = $user->member_seq;
    $member->mtype = $user->mtype;
    $member->group_seq = $user->group_seq;
    Auth::setUser($member);

    $info = $service->calculatePrice($product, $option, $qty);
    echo str_pad($name, 45, ' ') . " | " . 
         str_pad($info['discount_type'], 20, ' ') . " | " . 
         "Unit: " . $info['unit_price'] . " | " . 
         "Total: " . $info['total_price'] . "\n";
    Auth::forgetUser();
}

echo "=== DOMETOPIA PRICING POLICY VERIFICATION ===\n";
echo "Base Retail: 50000 | Wholesale: 40000 (10k off) | 50+ Volume: 35000 (15k off) | Staff(GDF): 32000\n\n";

// Test 1: Normal Retail, 1 item (< 99k) => Retail
runTest("1. Retail < 99k (Qty 1)", function() {
    return (object)['member_seq'=>1, 'mtype'=>'retail', 'group_seq'=>1];
}, 1, $product, $option, $service);

// Test 2: Normal Retail, 2 items (= 100k) => Wholesale (99k Rule applied)
runTest("2. Retail >= 99k (Qty 2) [Auto-Wholesale]", function() {
    return (object)['member_seq'=>1, 'mtype'=>'retail', 'group_seq'=>1];
}, 2, $product, $option, $service);

// Test 3: Normal Retail, 1 item (> 99k base price) => Exception: No Wholesale
$expensiveProduct = clone $product;
$expensiveProduct->price = 120000;
$expensiveOption = clone $option;
$expensiveOption->price = 120000;
runTest("3. Retail 1 item > 99k [Exception: No Disc]", function() {
    return (object)['member_seq'=>1, 'mtype'=>'retail', 'group_seq'=>1];
}, 1, $expensiveProduct, $expensiveOption, $service);

// Test 4: Business Member, 1 item => Always Wholesale
runTest("4. Business/Dealer (Qty 1) [Wholesale]", function() {
    return (object)['member_seq'=>2, 'mtype'=>'business', 'group_seq'=>1];
}, 1, $product, $option, $service);

// Test 5: Any Member, 50+ items => Tier 1 Volume Discount
runTest("5. Volume Purchase 50+ [Tier 1]", function() {
    return (object)['member_seq'=>1, 'mtype'=>'retail', 'group_seq'=>1];
}, 50, $product, $option, $service);

// Test 6: Staff (Group 2) => Staff Pricing Logic (GDF)
runTest("6. Staff Member (Group 2) [Staff Pricing]", function() {
    return (object)['member_seq'=>3, 'mtype'=>'staff', 'group_seq'=>2];
}, 1, $product, $option, $service);

echo "\nVerification Complete!\n";
