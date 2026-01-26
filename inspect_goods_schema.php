<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$cols = DB::select("SHOW COLUMNS FROM fm_goods");
foreach($cols as $c) {
    if(in_array($c->Field, ['goods_seq', 'goods_code', 'goods_name', 'sale_price', 'supply_price', 'consumer_price', 'stock_mode', 'stock_num', 'runout_policy', 'display', 'goods_status', 'shipping_policy'])) {
        echo $c->Field . " | " . $c->Type . "\n";
    }
}
