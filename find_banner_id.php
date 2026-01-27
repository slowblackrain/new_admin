<?php
use Illuminate\Support\Facades\DB;
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$filenames = [
    'main_top_B1.jpg',
    'main_top_B2.jpg',
    'main_top_B3.jpg',
    'main_top_B4.jpg'
];

foreach ($filenames as $name) {
    $item = DB::table('fm_design_banner_item')->where('image', 'like', "%$name%")->first();
    if ($item) {
        echo "Found $name in banner_seq: {$item->banner_seq} (Item Seq: {$item->banner_item_seq})\n";
    } else {
        echo "Could not find $name in DB\n";
    }
}

// Also check for any banner with 4 items that looks like "Middle" or "Bottom"
$candidates = DB::table('fm_design_banner')
    ->whereIn('name', ['메인중간배너', '메인하단배너', 'Main Bottom', 'Main Middle'])
    ->orWhere('name', 'like', '%중간%')
    ->get();

foreach($candidates as $c) {
     echo "Candidate: [{$c->banner_seq}] {$c->name}\n";
}
