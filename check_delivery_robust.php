<?php
use Illuminate\Support\Facades\DB;
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "1. Checking fm_goods columns (Raw SQL)...\n";
$columns = DB::select("DESCRIBE fm_goods");
foreach ($columns as $col) {
    if (strpos($col->Field, 'delivery') !== false || strpos($col->Field, 'shipping') !== false || strpos($col->Field, 'cost') !== false) {
        echo " - " . $col->Field . " (" . $col->Type . ")\n";
    }
}

echo "\n2. Checking fm_delivery_add_price...\n";
try {
    $rows = DB::select("SELECT * FROM fm_delivery_add_price LIMIT 5");
    if (count($rows) > 0) {
        echo "Found entries in fm_delivery_add_price:\n";
        print_r($rows[0]);
    } else {
        echo "Table exists but is empty.\n";
    }
} catch (\Exception $e) {
    echo "Table fm_delivery_add_price likely does not exist or error: " . $e->getMessage() . "\n";
}

echo "\n3. Checking fm_provider_shipping again for 'add' costs...\n";
$shippings = DB::select("SELECT * FROM fm_provider_shipping");
foreach ($shippings as $s) {
    echo "Provider Seq: " . $s->provider_seq . "\n";
    echo " - delivery_type: " . $s->delivery_type . "\n";
    echo " - add_delivery_cost: " . ($s->add_delivery_cost ?? 'NULL') . "\n"; // Check this specifically
}
