<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$columns = Illuminate\Support\Facades\DB::select('DESCRIBE fm_config');
foreach ($columns as $col) {
    echo "{$col->Field} | {$col->Type} | {$col->Key} | {$col->Default} | {$col->Extra}\n";
}
echo "--- PG CONFIG ---\n";
// Adjust query based on fm_config schema. Usually it has 'config_value' or similar.
// I'll assume standard key-value structure first or list limits.
echo "--- BASIC CONFIG ---\n";
$results = Illuminate\Support\Facades\DB::select("SELECT * FROM fm_config WHERE groupcd = 'basic'");
foreach ($results as $row) {
    echo "Code: {$row->codecd}, Value: " . substr($row->value, 0, 100) . "\n";
}
echo "--- ORDER CONFIG ---\n";
$results = Illuminate\Support\Facades\DB::select("SELECT * FROM fm_config WHERE groupcd = 'order'");
foreach ($results as $row) {
    echo "Code: {$row->codecd}, Value: " . substr($row->value, 0, 100) . "\n";
}
foreach ($results as $row) {
    print_r($row);
}
//
foreach ($columns as $col) {
    echo "{$col->Field} | {$col->Type} | {$col->Key} | {$col->Default} | {$col->Extra}\n";
}
//
foreach ($columns as $col) {
    echo "{$col->Field} | {$col->Type} | {$col->Key} | {$col->Default} | {$col->Extra}\n";
}
//
foreach ($columns as $col) {
    echo "{$col->Field} | {$col->Type} | {$col->Key} | {$col->Default} | {$col->Extra}\n";
}

//
echo "\nIndexes:\n";
foreach ($indexes as $idx) {
    echo "{$idx->Key_name} | {$idx->Column_name} | Unique: " . ($idx->Non_unique ? 'No' : 'Yes') . "\n";
}
