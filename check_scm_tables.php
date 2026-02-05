<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;

$tables = DB::select('SHOW TABLES LIKE "fm_%"');
$found = [];
foreach ($tables as $t) {
    $val = array_values((array)$t)[0];
    if (strpos($val, 'offer') !== false || strpos($val, 'scm_order') !== false || strpos($val, 'autoorder') !== false) {
        $found[] = $val;
    }
}
$tables = DB::select('DESCRIBE fm_manager');
print_r($tables);
