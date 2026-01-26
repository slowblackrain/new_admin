<?php

use App\Models\Order;
use App\Models\OrderItemOption;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Admin\Order\OrderProcessController;
use Illuminate\Http\Request;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "--- Stock Logic Verification ---\n";

// 1. Pick a test Order Item Option
$item = OrderItemOption::first();
if (!$item) { 
    echo "No Order Items found to test.\n"; 
    exit;
}

$optionSeq = $item->option_seq;
$orderSeq = $item->order_seq;

// 2. Prepare Helper to check stock
function getStock($optSeq) {
    return DB::table('fm_goods_supply')->where('option_seq', $optSeq)->first();
}

$initial = getStock($optionSeq);
echo "Initial State: Stock={$initial->stock}, Res15={$initial->reservation15}, Res25={$initial->reservation25}\n";

// 3. Mock Controller
$controller = new OrderProcessController();

// --- TEST 1: Deposit Confirm (15 -> 25) ---
echo "\n[Test 1] Deposit Confirm (Simulating 15 -> 25)...\n";
// Manually set step to 15 first to simulate pre-condition
DB::table('fm_order')->where('order_seq', $orderSeq)->update(['step' => 15]);

// Call Controller
$req = new Request(['order_seq' => $orderSeq, 'mode' => 'deposit_confirm']);
$controller->updateStatus($req);

$afterDeposit = getStock($optionSeq);
echo "Result: Res15={$afterDeposit->reservation15}, Res25={$afterDeposit->reservation25}\n";
echo "Check: Res15 should decrease, Res25 should increase.\n";

// --- TEST 2: Export Manual Step to 45 (Prepare) ---
echo "\n[Setup] Moving to step 45 (Prepare)...\n";
// The controller 'prepare_goods' moves 25 -> 45. Let's do it via controller to be safe.
$req = new Request(['order_seq' => $orderSeq, 'mode' => 'prepare_goods']);
$controller->updateStatus($req);

// --- TEST 3: Export Complete (45 -> 55) ---
echo "\n[Test 3] Export Complete (Simulating 45 -> 55)...\n";
$req = new Request(['order_seq' => $orderSeq, 'mode' => 'export_goods']);
$controller->updateStatus($req);

$afterExport = getStock($optionSeq);
echo "Result: Res25={$afterExport->reservation25}, Stock={$afterExport->stock}\n";
echo "Check: Res25 should decrease, Stock should decrease.\n";


// --- TEST 4: Cancel Order (55 -> 95) ---
echo "\n[Test 4] Cancel Order (Simulating 55 -> 95)...\n";
$req = new Request(['order_seq' => $orderSeq, 'mode' => 'cancel_order']);
$controller->updateStatus($req);

$afterCancel = getStock($optionSeq);
echo "Result: Stock={$afterCancel->stock}\n";
echo "Check: Stock should increase (restore).\n";


echo "\n--- Verification Complete ---\n";
