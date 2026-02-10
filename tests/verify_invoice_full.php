<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemOption;
use App\Services\Order\InvoiceService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 1. Setup Dummy Order
echo "1. Creating Dummy Order...\n";
$orderSeq = 'TEST_' . time();
$itemSeq = 999999;
$optionSeq = 888888;
$goodsSeq = 12345; // Must be valid logic for service? Service uses goods_seq only for logging.

try {
    DB::beginTransaction();

    // Cleanup first
    DB::table('fm_order')->where('order_seq', $orderSeq)->delete();
    DB::table('fm_order_item')->where('order_seq', $orderSeq)->delete();
    DB::table('fm_order_item_option')->where('order_seq', $orderSeq)->delete();
    DB::table('fm_goods_export')->where('order_seq', $orderSeq)->delete();
    DB::table('fm_goods_export_item')->where('export_code', 'LIKE', 'D' . date('ymd') . '%')->delete(); // Risky? No, just for this test context if we use the object.
    
    // Create Order
    DB::table('fm_order')->insert([
        'order_seq' => $orderSeq,
        'step' => '25', // Payment Confirmed
        'regist_date' => date('Y-m-d H:i:s'),
        'order_user_name' => 'Test User',
        'linkage_id' => '', // General Order
        'sitetype' => 'DOTO',
        'settleprice' => 1000,
    ]);

    DB::table('fm_order_item')->insert([
        'item_seq' => $itemSeq,
        'order_seq' => $orderSeq,
        'goods_seq' => $goodsSeq,
        'goods_name' => 'Test Goods',
    ]);

    DB::table('fm_order_item_option')->insert([
        'item_option_seq' => $optionSeq,
        'item_seq' => $itemSeq,
        'order_seq' => $orderSeq,
        'ea' => 1,
        'step' => '25',
        'supply_price' => 500,
        'price' => 1000,
    ]);

    DB::commit();
    echo "Dummy Order Created: $orderSeq\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "Setup Failed: " . $e->getMessage() . "\n";
    exit;
}

// 2. Test 'insert' Mode
echo "\n2. Testing 'insert' Mode...\n";
$service = new InvoiceService();

// Mock CSV Data via direct invocation or temp file? 
// Service expects a File object (getRealPath).
// Let's manually create a temp csv.
$tempCsv = tempnam(sys_get_temp_dir(), 'inv');
$handle = fopen($tempCsv, 'w');
// Header
fputcsv($handle, ['SEQ', 'CODE', 'NUMBER', 'MEMO', 'SMS']);
// Row
fputcsv($handle, [$orderSeq, 'code0', '1234567890', 'Test Memo', '']);
fclose($handle);

$file = new UploadedFile($tempCsv, 'test.csv', 'text/csv', null, true);

try {
    $result = $service->processExcel($file, 'insert');
    echo "Result: Success={$result['success']}, Fail={$result['fail']}\n";
    
    if ($result['success'] == 1) {
        $order = DB::table('fm_order')->where('order_seq', $orderSeq)->first();
        echo "Order Step: " . $order->step . " (Expected: 45)\n";
        
        $export = DB::table('fm_goods_export')->where('order_seq', $orderSeq)->first();
        echo "Export Created: " . ($export ? 'YES' : 'NO') . "\n";
        
        if ($order->step == '45' && $export) {
            echo "INSERT MODE PASSED.\n";
        } else {
            echo "INSERT MODE FAILED.\n";
        }
    } else {
        print_r($result['errors']);
    }

} catch (\Exception $e) {
    echo "INSERT MODE EXCEPTION: " . $e->getMessage() . "\n";
}

// 3. Test 'all' Mode
echo "\n3. Testing 'all' Mode...\n";

// Re-open/Create fresh CSV if needed (file pointer might be at end? Service opens it fresh)
// Reuse $file object? Service opens getRealPath(). It should be fine.

try {
    $result = $service->processExcel($file, 'all');
    echo "Result: Success={$result['success']}, Fail={$result['fail']}\n";
    
    if ($result['success'] == 1) {
        $order = DB::table('fm_order')->where('order_seq', $orderSeq)->first();
        echo "Order Step: " . $order->step . " (Expected: 65)\n"; // 65 for General Order
        
        $log = DB::table('fm_scm_location_link_out')->where('order_seq', $orderSeq)->first();
        echo "Out Log Created: " . ($log ? 'YES' : 'NO') . "\n";
        
        if ($order->step == '65' && $log) {
            echo "ALL MODE PASSED.\n";
        } else {
            echo "ALL MODE FAILED.\n";
        }
    } else {
        print_r($result['errors']);
    }

} catch (\Exception $e) {
    echo "ALL MODE EXCEPTION: " . $e->getMessage() . "\n";
}

// Cleanup
unlink($tempCsv);
echo "\nTest Complete.\n";
