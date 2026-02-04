<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "fm_goods_option:\n";
print_r(Illuminate\Support\Facades\DB::getSchemaBuilder()->getColumnListing('fm_goods_option'));

echo "\nfm_goods_supply:\n";
print_r(Illuminate\Support\Facades\DB::getSchemaBuilder()->getColumnListing('fm_goods_supply'));
