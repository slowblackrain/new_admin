<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$schema = \Illuminate\Support\Facades\DB::select("DESCRIBE fm_customer_memo");
foreach($schema as $col) {
    echo $col->Field . " - " . $col->Type . PHP_EOL;
}
