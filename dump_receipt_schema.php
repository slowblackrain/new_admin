<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables1 = Illuminate\Support\Facades\DB::select("SHOW TABLES LIKE '%tax%'");
$tables2 = Illuminate\Support\Facades\DB::select("SHOW TABLES LIKE '%receipt%'");
print_r($tables1);
print_r($tables2);
