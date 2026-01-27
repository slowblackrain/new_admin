<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DesignBanner;
use Illuminate\Support\Facades\DB;

echo "Inspecting Banner Group 12 (Left):\n";
$group12 = DesignBanner::where('banner_seq', 12)->orderBy('modtime', 'desc')->get();
foreach ($group12 as $g) {
    echo "Group ID: {$g->curr_group}, Modtime: {$g->modtime}, Skin: {$g->skin}\n";
    $items = DB::table('fm_design_banner_item')->where('banner_seq', 12)->where('app_group', $g->curr_group)->get();
    echo "  Items Count: " . $items->count() . "\n";
    foreach ($items as $item) {
        echo "    Item Seq: {$item->item_seq}, Title: {$item->banner_title}, Image: {$item->image}, Sort: {$item->sort_seq}\n";
    }
}

echo "\nInspecting Banner Group 13 (Right):\n";
$group13 = DesignBanner::where('banner_seq', 13)->orderBy('modtime', 'desc')->get();
foreach ($group13 as $g) {
    echo "Group ID: {$g->curr_group}, Modtime: {$g->modtime}, Skin: {$g->skin}\n";
    $items = DB::table('fm_design_banner_item')->where('banner_seq', 13)->where('app_group', $g->curr_group)->get();
    echo "  Items Count: " . $items->count() . "\n";
    foreach ($items as $item) {
        echo "    Item Seq: {$item->item_seq}, Title: {$item->banner_title}, Image: {$item->image}, Sort: {$item->sort_seq}\n";
    }
}
