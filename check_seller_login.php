<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Seller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

// 1. Find a test seller
$seller = Seller::where('provider_status', 'Y')->first();

if (!$seller) {
    echo "No active seller found in fm_provider.\n";
    exit;
}

echo "Found Seller: " . $seller->provider_id . "\n";
echo "Hashed Password (MD5): " . $seller->provider_passwd . "\n";

// 2. Test Manual MD5 Check
// In a real scenario, we might know the plain text, but here we can only check if the logic holds
// effectively we can't 'guess' the plain text, but we can verify our Model configuration.

echo "Model Configuration:\n";
echo "- Table: " . $seller->getTable() . "\n";
echo "- Key: " . $seller->getKeyName() . "\n";
echo "- Auth Password Field: " . $seller->getAuthPassword() . "\n";

// 3. Check Auth Guard
echo "\nAuth Guard 'seller' Check:\n";
try {
    $guard = Auth::guard('seller');
    echo "- Guard Driver: " . config('auth.guards.seller.driver') . "\n";
    echo "- Guard Provider: " . config('auth.guards.seller.provider') . "\n";
    echo "- Provider Model: " . config('auth.providers.sellers.model') . "\n";
} catch (\Exception $e) {
    echo "- Error: " . $e->getMessage() . "\n";
}
