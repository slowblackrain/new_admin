<?php
require 'C:/dometopia/new_admin/vendor/autoload.php';
$app = require_once 'C:/dometopia/new_admin/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = Illuminate\Support\Facades\DB::select('SHOW TABLES');
foreach ($tables as $table) {
    foreach ($table as $key => $value) {
        if (strpos($value, 'fm_config') !== false || strpos($value, 'fm_setting') !== false || strpos($value, 'fm_shop') !== false) {
            echo $value . "\n";
        }
    }
}
