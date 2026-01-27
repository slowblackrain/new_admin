<?php
use Illuminate\Support\Facades\DB;
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$items = DB::table('fm_design_banner_item')->where('banner_seq', 12)->get();
echo "Banner 12 Items: " . $items->count() . "\n";
foreach($items as $i) {
    echo " - [Item {$i->banner_item_seq}] Img: {$i->image}, Link: {$i->link}, Skin: {$i->skin}\n";
}

$items13 = DB::table('fm_design_banner_item')->where('banner_seq', 13)->get();
echo "Banner 13 Items: " . $items13->count() . "\n";
foreach($items13 as $i) {
    echo " - [Item {$i->banner_item_seq}] Img: {$i->image}, Link: {$i->link}, Skin: {$i->skin}\n";
}
