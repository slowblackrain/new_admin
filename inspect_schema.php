<?php
use Illuminate\Support\Facades\DB;
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$columns = DB::getSchemaBuilder()->getColumnListing('fm_design_banner');
print_r($columns);

// Also peek at first row to see what kind of data is there
$first = DB::table('fm_design_banner')->first();
print_r($first);
