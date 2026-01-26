<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Verifying Query (Round 3)...\n";
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
                DB::raw('(SELECT SUM(s.stock) FROM fm_goods_supply as s WHERE s.goods_seq = g.goods_seq) as total_stock'),
                DB::raw('(SELECT SUM(s.badstock + s.reservation15 + s.reservation25) FROM fm_goods_supply as s WHERE s.goods_seq = g.goods_seq) as total_holding'),
                DB::raw('(SELECT MAX(o.regist_date) FROM fm_order as o JOIN fm_order_item as oi ON o.order_seq = oi.order_seq WHERE oi.goods_seq = g.goods_seq) as l_date')
            )
            ->limit(1)
            ->get();
    echo "Query Success! First Row: " . json_encode($results[0] ?? []) . "\n";
} catch (\Exception $e) {
    echo "Query Failed: " . $e->getMessage() . "\n";
}
