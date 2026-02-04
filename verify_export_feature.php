<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Seller\SellerExportController;
use Illuminate\Support\Facades\Auth;
use App\Models\Seller;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Laravel Bootstrapped.\n";

echo "Starting Verification...\n";

// Login
$providerId = 'newjjang3';
$seller = Seller::where('provider_id', $providerId)->first();

if (!$seller) {
    echo "Error: Seller {$providerId} not found.\n";
    exit(1);
}

echo "Found Seller: " . $seller->provider_id . "\n";
Auth::guard('seller')->login($seller);
echo "Logged in as Seller ID: " . Auth::guard('seller')->id() . "\n";

// Call Controller
echo "Calling Controller Directly...\n";
$controller = new SellerExportController();
$request = Request::create('/selleradmin/export/catalog', 'GET');

try {
    $view = $controller->catalog($request);
    
    if (is_object($view) && method_exists($view, 'getName')) {
        echo "Success! Controller returned View: " . $view->getName() . "\n";
        echo "Data Keys: " . implode(', ', array_keys($view->getData())) . "\n";
        
        $data = $view->getData();
        $exports = $data['exports'];
        echo "Exports Count: " . $exports->count() . "\n";
        
        if ($exports->count() > 0) {
            echo "First Export Code: " . $exports->first()->export_code . "\n";
        } else {
            echo "No exports found for this seller.\n";
        }

    } else {
        echo "Controller returned unexpected type: " . gettype($view) . "\n";
        if (is_object($view)) {
             echo "Class: " . get_class($view) . "\n";
        }
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
