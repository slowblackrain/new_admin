<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Checking Member Columns:\n";
$columns = Schema::getColumnListing('fm_member');
foreach ($columns as $col) {
    if (strpos($col, 'point') !== false || strpos($col, 'emoney') !== false || strpos($col, 'mileage') !== false) {
        echo "- {$col}\n";
    }
}

echo "\nChecking Coupon Tables:\n";
$tables = DB::select('SHOW TABLES');
foreach ($tables as $table) {
    $tableName = reset($table);
    if (strpos($tableName, 'coupon') !== false || strpos($tableName, 'point') !== false || strpos($tableName, 'emoney') !== false) {
        echo "- {$tableName}\n";
    }
}
