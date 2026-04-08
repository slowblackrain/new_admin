<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\Order\OrderExcelService;
use Illuminate\Support\Facades\Auth;
use App\Models\Seller;

// 1. Authenticate as the test seller
$seller = Seller::where('provider_id', '102_store')->first();
Auth::guard('seller')->login($seller);

// 2. Setup file path
$csvFilePath = storage_path('app/test_order.csv');

// 3. Init Service
$service = app(OrderExcelService::class);

echo "Step 1: Parsing Excel File...\n";
list($orderData, $result_error) = $service->excel_upload($csvFilePath, 'check');

if (count($result_error) > 0) {
    echo "Validation Errors Found:\n";
    print_r($result_error);
    die();
}

echo "Validation Passed. " . count($orderData) . " valid rows found.\n";
echo "Step 2: Processing Order Creation...\n";

// 4. Process Orders
$result = $service->create_orders($orderData, $seller);

if ($result['success']) {
    echo "Success: " . $result['message'] . "\n";
} else {
    echo "Failure: " . $result['message'] . "\n";
}
