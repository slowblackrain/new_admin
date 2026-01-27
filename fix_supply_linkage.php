<?php
use Illuminate\Support\Facades\DB;
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$optionSeq = 388112;
$goodsSeq = 211160;

echo "Checking Supply for Option Set: {$optionSeq}\n";

$supply = DB::table('fm_goods_supply')->where('option_seq', $optionSeq)->first();
if ($supply) {
    echo "[OK] Found supply for option_seq {$optionSeq}. Stock: {$supply->stock}\n";
} else {
    echo "[FAIL] No supply for option_seq {$optionSeq}.\n";
    
    // Check if supply exists for goods_seq with empty option
    $supplyGoods = DB::table('fm_goods_supply')->where('goods_seq', $goodsSeq)->where(function($q){
        $q->whereNull('option_seq')->orWhere('option_seq', 0)->orWhere('option_seq', '');
    })->first();
    
    if ($supplyGoods) {
        echo "[INFO] Found supply for goods_seq {$goodsSeq} with empty option_seq. Updating it...\n";
        DB::table('fm_goods_supply')->where('supply_seq', $supplyGoods->supply_seq)->update(['option_seq' => $optionSeq]);
        echo "[FIXED] Updated supply_seq {$supplyGoods->supply_seq} to match option_seq {$optionSeq}.\n";
    } else {
        echo "[ERROR] No supply record found to link.\n";
    }
}
