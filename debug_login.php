<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Seller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

echo "Debug Login Script\n";

$username = 'dometopia';
$password = 'dometopia';

// 1. Retrieve User
$user = Seller::where('provider_id', $username)->first();

if (!$user) {
    echo "User '$username' not found!\n";
    exit;
}

echo "User found: " . $user->provider_id . " (seq: " . $user->provider_seq . ")\n";
echo "Stored Password Hash: " . $user->provider_passwd . "\n";
echo "Input Password MD5: " . md5($password) . "\n";

// 2. Check Equality
if ($user->provider_passwd === md5($password)) {
    echo "Password MATCHES via direct comparison.\n";
} else {
    echo "Password MISMATCH via direct comparison.\n";
}

// 3. Test Auth Attempt manually (simulating LoginController)
$credentials = ['provider_id' => $username, 'password' => $password];

if (Auth::guard('seller')->attempt($credentials)) {
    echo "Auth::guard('seller')->attempt() SUCCEEDED.\n";
} else {
    echo "Auth::guard('seller')->attempt() FAILED.\n";
    
    // Debug why
    // Check if provider is loaded correctly
    // The provider name in config/auth.php is 'sellers' using driver 'seller_driver'
}
