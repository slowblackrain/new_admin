<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$goodsSeq = 1000074;
$option = Illuminate\Support\Facades\DB::table('fm_goods_option')->where('goods_seq', $goodsSeq)->first();
echo "Option Seq: " . $option->option_seq . "\n";

$supply = Illuminate\Support\Facades\DB::table('fm_goods_supply')->where('option_seq', $option->option_seq)->first();
echo "Supply Stock: " . ($supply->stock ?? 'NULL') . "\n";
echo "Supply BadStock: " . ($supply->badstock ?? 'NULL') . "\n";

$supplies = Illuminate\Support\Facades\DB::table('fm_goods_supply')->where('goods_seq', $goodsSeq)->get();
echo "All Supplies for Goods:\n";
foreach ($supplies as $s) {
    echo "Seq: {$s->supply_seq}, Opt: {$s->option_seq}, Stock: {$s->stock}\n";
}
