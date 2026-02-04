<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\DB;

$tables = ['fm_account_provider_ats', 'fm_category_link'];

foreach ($tables as $table) {
    echo "\n=== Table: $table ===\n";
    try {
        $columns = DB::getSchemaBuilder()->getColumnListing($table);
        print_r($columns);
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
