<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = ['fm_goods_option'];
foreach ($tables as $t) {
    echo "--- $t ---\n";
    $schema = DB::select("SHOW CREATE TABLE $t");
    print_r($schema);
    
    echo "\nSample Data:\n";
    $data = DB::table($t)->limit(3)->get();
    print_r($data);
    echo "\n";
}
