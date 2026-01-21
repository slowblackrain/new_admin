<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;

// Find a provider with provider_YN = 'Y'
$atsMember = DB::table('fm_member')
    ->where('provider_YN', 'Y')
    ->where('status', 'active') // Assuming active status if column exists, or just check provider linkage
    ->first();

if ($atsMember) {
    echo "Found ATS Member: " . $atsMember->userid . " (Seq: " . $atsMember->member_seq . ")\n";
    
    // Check if they exist in fm_provider
    $provider = DB::table('fm_provider')->where('provider_id', $atsMember->userid)->first();
    if ($provider) {
        echo "Linked Provider Found: " . $provider->provider_id . " (Seq: " . $provider->provider_seq . ")\n";
        echo "This account can be used for ATS verification.\n";
    } else {
        echo "No linked provider found in fm_provider for userid " . $atsMember->userid . "\n";
    }
} else {
    echo "No ATS Member (provider_YN='Y') found.\n";
}
