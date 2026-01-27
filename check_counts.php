<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = [
    'fm_goods', 'fm_goods_option', 'fm_goods_image', 'fm_goods_supply',
    'fm_member', 'fm_member_group',
    'fm_order', 'fm_order_item', 'fm_order_item_option',
    'fm_manager', 'fm_log_manager',
    'fm_category_link',
    'fm_offer', 'fm_order_log', 'fm_cash'
];

$results = [];

foreach ($tables as $t) {
    try {
        $count = Illuminate\Support\Facades\DB::table($t)->count();
        $results[$t] = $count;
    } catch (\Exception $e) {
        $results[$t] = "MISSING";
    }
}

echo json_encode($results, JSON_PRETTY_PRINT);
