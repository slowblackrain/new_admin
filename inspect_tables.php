<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Tables matching 'stock':\n";
print_r(DB::select("SHOW TABLES LIKE '%stock%'"));

echo "\nTables matching 'history':\n";
print_r(DB::select("SHOW TABLES LIKE '%history%'"));
