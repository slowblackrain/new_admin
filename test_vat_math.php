<?php
// test_vat_math.php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Cart;
use App\Models\GoodsOption;
use App\Services\PricingService;
use Illuminate\Support\Facades\Auth;

$memberSeq = 2824;
Auth::loginUsingId($memberSeq);

$cartItems = Cart::where('member_seq', $memberSeq)->with(['goods.option', 'options'])->get();
$pricingService = app()->make(PricingService::class);

$totalVat = 0;
$totalPrice = 0;

echo "--- CART CONTENTS ---\n";
foreach($cartItems as $cItem) {
    $goods = $cItem->goods;
    $cartOption = $cItem->options->first();
    $ea = $cartOption->ea ?? 1;
    
    // Find matching GoodsOption
    $matchedOption = null;
    if ($goods && $goods->option) {
        $matchedOption = $goods->option->first(function($o) use ($cartOption) {
             return (string)$o->option1 == (string)$cartOption->option1 &&
                    (string)$o->option2 == (string)$cartOption->option2 &&
                    (string)$o->option3 == (string)$cartOption->option3 &&
                    (string)$o->option4 == (string)$cartOption->option4 &&
                    (string)$o->option5 == (string)$cartOption->option5;
        });
    }
    $calcOption = $matchedOption ?? $goods->option->first();
    
    // Simulate real price calculation
    $pricingInfo = $pricingService->calculatePrice($goods, $calcOption, $ea);
    $priceUnitPrice = $pricingInfo['unit_price'];
    $price = $priceUnitPrice * $ea;
    
    $totalPrice += $price;

    if ($goods && $goods->tax === 'tax') {
        $vat = floor($price * 0.1);
        $totalVat += $vat;
        echo "[TAXABLE] {$goods->goods_name} : {$priceUnitPrice} x {$ea} = {$price} won -> VAT: {$vat} won\n";
    } else {
        echo "[EXEMPT ] {$goods->goods_name} : {$priceUnitPrice} x {$ea} = {$price} won -> VAT: 0 won\n";
    }
}

echo "---------------------\n";
echo "Total Goods Price: {$totalPrice} won\n";
echo "Calculated VAT: {$totalVat} won\n";
