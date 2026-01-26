<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = ['fm_order_return', 'fm_order_refund'];
foreach ($tables as $t) {
    echo "--- $t ---\n";
    print_r(DB::select("SHOW CREATE TABLE $t"));
    echo "\nSample:\n";
    print_r(DB::table($t)->limit(1)->get());
    echo "\n";
}
