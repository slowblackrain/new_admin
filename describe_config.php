<?php
use Illuminate\Support\Facades\DB;
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cols = DB::select("DESCRIBE fm_config");
foreach ($cols as $c) echo $c->Field . "\n";

echo "Checking fm_zone...\n";
if (Schema::hasTable('fm_zone')) {
    echo "fm_zone exists.\n";
} else {
    echo "fm_zone MISSING.\n";
}
