<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $tables = DB::select("SHOW TABLES LIKE '%cart%'");
    print_r($tables);
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
