<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$order = \Illuminate\Support\Facades\DB::table('fm_order_item_option')->first();
$columns = array_keys((array)$order);
file_put_contents('order_columns.txt', 'fm_order_item_option columns:' . "\n" . implode("\n", $columns));
echo "Dumped fm_order_item_option to order_columns.txt";
