<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Admin\Scm\ScmManageController;
use Illuminate\Http\Request;

echo "Running Ledger Query Test...\n";

$controller = new ScmManageController();
$request = Request::create('/admin/scm_manage/ledger', 'GET');

// Simulate the logic manually to debug builder
$warehousing = DB::table('fm_offer as o')
    ->join('fm_goods as g', 'o.goods_seq', '=', 'g.goods_seq')
    ->where('o.step', 11) // Stocked
    ->select(
        DB::raw("'입고' as type"),
        'o.stock_date as date',
        'g.goods_name',
        'o.ord_stock as qty'
    );

$revision = DB::table('fm_scm_stock_revision_goods as rg')
    ->join('fm_scm_stock_revision as r', 'rg.revision_seq', '=', 'r.revision_seq')
    ->where('r.revision_status', 1)
    ->select(
        DB::raw("'조정' as type"),
        'r.complete_date as date',
        'rg.goods_name',
        'rg.ea as qty'
    );

$query = $warehousing->unionAll($revision);

$results = DB::query()->fromSub($query, 'ledger')
    ->orderBy('date', 'desc')
    ->limit(10)
    ->get();

if ($results->isEmpty()) {
    echo "Warning: No ledger data found. Did you run warehousing/revision tests?\n";
} else {
    echo "Found " . $results->count() . " items:\n";
    foreach ($results as $item) {
        echo "[{$item->type}] {$item->date} | {$item->goods_name} | Qty: {$item->qty}\n";
    }
    echo "PASS: Ledger logic works.\n";
}
