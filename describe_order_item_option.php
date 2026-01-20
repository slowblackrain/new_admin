<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $columns = DB::select("DESCRIBE fm_order_item_option");
    foreach ($columns as $col) {
        echo $col->Field . " | " . $col->Type . " | " . $col->Null . " | " . $col->Key . " | " . $col->Default . "\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
