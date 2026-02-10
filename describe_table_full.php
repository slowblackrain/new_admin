<?php
use Illuminate\Support\Facades\DB;
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

$table = $argv[1] ?? 'fm_goods'; // Default fallback
echo "Structure of: $table\n";
try {
    $columns = DB::select("DESCRIBE $table");
    foreach ($columns as $col) {
        echo "Field: {$col->Field}, Type: {$col->Type}, Null: {$col->Null}, Key: {$col->Key}, Default: {$col->Default}\n";
    }
} catch (\Exception $e) {
    echo "Error describing table $table: " . $e->getMessage() . "\n";
}
