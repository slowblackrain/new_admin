<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "--- Export Query Duplicates Analysis ---\n";

// 1. Check fm_goods_option default='y' duplicates
$optDups = DB::table('fm_goods_option')
    ->select('goods_seq', DB::raw('count(*) as cnt'))
    ->where('default_option', 'y')
    ->groupBy('goods_seq')
    ->having('cnt', '>', 1)
    ->orderBy('cnt', 'desc')
    ->limit(5)
    ->get();

echo "Goods with multiple default options:\n";
foreach ($optDups as $row) {
    echo "Goods Seq: {$row->goods_seq} - Count: {$row->cnt}\n";
}
if ($optDups->isEmpty()) echo "None found.\n";

// 2. Check fm_goods_supply duplicates
$supplyDups = DB::table('fm_goods_supply')
    ->select('goods_seq', DB::raw('count(*) as cnt'))
    ->groupBy('goods_seq')
    ->having('cnt', '>', 1)
    ->orderBy('cnt', 'desc')
    ->limit(5)
    ->get();

echo "\nGoods with multiple supply rows:\n";
foreach ($supplyDups as $row) {
    echo "Goods Seq: {$row->goods_seq} - Count: {$row->cnt}\n";
}
if ($supplyDups->isEmpty()) echo "None found.\n";

// 3. Count Total Export Query Rows
$queryCount = DB::table('fm_goods as g')
    ->leftJoin('fm_goods_option as o', function($join) {
            $join->on('g.goods_seq', '=', 'o.goods_seq')->where('o.default_option', 'y');
    })
    ->leftJoin('fm_goods_supply as s', 'g.goods_seq', '=', 's.goods_seq')
    ->count();

echo "\nTotal Rows waiting for Export: " . number_format($queryCount) . "\n";
