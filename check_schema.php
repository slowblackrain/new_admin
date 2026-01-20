<?php
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$columns = Schema::getColumnListing('fm_cart');
echo "fm_cart columns:\n" . implode(', ', $columns) . "\n\n";

$columns = Schema::getColumnListing('fm_cart_option');
echo "fm_cart_option columns:\n" . implode(', ', $columns) . "\n\n";

$columns = Schema::getColumnListing('fm_cart_input');
echo "fm_cart_input columns:\n" . implode(', ', $columns) . "\n\n";
