<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Member;
use App\Models\Seller;
use App\Models\Goods;
use App\Services\Order\OrderExcelService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

try {
    // 1. Setup Test Member & Seller
    $providerId = 'newjjang3';
    $member = Member::where('userid', $providerId)->first();
    $seller = Seller::where('provider_id', $providerId)->first();

    if (!$member || !$seller) {
        throw new Exception("Test seller newjjang3 not found.");
    }

    // Give emoney for testing
    $initialEmoney = 500000;
    $member->update(['emoney' => $initialEmoney]);
    echo "Initial EMoney: " . number_format($initialEmoney) . " won\n";

    // Authenticate as Seller
    Auth::guard('seller')->login($seller);

    // 2. Create a mock CSV file
    $csvPath = __DIR__.'/storage/test_order_upload.csv';
    $fp = fopen($csvPath, 'w');
    $header = ["수령인", "수령인핸드폰", "우편번호", "주소", "상품코드", "수량"];
    fputcsv($fp, $header);
    
    // Find a valid product to test with
    $goods = Goods::where('goods_view', 'look')
                  ->where('goods_status', 'normal')
                  ->whereNotNull('goods_code')
                  ->where('goods_code', '!=', '')
                  ->first(); // Use any active good
    if (!$goods) throw new Exception("No active goods found with goods_code.");
    $goods_code = $goods->goods_code;
    
    $row1 = ["김철수", "010-1234-5678", "12345", "서울시 강남구", $goods_code, 2];
    fputcsv($fp, $row1);
    fclose($fp);

    // 3. Test Parsing
    $excelService = app(OrderExcelService::class);
    list($orderData, $result_error) = $excelService->excel_upload($csvPath, 'check');

    echo "\n[1] Parser Results:\n";
    if (count($result_error) > 0) {
        echo "Errors found during parsing:\n";
        print_r($result_error);
        exit(1);
    }

    echo "Successfully parsed " . count($orderData) . " row(s).\n";
    $firstRowPrice = collect($orderData)->sum('settleprice');
    echo "Expected Total Settle Price: " . number_format($firstRowPrice) . " won\n";

    // 4. Test Order Creation
    echo "\n[2] Executing create_orders...\n";
    $result = $excelService->create_orders($orderData, $seller);

    if (isset($result['success']) && $result['success']) {
        echo "Order Creation Result: " . $result['message'] . "\n";
        
        // Verify DB updates
        $member->refresh();
        echo "New EMoney Balance: " . number_format($member->emoney) . " won\n";
        
        $deducted = $initialEmoney - $member->emoney;
        echo "Deducted Amount: " . number_format($deducted) . " won == Expected: " . number_format($firstRowPrice) . " won\n";
        
        if ($deducted == $firstRowPrice) {
            echo "\n=> SUCCESS: Emoney deduction matched Settle Price perfectly.\n";
        } else {
            echo "\n=> FAILED: Emoney mismatch!\n";
        }
    } else {
        echo "Order Creation Failed: " . ($result['message'] ?? 'Unknown Error') . "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
