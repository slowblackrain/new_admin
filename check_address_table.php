<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function checkTable($table) {
    if (Schema::hasTable($table)) {
        echo "Table '$table' EXISTS.\n";
        $cols = DB::select("DESCRIBE $table");
        foreach ($cols as $col) {
            echo " - " . $col->Field . " (" . $col->Type . ")\n";
        }
    } else {
        echo "Table '$table' does NOT exist.\n";
    }
    echo "\n";
}

checkTable('fm_member_delivery');
checkTable('fm_delivery_address');
checkTable('fm_shipping_address');
checkTable('fm_member_address');
