<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Checking fm_goods columns:\n";
$cols = DB::select('DESCRIBE fm_goods');
foreach ($cols as $col) {
    echo " - " . $col->Field . "\n";
}
