<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;

$cols = DB::select('SHOW COLUMNS FROM fm_scm_stock_move');
foreach ($cols as $col) {
    echo "Field: {$col->Field}, Type: {$col->Type}\n";
}
