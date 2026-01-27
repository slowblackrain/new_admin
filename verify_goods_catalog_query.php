<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "Simulating GoodsController::catalog Query...\n";

    $query = DB::table('fm_goods as g')
        ->leftJoin('fm_goods_image as i', function($join) {
            $join->on('g.goods_seq', '=', 'i.goods_seq')->where('i.image_type', 'list1');
        })
        ->leftJoin('fm_provider as p', 'g.provider_seq', '=', 'p.provider_seq')
        ->leftJoin('fm_category_link as cl', function($join) {
            $join->on('g.goods_seq', '=', 'cl.goods_seq')->where('cl.link', 1);
        })
        ->leftJoin('fm_category as c', 'cl.category_code', '=', 'c.category_code')
        ->select(
            'g.goods_seq', 'g.goods_name', 'g.goods_code', 'g.goods_view', 'g.goods_status', 
            'g.regist_date', 'g.update_date', 'g.goods_scode', 'g.provider_status',
            // 'g.model', 'g.maker_name', 'g.origin_name', 
            'g.provider_seq', 'g.offer_chk', // offer_chk might be the issue
            'i.image',
            'p.provider_name',
            'c.title as category_title',
            DB::raw('(SELECT SUM(s.stock) FROM fm_goods_supply as s WHERE s.goods_seq = g.goods_seq) as total_stock'),
            DB::raw('(SELECT SUM(s.badstock + s.reservation15 + s.reservation25) FROM fm_goods_supply as s WHERE s.goods_seq = g.goods_seq) as total_holding'),
            DB::raw('(SELECT MAX(o.regist_date) FROM fm_order as o JOIN fm_order_item as oi ON o.order_seq = oi.order_seq WHERE oi.goods_seq = g.goods_seq) as l_date')
        )
        ->orderBy('g.regist_date', 'desc')
        ->limit(10); // Use limit instead of paginate for CLI

    $results = $query->get();

    echo "Query Successful.\n";
    echo "Retrieved " . $results->count() . " items.\n";
    
    if ($results->count() > 0) {
        print_r($results->first());
    }

} catch (\Exception $e) {
    echo "Query FAILED: " . $e->getMessage() . "\n";
}
