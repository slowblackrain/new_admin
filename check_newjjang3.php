<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$userId = 'newjjang3';

// Check fm_member
$member = \Illuminate\Support\Facades\DB::table('fm_member')->where('userid', $userId)->first();

if (!$member) {
    echo "Member '{$userId}' not found.\n";
    exit;
}

echo "Member Found: {$member->userid} (Seq: {$member->member_seq})\n";

// Check fm_provider
$provider = \Illuminate\Support\Facades\DB::table('fm_provider')->where('provider_id', $userId)->first();

if ($provider) {
    echo "Provider Found: {$provider->provider_id} (Seq: {$provider->provider_seq})\n";
} else {
    echo "Provider not found for this ID (might be linked via member_seq).\n";
}

// Check provider_YN (ATS Access)
if (isset($member->provider_YN)) {
    echo "provider_YN: {$member->provider_YN}\n";
} else {
    echo "provider_YN column not found on member.\n";
}
