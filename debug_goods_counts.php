<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Database Connection Info:\n";
$config = config('database.connections.mysql');
echo "Read Host: " . ($config['read']['host'] ?? 'N/A') . "\n";
echo "Write Host: " . ($config['write']['host'] ?? 'N/A') . "\n";
echo "Default DB (Read): " . ($config['read']['database'] ?? 'N/A') . "\n\n";

echo "--- Goods Count Analysis ---\n";

// 1. Total Raw Count
$total = DB::table('fm_goods')->count();
echo "Total Rows in fm_goods: " . number_format($total) . "\n";

// 2. Count by Provider Status (Legacy deletion/visibility ?)
$byProviderStatus = DB::table('fm_goods')
    ->select('provider_status', DB::raw('count(*) as count'))
    ->groupBy('provider_status')
    ->get();

echo "\nCounts by provider_status:\n";
foreach ($byProviderStatus as $row) {
    echo "Status {$row->provider_status}: " . number_format($row->count) . "\n";
}

// 3. Count by Goods Status
$byGoodsStatus = DB::table('fm_goods')
    ->select('goods_status', DB::raw('count(*) as count'))
    ->groupBy('goods_status')
    ->get();

echo "\nCounts by goods_status:\n";
foreach ($byGoodsStatus as $row) {
    echo "Status {$row->goods_status}: " . number_format($row->count) . "\n";
}

// 4. Count with Joins (Simulating Catalog Query without filters)
$joinedCount = DB::table('fm_goods as g')
    ->leftJoin('fm_goods_option as o', function($join) {
         $join->on('g.goods_seq', '=', 'o.goods_seq')->where('o.default_option', 'y');
    })
    ->count();

echo "\nTotal with Option Join (Left Join): " . number_format($joinedCount) . "\n";

