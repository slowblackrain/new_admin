<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Seller;

echo "=== Final Data Verification ===\n";

// 1. Authenticate as 'parksh73' provider
$providerId = 'parksh73';
$seller = Seller::where('provider_id', $providerId)->first();

if (!$seller) {
    echo "Error: Provider '{$providerId}' not found.\n";
    exit;
}

echo "Provider: {$seller->provider_id} (Seq: {$seller->provider_seq}, UserID: {$seller->userid})\n";

// 2. Get Member Info
$memberData = DB::table('fm_provider as P')
    ->leftJoin('fm_member as M', 'P.userid', '=', 'M.userid')
    ->where('P.provider_seq', $seller->provider_seq)
    ->select('M.member_seq', 'M.userid')
    ->first();

$memberSeq = $memberData ? $memberData->member_seq : null;
echo "Linked Member Seq: " . ($memberSeq ?? 'None') . "\n";

// 3. Check Ready to Ship Count
if ($memberSeq) {
    $steps = ['25', '35', '40', '50', '60', '70'];
    
    $readyToShipCnt = DB::table('fm_order_item as b')
        ->leftJoin('fm_order_item_option as c', 'b.item_seq', '=', 'c.item_seq')
        ->leftJoin('fm_order_item_suboption as d', 'b.item_seq', '=', 'd.item_seq')
        ->leftJoin('fm_order as e', 'b.order_seq', '=', 'e.order_seq')
        ->where('e.member_seq', $memberSeq)
        ->where(function($q) use ($steps) {
            $q->whereIn('c.step', $steps) // Check option step
                ->orWhereIn('d.step', $steps); // Check suboption step
        })
        ->distinct('b.order_seq')
        ->count('b.order_seq');

    echo "Ready to Ship Count: {$readyToShipCnt}\n";
    
    // Debug: List order_seqs if any
    $orders = DB::table('fm_order_item as b')
        ->leftJoin('fm_order_item_option as c', 'b.item_seq', '=', 'c.item_seq')
        ->leftJoin('fm_order_item_suboption as d', 'b.item_seq', '=', 'd.item_seq')
        ->leftJoin('fm_order as e', 'b.order_seq', '=', 'e.order_seq')
        ->where('e.member_seq', $memberSeq)
        ->where(function($q) use ($steps) {
            $q->whereIn('c.step', $steps)
                ->orWhereIn('d.step', $steps);
        })
        ->distinct('b.order_seq')
        ->limit(5)
        ->pluck('b.order_seq');
        
    if ($orders->isNotEmpty()) {
        echo "Sample Order Seqs: " . implode(', ', $orders->toArray()) . "\n";
    }
}

// 4. Check Notices (Raw Query for 'gs_seller_notice')
echo "\n[Notice Check]\n";
$notices = DB::table('fm_boarddata')
    ->where('boardid', 'gs_seller_notice')
    ->orderBy('gid', 'asc') // FirstMall standard sorting
    ->orderBy('m_date', 'asc')
    ->limit(5)
    ->get();

echo "Found " . $notices->count() . " notices.\n";
foreach($notices as $n) {
    echo "- [{$n->m_date}] {$n->subject}\n";
}
