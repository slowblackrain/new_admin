<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "Starting DB Migration for Printing Options...\n";

// 1. Revert fm_goods_input Schema
echo "1. Reverting fm_goods_input schema...\n";
try {
    DB::statement("ALTER TABLE fm_goods_input MODIFY COLUMN input_form ENUM('text','edit','file') NOT NULL DEFAULT 'text'");
    // Note: Reverting input_limit to tinyint might truncate data if we don't clean it first, 
    // but we are about to delete the data for 206718 anyway. 
    // However, to be safe, we will clean data first then alter.
} catch (\Exception $e) {
    echo "Warning: Could not revert input_form immediately (possibly due to existing data): " . $e->getMessage() . "\n";
}

// 2. Clean Data for 206718
echo "2. Cleaning input data for goods_seq 206718...\n";
DB::table('fm_goods_input')->where('goods_seq', 206718)->delete();
echo "   Deleted input data.\n";

// Retry Schema Revert if needed (specifically input_limit which was changed to TEXT)
try {
    DB::statement("ALTER TABLE fm_goods_input MODIFY COLUMN input_limit TINYINT(3) UNSIGNED NOT NULL DEFAULT 0");
    echo "   Reverted input_limit to TINYINT.\n";
} catch (\Exception $e) {
    echo "Warning: Could not revert input_limit: " . $e->getMessage() . "\n";
}

// 3. Clean Suboption Data for 206718 (to avoid duplicates)
echo "3. Cleaning existing suboption data for goods_seq 206718...\n";
DB::table('fm_goods_suboption')->where('goods_seq', 206718)->delete();

// 4. Insert Suboption Data
echo "4. Inserting Suboption data for goods_seq 206718...\n";

$subOptions = [
    [
        'suboption_title' => '인쇄',
        'suboption' => '중국1도',
        'suboption_code' => '', 
        'consumer_price' => 0,
        'price' => 80, // +80 won
        'reserve' => 0,
        'commission_rate' => 0,
        'goods_seq' => 206718,
        'sub_required' => 'n', // Usually optional or checked via JS
        'suboption_type' => '',
        'newtype' => 'none',
    ],
    [
        'suboption_title' => '인쇄',
        'suboption' => '중국2도',
        'price' => 150,
        'goods_seq' => 206718,
    ],
    [
        'suboption_title' => '인쇄',
        'suboption' => '중국3도',
        'price' => 200,
        'goods_seq' => 206718,
    ],
    [
        'suboption_title' => '인쇄',
        'suboption' => '중국4도',
        'price' => 250,
        'goods_seq' => 206718,
    ],
    [
        'suboption_title' => '인쇄',
        'suboption' => '한국1도',
        'price' => 0, // Consultation required usually, but let's set 0 for now as per legacy logic relying on text
        'goods_seq' => 206718,
    ],
    [
        'suboption_title' => '인쇄',
        'suboption' => '중국스티커',
        'price' => 0, // Base price might be complex, verify if legacy had fixed price here. 
                      // Legacy view showed text: "기본 1,000장 15,000원..."
        'goods_seq' => 206718,
    ],
    [
        'suboption_title' => '인쇄',
        'suboption' => '한국스티커',
        'price' => 0,
        'goods_seq' => 206718,
    ],
    // Add logic for '인쇄 문구 입력' and '로고 파일 첨부' 
    // Wait, 'inputs' ARE used for text/file in legacy. 
    // Legacy view has: <!--{ ? inputs && goods.inputoption_layout_position != 'down' }-->
    // So we ONLY remove the 'select' type inputs. Text and File inputs should remain!
];

// CRITICAL CORRECTION: Legacy uses inputs for "Text" and "File", but Suboptions for "Select" (Printing Type).
// So we must re-add the text/file inputs for 206718 properly.

$textInputs = [
    [
        'input_name' => '인쇄 문구 입력',
        'input_form' => 'text',
        'input_require' => '0',
        'input_limit' => 0,
        'goods_seq' => 206718
    ],
    [
        'input_name' => '로고 파일 첨부',
        'input_form' => 'file',
        'input_require' => '0',
        'input_limit' => 0,
        'goods_seq' => 206718
    ]
];

foreach ($subOptions as $opt) {
    // Fill defaults
    $opt = array_merge([
        'code_seq' => '',
        'sub_required' => 'n',
        'suboption_type' => '',
        'suboption_code' => '',
        'consumer_price' => 0,
        'price' => 0,
        'reserve_rate' => 0,
        'reserve_unit' => 'percent',
        'reserve' => 0,
        'commission_rate' => 0,
        'commission_type' => 'SUCO',
        'newtype' => 'none',
        'color' => '',
        'zipcode' => '',
        'address_type' => 'street',
        'address' => '',
        'address_street' => '',
        'addressdetail' => '',
        'sub_sale' => 'n',
        'coupon_input' => 0,
        'address_commission' => 0,
        'package_count' => 0,
        'package_goods_name1' => '',
        'package_option_seq1' => 0,
        'package_option1' => '',
        'package_unit_ea1' => 0,
        'relation_yn' => 'n'
    ], $opt);
    
    DB::table('fm_goods_suboption')->insert($opt);
}
echo "   Inserted " . count($subOptions) . " suboptions.\n";

echo "5. Restoring Text/File Inputs for 206718...\n";
foreach ($textInputs as $input) {
    DB::table('fm_goods_input')->insert($input);
}
echo "   Inserted " . count($textInputs) . " inputs.\n";

echo "Done.\n";
