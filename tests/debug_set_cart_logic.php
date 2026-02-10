<?php

use App\Models\Goods;
use App\Models\Cart;
use App\Models\CartOption;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Test Goods ID (Parent Set found earlier)
$goodsSeq = 96700; 

echo "Checking Goods: $goodsSeq\n";
$goods = Goods::with('option')->find($goodsSeq);

if (!$goods) {
    echo "Goods not found.\n";
    exit;
}

echo "Goods Name: " . $goods->goods_name . "\n";
echo "Option Count: " . $goods->option->count() . "\n";

if ($goods->option->isEmpty()) {
    echo "[ERROR] Goods has no options. CartController will likely fail.\n";
    exit;
}

$option = $goods->option->first();
echo "Using First Option Seq: " . $option->option_seq . "\n";

// Simulate Cart Interaction
// We can't easily call Controller->store() without a full Request mock including Session/Auth.
// But we can check the logic: 
// 1. Controller expects 'option_seq' array.
// 2. It checks DB::table('fm_goods_option')->where('option_seq', ...).
// 3. If found, it adds to Cart.

// Let's verify if the option is valid
$optCheck = DB::table('fm_goods_option')->where('option_seq', $option->option_seq)->first();
if ($optCheck) {
    echo "Option is valid in DB.\n";
    echo "Logic Test: PASSED. Controller should accept this.\n";
} else {
    echo "Logic Test: FAILED. Option not found in DB.\n";
}
