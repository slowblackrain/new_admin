<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== DESCRIBE fm_member_group ===\n";
$columns = DB::select("DESCRIBE fm_member_group");
foreach ($columns as $col) {
    echo $col->Field . " (" . $col->Type . ")\n";
}

echo "\n=== DESCRIBE fm_member_group_sale ===\n";
$columns = DB::select("DESCRIBE fm_member_group_sale");
foreach ($columns as $col) {
    echo $col->Field . " (" . $col->Type . ")\n";
}

echo "\n=== DESCRIBE fm_member_group_sale_detail ===\n";
$columns = DB::select("DESCRIBE fm_member_group_sale_detail");
foreach ($columns as $col) {
    echo $col->Field . " (" . $col->Type . ")\n";
}
