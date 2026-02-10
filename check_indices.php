<?php
use Illuminate\Support\Facades\DB;
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

$table = $argv[1] ?? 'fm_goods';
echo "Indices for table: $table\n";
$indices = DB::select("SHOW INDEXES FROM $table");
foreach ($indices as $index) {
    echo "Key: {$index->Key_name}, Column: {$index->Column_name}, Non_unique: {$index->Non_unique}\n";
}
