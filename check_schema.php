<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = DB::select('SHOW TABLES');
$tableNames = array_map(function($t) { return array_values((array)$t)[0]; }, $tables);

$targetTables = [];
foreach ($tableNames as $table) {
    if (strpos($table, 'fm_goods') !== false || strpos($table, 'fm_order') !== false || strpos($table, 'fm_scm') !== false || strpos($table, 'fm_purchase') !== false) {
        $targetTables[] = $table;
    }
}

$schema = [];
foreach ($targetTables as $table) {
    $columns = DB::select("SHOW COLUMNS FROM `$table`");
    $schema[$table] = [];
    foreach ($columns as $col) {
        $schema[$table][] = "{$col->Field} ({$col->Type})";
    }
}

file_put_contents(__DIR__.'/schema_output.json', json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
