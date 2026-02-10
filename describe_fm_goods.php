<?php
use Illuminate\Support\Facades\DB;
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

$table = 'fm_goods';
echo "Table: $table\n";
$columns = DB::getSchemaBuilder()->getColumnListing($table);
print_r($columns);
echo "\n";
