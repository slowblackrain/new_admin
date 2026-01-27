<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DesignBanner;
use Illuminate\Support\Facades\DB;

echo "Inspecting Banner Group 12 (Left):\n";
$group12 = DesignBanner::where('banner_seq', 12)->orderBy('modtime', 'desc')->first();

if ($group12) {
    echo "Found Group 12: Skin={$group12->skin}, Modtime={$group12->modtime}\n";
    
    // Fetch items using the logic in HomeController
    $items = DB::table('fm_design_banner_item')
        ->where('banner_seq', 12)
        ->where('skin', $group12->skin)
        ->get();
        
    echo "Items Count: " . $items->count() . "\n";
    foreach ($items as $item) {
        echo "  Item Seq: {$item->banner_item_seq}, Image: {$item->image}, Link: {$item->link}\n";
    }

    // Check if there are multiple groups for banner_seq 12
    $allGroups = DesignBanner::where('banner_seq', 12)->get();
    echo "\nTotal Groups for Banner 12: " . $allGroups->count() . "\n";
    foreach ($allGroups as $g) {
        echo "  Skin: {$g->skin}, Modtime: {$g->modtime}\n";
    }

} else {
    echo "No Group 12 found.\n";
}

echo "\n------------------------------------------------\n";

echo "Inspecting Banner Group 1 (Main Visual):\n";
$group1 = DesignBanner::whereIn('banner_seq', [1, 11])->orderBy('modtime', 'desc')->first();
if ($group1) {
    echo "Found Group {$group1->banner_seq}: Skin={$group1->skin}, Modtime={$group1->modtime}\n";
    $items = DB::table('fm_design_banner_item')
        ->where('banner_seq', $group1->banner_seq)
        ->where('skin', $group1->skin)
        ->get();
    echo "Items Count: " . $items->count() . "\n";
     foreach ($items as $item) {
        echo "  Item Seq: {$item->banner_item_seq}, Image: {$item->image}\n";
    }
} else {
    echo "No Group 1 or 11 found.\n";
}
