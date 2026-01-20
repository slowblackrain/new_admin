<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $cols = DB::select('DESCRIBE fm_member');
    echo "STRICT COLUMNS (Not Null, No Default):\n";
    foreach ($cols as $col) {
        if ($col->Null === 'NO' && $col->Default === null && $col->Extra !== 'auto_increment') {
            echo "- " . $col->Field . " (" . $col->Type . ")\n";
        }
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
