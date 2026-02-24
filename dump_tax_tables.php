<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$result = Illuminate\Support\Facades\DB::select("
    SELECT table_name, column_name 
    FROM information_schema.columns 
    WHERE table_schema = 'dometopia_new' 
    AND column_name IN ('tax_person', 'co_name', 'co_status', 'co_type', 'cuse', 'cash_receipt_number')
");

print_r($result);
