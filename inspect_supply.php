<?php

use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$items = DB::table('fm_order_item_option')->limit(5)->get();

foreach ($items as $item) {
    echo "Item Option Seq: " . $item->option_seq . "\n";
    $supply = DB::table('fm_goods_supply')->where('option_seq', $item->option_seq)->first();
    if ($supply) {
        echo " - FOUND Supply! Stock: " . $supply->stock . "\n";
    } else {
        echo " - NO Supply found.\n";
    }
}
