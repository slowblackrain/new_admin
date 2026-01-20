<?php
use Illuminate\Support\Facades\DB;
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking fm_goods columns related to delivery...\n";
$goodsCols = DB::getSchemaBuilder()->getColumnListing('fm_goods');
$deliveryCols = array_filter($goodsCols, function($c) {
    return strpos($c, 'delivery') !== false || strpos($c, 'shipping') !== false || strpos($c, 'cost') !== false;
});
print_r($deliveryCols);

echo "\nChecking fm_delivery_add_price (Island Sur-charge)...\n";
if (Schema::hasTable('fm_delivery_add_price')) {
    echo "Table exists. Count: " . DB::table('fm_delivery_add_price')->count() . "\n";
    print_r(DB::table('fm_delivery_add_price')->first());
} else {
    echo "fm_delivery_add_price Table NOT found.\n";
}

echo "\nChecking fm_zone (Alternative Island Sur-charge)...\n";
if (Schema::hasTable('fm_zone')) {
    echo "Table exists. Count: " . DB::table('fm_zone')->count() . "\n";
}
