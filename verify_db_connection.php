<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Testing Default Connection (Read)...\n";
    $goods = \Illuminate\Support\Facades\DB::table('fm_goods')->first();
    echo "Goods found: " . ($goods ? $goods->goods_name : 'None') . "\n";
    echo "Connection successful!\n";
} catch (\Exception $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
