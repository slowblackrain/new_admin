<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "--- Duplicates Analysis ---\n";

// 1. Check fm_goods_image type='list1' duplicates
$imageDups = DB::table('fm_goods_image')
    ->select('goods_seq', DB::raw('count(*) as cnt'))
    ->where('image_type', 'list1')
    ->groupBy('goods_seq')
    ->having('cnt', '>', 1)
    ->orderBy('cnt', 'desc')
    ->limit(5)
    ->get();

echo "Goods with multiple 'list1' images:\n";
foreach ($imageDups as $row) {
    echo "Goods Seq: {$row->goods_seq} - Count: {$row->cnt}\n";
}
if ($imageDups->isEmpty()) echo "None found.\n";

// 2. Check fm_category_link link=1 duplicates
$catDups = DB::table('fm_category_link')
    ->select('goods_seq', DB::raw('count(*) as cnt'))
    ->where('link', 1)
    ->groupBy('goods_seq')
    ->having('cnt', '>', 1)
    ->orderBy('cnt', 'desc')
    ->limit(5)
    ->get();

echo "\nGoods with multiple 'link=1' categories:\n";
foreach ($catDups as $row) {
    echo "Goods Seq: {$row->goods_seq} - Count: {$row->cnt}\n";
}
if ($catDups->isEmpty()) echo "None found.\n";

// 3. Simulating the main query count
$query = DB::table('fm_goods as g')
    ->leftJoin('fm_goods_image as i', function($join) {
        $join->on('g.goods_seq', '=', 'i.goods_seq')->where('i.image_type', 'list1');
    })
    ->leftJoin('fm_category_link as cl', function($join) {
        $join->on('g.goods_seq', '=', 'cl.goods_seq')->where('cl.link', 1); 
    })
    ->count();

echo "\nTotal Row Count with Joins: " . number_format($query) . "\n";
