<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "fm_uris_tax columns:\n";
print_r(Illuminate\Support\Facades\Schema::getColumnListing('fm_uris_tax'));

echo "\nfm_kr_receipt columns:\n";
print_r(Illuminate\Support\Facades\Schema::getColumnListing('fm_kr_receipt'));
