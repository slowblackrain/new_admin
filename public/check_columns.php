<?php
// Load Laravel logic to use DB facade
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Check fm_order columns
$order = \Illuminate\Support\Facades\DB::table('fm_order')->first();
$output = "fm_order columns:\n" . implode("\n", array_keys((array)$order)) . "\n\n";

file_put_contents('columns.txt', $output);
echo "Columns dumped to columns.txt";
