<?php
use Illuminate\Contracts\Console\Kernel;
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$goodsSeq = 206718;
echo "Goods: $goodsSeq\n";

$options = App\Models\GoodsOption::where('goods_seq', $goodsSeq)->get();
foreach($options as $opt) {
    echo "Option Seq: " . $opt->option_seq . " | Name: " . $opt->option1 . "\n";
    $supply = DB::table('fm_goods_supply')->where('option_seq', $opt->option_seq)->first();
    echo "  -> Supply Stock: " . ($supply->stock ?? 'NULL') . " (SupplySeq: " . ($supply->supply_seq ?? 'NULL') . ")\n";
}

// Check Cart usage
$user = App\Models\Member::where('userid', 'testseller')->first();
$cartItems = App\Models\Cart::where('member_seq', $user->member_seq)->where('goods_seq', $goodsSeq)->get();
foreach($cartItems as $c) {
    if ($c->options->isEmpty()) { echo "Cart Item has no options relation loaded properly.\n"; continue; }
    $cOpt = $c->options->first();
    echo "Cart Option: " . $cOpt->option1 . "\n";
    
    // Manual Match
    $matched = $options->first(function($o) use ($cOpt){
        return (string)$o->option1 == (string)$cOpt->option1;
    });
    echo "Matched OptionSeq: " . ($matched ? $matched->option_seq : "NONE") . "\n";
}
