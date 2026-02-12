<?php
// sync_legacy_goods.php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Goods;
use Illuminate\Support\Facades\File;

// Target Goods Data from Live Site
$goodsData = [
    // Top Right (Best) - Images Only
    159989 => [
        'name' => 'Legacy Best Item 159989',
        'price' => 10000,
        'img_url' => 'https://dmtusr.vipweb.kr/goods_img/1/2022/11/159989/2_8222large.jpg'
    ],
    175547 => [
        'name' => 'Legacy Best Item 175547',
        'price' => 12000,
        'img_url' => 'https://dmtusr.vipweb.kr/goods_img/1/2023/05/175547/_883large.jpg'
    ],
    176671 => [
        'name' => 'Legacy Best Item 176671',
        'price' => 15000,
        'goods_scode' => 'GKM00003', // Placeholder
        'img_url' => 'https://dmtusr.vipweb.kr/goods_img/1/2023/05/1351669/large.jpg' // specific url
    ],
    // 2. Right Bottom "Recommended" (Rolling) - Display 101810
    130286 => [
        'name' => '[초록자연] 샤인머스켓 2kg(4수)',
        'price' => 13310,
        'goods_scode' => 'GKM21081', // Model Number
        'img_url' => 'https://dmtusr.vipweb.kr/goods_img/1/2021/07/130286/13-Ph_19thumbScroll.jpg'
    ],
    133545 => [
        'name' => '[초록자연] 신고배 선물세트 7.5kg (8-10과) (명품)',
        'price' => 61250,
        'goods_scode' => 'GKM32744', // Model Number
        'img_url' => 'https://dmtusr.vipweb.kr/goods_img/1/2021/09/133545/8-4_64thumbScroll.jpg'
    ],
    114383 => [
        'name' => '[초록자연] 완숙 토마토 5kg (1번)',
        'price' => 23310,
        'goods_scode' => 'GKM49294', // Model Number
        'img_url' => 'https://dmtusr.vipweb.kr/goods_img/1/2021/02/114383/0_29thumbScroll.jpg'
    ]
];

// Ensure directory exists
$localImgDir = public_path('images/legacy_synced');
if (!File::exists($localImgDir)) {
    File::makeDirectory($localImgDir, 0755, true);
}

foreach ($goodsData as $id => $data) {
    echo "Processing Goods ID: $id\n";

    // 1. Download Image
    $imgFileName = $id . '.jpg';
    $localPath = $localImgDir . '/' . $imgFileName;
    $publicPath = 'images/legacy_synced/' . $imgFileName;

    // Only download if not exists (or force? let's download to be safe)
    try {
        $imageContent = file_get_contents($data['img_url']);
        if ($imageContent !== false) {
            file_put_contents($localPath, $imageContent);
            echo "  - Image downloaded to $localPath\n";
        } else {
            echo "  - Failed to download image from {$data['img_url']}\n";
        }
    } catch (\Exception $e) {
        echo "  - Error downloading image: " . $e->getMessage() . "\n";
    }

    // 2. Insert/Update fm_goods
    $exists = DB::table('fm_goods')->where('goods_seq', $id)->exists();

    $goodsFields = [
        'goods_name' => $data['name'],
        'goods_status' => 'normal',
        'goods_view' => 'look',
        'goods_scode' => $data['goods_scode'] ?? null, // Insert goods_scode
        'update_date' => now()
    ];

    if ($exists) {
        DB::table('fm_goods')->where('goods_seq', $id)->update($goodsFields);
        echo "  - Updated fm_goods record.\n";
    } else {
        $goodsFields['goods_seq'] = $id;
        $goodsFields['regist_date'] = now();
        $goodsFields['provider_seq'] = 1; 
        
        DB::table('fm_goods')->insert($goodsFields);
        echo "  - Inserted new fm_goods record.\n";
    }

    // 2.5 Update/Insert fm_goods_option for Price
    $optionExists = DB::table('fm_goods_option')
        ->where('goods_seq', $id)
        ->where('default_option', 'y')
        ->exists();

    $optionFields = [
        'price' => $data['price'],
        'consumer_price' => $data['price'] * 1.2, 
        'default_option' => 'y',
        'option_type' => 'S'
    ];

    if ($optionExists) {
        DB::table('fm_goods_option')
            ->where('goods_seq', $id)
            ->where('default_option', 'y')
            ->update(['price' => $data['price'], 'consumer_price' => $data['price'] * 1.2]);
        echo "  - Updated fm_goods_option price.\n";
    } else {
        $optionFields['goods_seq'] = $id;
        DB::table('fm_goods_option')->insert($optionFields);
        echo "  - Inserted fm_goods_option record.\n";
    }

    // 2.6 Insert/Update fm_goods_image
    // Check if image exists for this type
    // We will use 'list1', 'view', 'main', 'magnify' types to cover all bases
    $types = ['list1', 'view', 'main', 'magnify'];
    
    // Clear existing images to avoid duplicates or simplify update
    DB::table('fm_goods_image')->where('goods_seq', $id)->delete();
    
    foreach ($types as $type) {
        DB::table('fm_goods_image')->insert([
            'goods_seq' => $id,
            'image_type' => $type,
            'image' => $publicPath, // Use relative path
            'cut_number' => 1,
            'match_color' => '',
            'label' => ''
        ]);
    }
    echo "  - Updated fm_goods_image records.\n";
}

// 3. Update Displays
// Display 7160 (Best)
echo "Updating Display 7160 (Best)...\n";
DB::table('fm_design_display_tab')->where('display_seq', 7160)->update(['auto_use' => 'n', 'contents_type' => 'manual']);
DB::table('fm_design_display_tab_item')->where('display_seq', 7160)->delete();

$bestIds = [159989, 175547, 176671];
foreach ($bestIds as $goodsSeq) {
    DB::table('fm_design_display_tab_item')->insert([
        'display_tab_index' => 0,
        'display_seq' => 7160,
        'goods_seq' => $goodsSeq
    ]);
}

// Display 101810 (Recommended)
echo "Updating Display 101810 (Recommended)...\n";
DB::table('fm_design_display_tab')->where('display_seq', 101810)->update(['auto_use' => 'n', 'contents_type' => 'manual']);
DB::table('fm_design_display_tab_item')->where('display_seq', 101810)->delete();

// 3 items for Rolling: 130286, 133545, 114383
$rollingIds = [130286, 133545, 114383];
foreach ($rollingIds as $goodsSeq) {
    DB::table('fm_design_display_tab_item')->insert([
        'display_tab_index' => 0,
        'display_seq' => 101810,
        'goods_seq' => $goodsSeq
    ]);
}

echo "Done.\n";
