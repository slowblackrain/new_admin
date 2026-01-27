<?php
use Illuminate\Support\Facades\DB;
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$goodsSeq = 211160;
echo "Checking Options for Goods: {$goodsSeq}\n";

$options = DB::table('fm_goods_option')->where('goods_seq', $goodsSeq)->get();

if ($options->isNotEmpty()) {
    foreach ($options as $opt) {
        echo "Option Seq: {$opt->option_seq} | Title: {$opt->option_title} | Price: {$opt->price}\n";
    }
} else {
    echo "No options found. It might be a single-option product.\n";
}

// Update Stock to 100
DB::table('fm_goods_supply')->where('goods_seq', $goodsSeq)->update(['stock' => 100]);
echo "Stock updated to 100 for Goods: {$goodsSeq}\n";
