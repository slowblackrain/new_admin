<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Schema:\n";
$schema = DB::select("SHOW CREATE TABLE fm_stock_history");
print_r($schema);

echo "\nLatest Data:\n";
$data = DB::table('fm_stock_history')->orderBy('regist_date', 'desc')->limit(5)->get();
print_r($data);
