<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = ['fm_scm_trader_account', 'fm_offer_calculate'];

foreach ($tables as $table) {
    echo "--- $table ---\n";
    print_r(DB::select("SHOW CREATE TABLE $table"));
    echo "\n";
}
