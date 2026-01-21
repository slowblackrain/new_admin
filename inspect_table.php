<?php
require 'C:/dometopia/new_admin/vendor/autoload.php';
$app = require_once 'C:/dometopia/new_admin/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Columns of fm_config:\n";
$cols = Illuminate\Support\Facades\DB::select('SHOW COLUMNS FROM fm_config');
foreach ($cols as $c) {
    echo $c->Field . "\n";
}

echo "\n--- Data Check ---\n";
// Check if field 'key' or 'value' exists?
// Or maybe 'name' and 'value'?
// Let's assume typical key-value or one row config.
// I'll fetch first 5 rows to see structure.
$rows = Illuminate\Support\Facades\DB::select('SELECT * FROM fm_config LIMIT 5');
print_r($rows);

// Search for '%key%' in data if possible
// Assuming columns might be 'key', 'val' or similar based on output.
// If output shows 'field' and 'value', I will adapt.
