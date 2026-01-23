<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\DB;

$columns = DB::getSchemaBuilder()->getColumnListing('fm_order_item_option');
echo "Columns for fm_order_item_option:\n";
print_r($columns);

$columnsGoods = DB::getSchemaBuilder()->getColumnListing('fm_goods_option');
echo "Columns for fm_goods_option:\n";
print_r($columnsGoods);
