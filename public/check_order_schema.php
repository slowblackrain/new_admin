<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "Checking fm_order_item columns...\n";
$columnsLink = Illuminate\Support\Facades\Schema::getColumnListing('fm_order_item');
foreach ($columnsLink as $col) {
    if (strpos($col, 'shipping') !== false) {
        echo " - $col\n";
    }
}

echo "Checking fm_order_item_option columns...\n";
$columnsOpt = Illuminate\Support\Facades\Schema::getColumnListing('fm_order_item_option');
foreach ($columnsOpt as $col) {
    if (strpos($col, 'shipping') !== false) {
        echo " - $col\n";
    }
}
