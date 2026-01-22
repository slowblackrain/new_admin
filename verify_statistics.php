<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Verifying StatisticController Query ===\n";

try {
    // Mimic StatisticController Logic
    $userid = 'dometopia'; // Seller User ID
    $mseq = DB::table('fm_member')->where('userid', $userid)->value('member_seq');
    
    if (!$mseq) {
        echo "Member sequence not found for userid '{$userid}'. Using hardcoded 10 based on log.\n";
        $mseq = 10;
    }
    
    echo "Using Member Seq: {$mseq}\n";
    
    $startDate = '2020-01-01'; // Broad range to catch data
    $endDate = date('Y-m-d');
    
    echo "Querying from {$startDate} to {$endDate}...\n";
    
    // The Query from StatisticController.php
    $query = DB::table('fm_order_item as item')
            ->join('fm_order as ord', 'item.order_seq', '=', 'ord.order_seq')
            ->join('fm_order_item_option as opt', 'item.item_seq', '=', 'opt.item_seq')
            ->where('ord.member_seq', $mseq)
            ->where('ord.step', '>=', '25') 
            ->whereBetween('ord.regist_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select(
                'item.goods_seq',
                DB::raw('MAX(item.goods_name) as goods_name'),
                DB::raw('SUM(opt.ea) as total_ea'),
                DB::raw('SUM(opt.price * opt.ea) as total_price')
            )
            ->groupBy('item.goods_seq')
            ->orderByDesc('total_price')
            ->limit(5); // Limit for test
            
    $results = $query->get();
    
    echo "Query Execution Successful!\n";
    echo "Results Count: " . $results->count() . "\n";
    
    foreach($results as $row) {
        echo "Goods: {$row->goods_name} (Seq: {$row->goods_seq}) | Qty: {$row->total_ea} | Price: {$row->total_price}\n";
    }

} catch (\Exception $e) {
    echo "ERROR executing query: " . $e->getMessage() . "\n";
}
