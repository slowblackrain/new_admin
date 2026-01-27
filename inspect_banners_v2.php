<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DesignBanner;
use Illuminate\Support\Facades\DB;

function inspectBanner($seq, $name) {
    echo "Inspecting Banner Group $seq ($name):\n";
    // Simulate Controller Logic
    $group = DesignBanner::where('banner_seq', $seq)
        ->orderBy('modtime', 'desc')
        ->first();

    if (!$group) {
        echo "  No DesignBanner found for seq $seq\n";
        return;
    }

    echo "  Selected Group Skin: {$group->skin}, Modtime: {$group->modtime}\n";

    $items = DB::table('fm_design_banner_item')
        ->where('banner_seq', $seq)
        ->where('skin', $group->skin)
        ->orderBy('sort_seq') // Assuming sort order
        ->get();

    echo "  Items Count: " . $items->count() . "\n";
    foreach ($items as $item) {
        echo "    Item Seq: {$item->banner_item_seq}, Title: {$item->banner_title ?? 'N/A'}, Image: {$item->image}, Sort: {$item->sort_seq}\n";
    }
    
    // Check for duplicates
    $images = $items->pluck('image')->toArray();
    $counts = array_count_values($images);
    $duplicates = array_filter($counts, function($count) { return $count > 1; });
    
    if (!empty($duplicates)) {
        echo "  !! DUPLICATES FOUND !!\n";
        print_r($duplicates);
    } else {
        echo "  No duplicates found in this skin.\n";
    }
}

inspectBanner(12, 'Left');
echo "\n";
inspectBanner(13, 'Right');
