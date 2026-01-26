<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

print_r(DB::select("SHOW CREATE TABLE fm_scm_stock_revision"));
print_r(DB::select("SHOW CREATE TABLE fm_scm_stock_revision_goods"));
