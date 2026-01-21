<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Seller;

echo "=== Seller Dashboard Data Verification ===\n";

// 1. Authenticate as 'dometopia' provider
$providerId = 'dometopia';
$seller = Seller::where('provider_id', $providerId)->first();

if (!$seller) {
    echo "Error: Provider '{$providerId}' not found.\n";
    exit;
}

echo "Provider: {$seller->provider_id} (Seq: {$seller->provider_seq})\n";

// 2. Get Member Info
$memberData = DB::table('fm_provider as P')
    ->leftJoin('fm_member as M', 'P.userid', '=', 'M.userid')
    ->where('P.provider_seq', $seller->provider_seq)
    ->select('M.member_seq', 'M.userid')
    ->first();

$memberSeq = $memberData ? $memberData->member_seq : null;
echo "Linked Member Seq: " . ($memberSeq ?? 'None') . "\n";

if ($memberSeq) {
    // 3. Check Ready to Ship Count
    $steps = ['25', '35', '40', '50', '60', '70'];
    
    // Debug: Count by Step to see distribution
    $countsByStep = DB::table('fm_order_item as b')
        ->leftJoin('fm_order_item_option as c', 'b.item_seq', '=', 'c.item_seq')
        ->leftJoin('fm_order_item_suboption as d', 'b.item_seq', '=', 'd.item_seq')
        ->leftJoin('fm_order as e', 'b.order_seq', '=', 'e.order_seq')
        ->where('e.member_seq', $memberSeq)
        ->whereIn('c.step', $steps)
        ->select('c.step', DB::raw('count(distinct b.order_seq) as order_count'))
        ->groupBy('c.step')
        ->get();
        
    echo "\n[Ready To Ship Breakdown by Step]\n";
    foreach($countsByStep as $row) {
        echo "Step {$row->step}: {$row->order_count}\n";
    }

    $readyToShipCnt = DB::table('fm_order_item as b')
        ->leftJoin('fm_order_item_option as c', 'b.item_seq', '=', 'c.item_seq')
        ->leftJoin('fm_order_item_suboption as d', 'b.item_seq', '=', 'd.item_seq')
        ->leftJoin('fm_order as e', 'b.order_seq', '=', 'e.order_seq')
        ->where('e.member_seq', $memberSeq)
        ->where(function($q) use ($steps) {
            $q->whereIn('c.step', $steps)
                ->orWhereIn('d.step', $steps);
        })
        ->distinct('b.order_seq')
        ->count('b.order_seq');

    echo "\nTotal Ready to Ship Count: {$readyToShipCnt}\n";
}

// 4. Check Notices
echo "\n[Checking Notice Tables]\n";
$tables = DB::select('SHOW TABLES LIKE "fm_board%"');
echo "Found Board Tables:\n";
$possibleTables = [];
foreach($tables as $t) {
    $val = array_values((array)$t)[0];
    if (strpos($val, 'seller') !== false || strpos($val, 'notice') !== false) {
        echo "- $val\n";
        $possibleTables[] = $val;
    }
}

// Try to fetch from most likely candidates
foreach ($possibleTables as $tableName) {
    try {
        $count = DB::table($tableName)->count();
        echo "Table '{$tableName}' has {$count} records.\n";
        if ($count > 0) {
            $first = DB::table($tableName)->first();
            echo "  Sample: " . json_encode($first) . "\n";
        }
    } catch (\Exception $e) {
        echo "  Error querying '{$tableName}': " . $e->getMessage() . "\n";
    }
}
