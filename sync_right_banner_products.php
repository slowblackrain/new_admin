<?php
// sync_right_banner_products.php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Goods;

// 1. Get candidate products (Active & Visible & Has Image)
$candidateIds = Goods::where('goods_view', 'look')
    ->where('goods_status', 'normal')
    ->whereHas('images')
    ->inRandomOrder()
    ->limit(20)
    ->pluck('goods_seq')
    ->toArray();

if (count($candidateIds) < 6) {
    die("Not enough active products found to populate banners.\n");
}

$products7160 = array_slice($candidateIds, 0, 3);
$products101810 = array_slice($candidateIds, 3, 3);

echo "Selected Products for 7160: " . implode(', ', $products7160) . "\n";
echo "Selected Products for 101810: " . implode(', ', $products101810) . "\n";

// 2. Update/Fix Display 7160 (Best Products)
// Set auto_use = 'n' to trigger manual logic
DB::table('fm_design_display_tab')
    ->where('display_seq', 7160)
    ->where('display_tab_index', 0)
    ->update([
        'auto_use' => 'n',
        'contents_type' => 'manual'
    ]);

// Clear existing items and insert new ones
DB::table('fm_design_display_tab_item')
    ->where('display_seq', 7160)
    ->where('display_tab_index', 0)
    ->delete();

foreach ($products7160 as $index => $goodsSeq) {
    DB::table('fm_design_display_tab_item')->insert([
        'display_tab_index' => 0,
        'display_seq' => 7160,
        'goods_seq' => $goodsSeq
    ]);
}
echo "Updated Display 7160 with manual products.\n";

// 3. Update/Fix Display 101810 (Recommended Roll)
// Check if tab exists, if not insert
$tab101810 = DB::table('fm_design_display_tab')
    ->where('display_seq', 101810)
    ->where('display_tab_index', 0)
    ->first();

if (!$tab101810) {
    DB::table('fm_design_display_tab')->insert([
        'display_seq' => 101810,
        'display_tab_index' => 0,
        'auto_use' => 'n', // Manual
        'auto_condition_use' => '0',
        'contents_type' => 'manual'
    ]);
    echo "Created missing tab for Display 101810.\n";
} else {
    DB::table('fm_design_display_tab')
        ->where('display_seq', 101810)
        ->where('display_tab_index', 0)
        ->update([
            'auto_use' => 'n',
            'contents_type' => 'manual'
        ]);
    echo "Updated existing tab for Display 101810.\n";
}

// Clear existing items and insert new ones
DB::table('fm_design_display_tab_item')
    ->where('display_seq', 101810)
    ->where('display_tab_index', 0)
    ->delete();

foreach ($products101810 as $index => $goodsSeq) {
    DB::table('fm_design_display_tab_item')->insert([
        'display_tab_index' => 0,
        'display_seq' => 101810,
        'goods_seq' => $goodsSeq
    ]);
}
echo "Updated Display 101810 with manual products.\n";
