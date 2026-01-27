<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "Columns in fm_design_banner:\n";
print_r(Schema::getColumnListing('fm_design_banner'));

echo "\nColumns in fm_design_banner_item:\n";
print_r(Schema::getColumnListing('fm_design_banner_item'));

echo "\nSample rows from fm_design_banner (Banner 12):\n";
$rows = DB::table('fm_design_banner')->where('banner_seq', 12)->get();
foreach ($rows as $r) {
    print_r($r);
}

echo "\nSample rows from fm_design_banner_item (Banner 12):\n";
$items = DB::table('fm_design_banner_item')->where('banner_seq', 12)->limit(5)->get();
foreach ($items as $i) {
    print_r($i);
}
