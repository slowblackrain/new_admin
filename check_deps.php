<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

function checkTable($table) {
    echo "Checking $table columns:\n";
    try {
        $cols = DB::select("DESCRIBE $table");
        foreach ($cols as $col) {
            echo " - " . $col->Field . "\n";
        }
    } catch (\Exception $e) {
        echo "Error checking $table: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

checkTable('fm_goods_supply');
checkTable('fm_category');
checkTable('fm_provider');
checkTable('fm_category_link');
checkTable('fm_order_item');

// Try running the query mechanically to see error
echo "Attempting Query...\n";
try {
    $results = DB::table('fm_goods as g')
            ->leftJoin('fm_goods_image as i', function($join) {
                $join->on('g.goods_seq', '=', 'i.goods_seq')->where('i.image_type', 'list1');
            })
            ->leftJoin('fm_provider as p', 'g.provider_seq', '=', 'p.provider_seq')
            ->leftJoin('fm_category_link as cl', function($join) {
                $join->on('g.goods_seq', '=', 'cl.goods_seq')->where('cl.link', 1); 
            })
            ->leftJoin('fm_category as c', 'cl.category_code', '=', 'c.category_code')
            ->select(
                'g.goods_seq',
                'p.provider_name',
                'c.title as category_title',
                DB::raw('(SELECT SUM(stock) FROM fm_goods_supply WHERE goods_seq = g.goods_seq) as total_stock'),
                DB::raw('(SELECT SUM(badstock + reservation15 + reservation25) FROM fm_goods_supply WHERE goods_seq = g.goods_seq) as total_holding'),
                DB::raw('(SELECT MAX(regist_date) FROM fm_order_item WHERE goods_seq = g.goods_seq) as l_date')
            )
            ->limit(1)
            ->get();
    echo "Query Success!\n";
} catch (\Exception $e) {
    echo "Query Failed: " . $e->getMessage() . "\n";
}
