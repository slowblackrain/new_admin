<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Schema:\n";
$schema = DB::select("SHOW CREATE TABLE fm_scm_stock_move_goods");
print_r($schema);

echo "\nLatest Data:\n";
$data = DB::table('fm_scm_stock_move_goods')->limit(5)->get();
print_r($data);
