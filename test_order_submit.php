<?php
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Front\OrderController;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

// Login
$user = App\Models\Member::where('userid', 'testseller')->first();
Auth::login($user);
echo "Logged in as: " . Auth::user()->userid . "\n";

// Mock Request
$request = Request::create('/order/store', 'POST', [
    'cart_seq' => [3303539], // Use one valid seq
    'order_user_name' => 'Test User',
    'order_cellphone' => '010-1234-5678',
    'order_email' => 'test@example.com',
    'recipient_user_name' => 'Test Receiver',
    'recipient_cellphone' => '010-1234-5678',
    'recipient_zipcode' => '12345',
    'recipient_address' => 'Seoul',
    'recipient_address_street' => 'Seoul St', // Optional but good
    'recipient_address_detail' => '101',
    'payment' => 'bank',
    'bank_account' => 'Bank info',
    'depositor' => 'Test Depositor',
    'memo' => 'Test Order',
    'delivery_chk' => 'on', // just in case
]);

// Resolve Controller
$controller = $app->make(OrderController::class);

try {
    $response = $controller->store($request);
    
    // Response processing
    if ($response->isRedirection()) {
        echo "Redirected to: " . $response->getTargetUrl() . "\n";
        // Check session errors
        if (session()->has('errors')) {
            echo "Errors: " . print_r(session('errors')->all(), true) . "\n";
        }
    } else {
        echo "Response Content: " . substr($response->getContent(), 0, 500) . "...\n";
    }

} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
