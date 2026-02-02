<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\DB;
use App\Models\Order;

// 1. Find order items for our test product
$items = DB::table('fm_order_item')->where('goods_seq', 999999)->orderBy('item_seq', 'desc')->get();

if ($items->isEmpty()) {
    echo "No order items found for goods_seq 999999.\n";
    exit;
}

foreach ($items as $item) {
    echo "Found Order Seq: " . $item->order_seq . "\n";
    
    // Check Options
    $options = DB::table('fm_order_item_option')->where('item_seq', $item->item_seq)->get();
    foreach ($options as $opt) {
        echo " - Option Seq: " . $opt->option_seq . ", EA: " . $opt->ea . "\n";
        
        $targetOptionSeq = $opt->option_seq; 
        
        // 3. Check fm_goods (Total Stock)
        $goods = DB::table('fm_goods')->where('goods_seq', $item->goods_seq)->first();
        echo "   -> fm_goods.tot_stock: " . $goods->tot_stock . " (Expected: 99 if 1 purchased from 100)\n";

        // 4. Check fm_goods_supply (Option Stock)
        $supply = DB::table('fm_goods_supply')->where('option_seq', $targetOptionSeq)->first();
        if ($supply) {
             echo "   -> fm_goods_supply.stock: " . $supply->stock . " (Expected: 99)\n";
        } else {
             echo "   -> fm_goods_supply: Supply NOT FOUND for option $targetOptionSeq\n";
        }
        
        // 5. Check fm_scm_location_link
        $scm = DB::table('fm_scm_location_link')
            ->where('option_seq', $targetOptionSeq) // SCM usually links by option_seq? Or goods_seq?
            // based on controller code: ->where('option_seq', $matchedOption->option_seq)
            ->where('wh_seq', 1)
            ->first();

        if ($scm) {
             echo "   -> fm_scm_location_link.ea: " . $scm->ea . " (Expected: 99)\n";
        } else {
             echo "   -> fm_scm_location_link: Record NOT FOUND for option $targetOptionSeq, wh_seq 1\n";
        }
    }
}
exit;


foreach ($items as $item) {
    print_r($item);
    // echo " - Item: Goods Seq {$item->goods_seq}, Option Seq {$item->option_seq}, EA {$item->ea}\n";

    // 3. Check fm_goods (Total Stock)
    $goods = DB::table('fm_goods')->where('goods_seq', $item->goods_seq)->first();
    echo "   -> fm_goods.tot_stock: " . $goods->tot_stock . " (Expected: 99 if 1 purchased from 100)\n";

    // 4. Check fm_goods_supply (Option Stock)
    $supply = DB::table('fm_goods_supply')->where('goods_seq', $item->goods_seq)->first(); // Assuming single option for test
    echo "   -> fm_goods_supply.stock: " . $supply->stock . " (Expected: 99 if 1 purchased from 100)\n";

    // 5. Check fm_scm_location_link (SCM Stock)
    // Assuming wh_seq 1 is default
    $scm = DB::table('fm_scm_location_link')
        ->where('goods_seq', $item->goods_seq)
        ->where('wh_seq', 1)
        ->first();
    
    if ($scm) {
         echo "   -> fm_scm_location_link.ea: " . $scm->ea . " (Expected: 99 if 1 purchased from 100)\n";
    } else {
         echo "   -> fm_scm_location_link: Record NOT FOUND for wh_seq 1\n";
    }
}
