<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "fm_point columns:\n";
print_r(Illuminate\Support\Facades\Schema::getColumnListing('fm_point'));

echo "\nfm_emoney columns:\n";
print_r(Illuminate\Support\Facades\Schema::getColumnListing('fm_emoney'));
