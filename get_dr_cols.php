<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$schema = \Illuminate\Support\Facades\DB::select('DESCRIBE fm_member_dr');
$cols = [];
foreach($schema as $col) {
    $cols[] = $col->Field;
}
echo implode(',', $cols) . PHP_EOL;
