<?php
// add_to_cart_for_vat_test.php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Cart;
use Illuminate\Support\Facades\DB;
use App\Models\GoodsOption;

$memberSeq = 2824; // User 'shw1'

// Clear existing cart for clean test
$cartSeqs = Cart::where('member_seq', $memberSeq)->pluck('cart_seq');
if($cartSeqs->isNotEmpty()) {
    DB::table('fm_cart_option')->whereIn('cart_seq', $cartSeqs)->delete();
    Cart::where('member_seq', $memberSeq)->delete();
}

$taxExemptOption = GoodsOption::whereHas('goods', function($q){
    $q->where('tax', 'exempt')->active();
})->where('default_option', 'y')->first();

$taxableOption = GoodsOption::whereHas('goods', function($q){
    $q->where('tax', 'tax')->active();
})->where('default_option', 'y')->first();

if(!$taxExemptOption || !$taxableOption) {
    die("Could not find suitable items for test.\n");
}

$taxExemptGoodsSeq = $taxExemptOption->goods_seq;
$taxableGoodsSeq = $taxableOption->goods_seq;

$sessionId = "test_php_script_" . time();

// Add Tax Exempt Item
$cart1 = Cart::create([
    'member_seq' => $memberSeq,
    'goods_seq' => $taxExemptGoodsSeq,
    'session_id' => $sessionId,
    'ip' => '127.0.0.1',
    'regist_date' => now(),
    'update_date' => now()
]);
DB::table('fm_cart_option')->insert([
    'cart_seq' => $cart1->cart_seq,
    'option1' => $taxExemptOption->option1 ?? '',
    'option2' => $taxExemptOption->option2 ?? '',
    'option3' => $taxExemptOption->option3 ?? '',
    'option4' => $taxExemptOption->option4 ?? '',
    'option5' => $taxExemptOption->option5 ?? '',
    'ea' => 1,
    'shipping_method' => 'default'
]);

// Add Taxable Item
$cart2 = Cart::create([
    'member_seq' => $memberSeq,
    'goods_seq' => $taxableGoodsSeq,
    'session_id' => $sessionId,
    'ip' => '127.0.0.1',
    'regist_date' => now(),
    'update_date' => now()
]);
DB::table('fm_cart_option')->insert([
    'cart_seq' => $cart2->cart_seq,
    'option1' => $taxableOption->option1 ?? '',
    'option2' => $taxableOption->option2 ?? '',
    'option3' => $taxableOption->option3 ?? '',
    'option4' => $taxableOption->option4 ?? '',
    'option5' => $taxableOption->option5 ?? '',
    'ea' => 2,
    'shipping_method' => 'default'
]);
echo "Cart setup complete for Member {$memberSeq}!\n";
