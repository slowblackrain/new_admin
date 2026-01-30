<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$prices = DB::table('fm_goods_option')
    ->whereIn('goods_seq', [930, 1898])
    ->get(['goods_seq', 'price', 'consumer_price']);

echo "Checking Prices:\n";
foreach ($prices as $p) {
    echo "ID: " . $p->goods_seq . " | Price: " . $p->price . " | Consumer: " . $p->consumer_price . "\n";
}
