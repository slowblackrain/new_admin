<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Goods;

echo "Verifying Printing Option Migration for Goods 206718...\n";

// 1. Check fm_goods_input (Should NOT have 'select' types)
$inputs = DB::table('fm_goods_input')->where('goods_seq', 206718)->get();
echo "1. Current Inputs (" . $inputs->count() . "):\n";
foreach ($inputs as $input) {
    echo "   - [{$input->input_form}] {$input->input_name}\n";
    if ($input->input_form === 'select') {
        echo "     [FAIL] 'select' input still exists!\n";
    }
}

// 2. Check fm_goods_suboption (Should have Printing Options with Prices)
$subOptions = DB::table('fm_goods_suboption')->where('goods_seq', 206718)->get();
echo "2. Current SubOptions (" . $subOptions->count() . "):\n";
foreach ($subOptions as $opt) {
    echo "   - [{$opt->suboption_title}] {$opt->suboption} -> Price: {$opt->price}\n";
}

// 3. Test Eloquent Relationship
try {
    $goods = Goods::with('subOptions')->find(206718);
    if ($goods && $goods->subOptions->count() > 0) {
        echo "3. Eloquent Relationship: SUCCESS (Loaded " . $goods->subOptions->count() . " items)\n";
    } else {
        echo "3. Eloquent Relationship: FAIL (No suboptions loaded)\n";
    }
} catch (\Exception $e) {
    echo "3. Eloquent Relationship: ERROR (" . $e->getMessage() . ")\n";
}
