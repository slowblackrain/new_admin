<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\Order\OrderProcessController;

echo "=== Verifying Order Status Update (Safe Test Mode) ===\n";

// 1. Create Test Order
echo "[1] Creating Test Order...\n";
$testOrderSeq = 'TEST-' . date('YmdHis');
try {
    // Insert FM_ORDER
    DB::table('fm_order')->insert([
        'order_seq' => $testOrderSeq,
        'step' => 15, // Order Received
        'order_user_name' => 'Test User',
        'order_cellphone' => '010-0000-0000',
        'recipient_user_name' => 'Test Recipient',
        'recipient_address' => 'Test Address',
        'recipient_cellphone' => '010-1111-1111',
        'regist_date' => now(),
        'settleprice' => 10000,
        'payment' => 'bank',
        'bank_account' => 'Test Bank 1234',
        'depositor' => 'Test Depositor',
        'shipping_cost' => 0,
        'member_seq' => null
    ]);

    // Insert FM_ORDER_ITEM
    $itemSeq = DB::table('fm_order_item')->insertGetId([
        'order_seq' => $testOrderSeq,
        'goods_seq' => 1, // Assumptions
        'goods_name' => 'Test Product',
    ]);

    // Insert FM_ORDER_ITEM_OPTION
    DB::table('fm_order_item_option')->insert([
        'item_seq' => $itemSeq,
        'order_seq' => $testOrderSeq,
        'ea' => 1,
        'step' => 15,
        'price' => 10000,
        'supply_price' => 5000,
        'consumer_price' => 12000,
    ]);

    echo "    > Created Order: {$testOrderSeq} (Step 15)\n";

    // 2. Simulate Status Update (15 -> 25)
    echo "[2] Simulating Update to Step 25 (Payment Confirm)...\n";
    
    // Mock Request
    $request = Request::create('/admin/order/process', 'POST', [
        'order_seq' => $testOrderSeq,
        'action' => 'deposit'
    ]);

    // Set Admin Auth Mock (Manually or just specific logic test)
    // Since Auth facade is used in controller, we need to mock login or bypass
    // For this script, we'll login as a dummy admin if possible, or catch the error if Auth fails.
    // However, simplest way is to manually run the update logic or Login first.
    // Let's manually invoke the logic similar to controller for verification without full Auth overhead if complex.
    // BUT we want to test the Controller.
    
    // Mock Admin Login
    // Assuming 'dmtadmin' exists from previous context
    $admin = \App\Models\Admin::where('manager_id', 'dmtadmin')->first();
    if ($admin) {
        \Illuminate\Support\Facades\Auth::guard('admin')->login($admin);
    } else {
        echo "    > Warning: Admin user not found. Creating temporary admin context.\n";
        // Logic might fail if no user.
    }

    $controller = new OrderProcessController();
    $response = $controller->updateStatus($request);

    // 3. Verify Update
    echo "[3] Verifying Database Changes...\n";
    $updatedOrder = DB::table('fm_order')->where('order_seq', $testOrderSeq)->first();
    $updatedOption = DB::table('fm_order_item_option')->where('order_seq', $testOrderSeq)->first();

    echo "    > Order Step: {$updatedOrder->step} (Expected: 25)\n";
    echo "    > Option Step: {$updatedOption->step} (Expected: 25)\n";

    if ($updatedOrder->step == 25 && $updatedOption->step == 25) {
        echo "    > SUCCESS: Status updated correctly.\n";
    } else {
        echo "    > FAILURE: Status did not update.\n";
    }

} catch (\Exception $e) {
    echo "    > ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
} finally {
    // 4. Cleanup
    echo "[4] Cleaning up Test Data...\n";
    DB::table('fm_order_item_option')->where('order_seq', $testOrderSeq)->delete();
    DB::table('fm_order_item')->where('order_seq', $testOrderSeq)->delete();
    DB::table('fm_order')->where('order_seq', $testOrderSeq)->delete();
    DB::table('fm_order_log')->where('order_seq', $testOrderSeq)->delete();
    echo "    > Cleanup Complete.\n";
}
