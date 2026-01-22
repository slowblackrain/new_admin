<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 1. Check fm_order (Expected Plain Text)
$order = \Illuminate\Support\Facades\DB::table('fm_order')->where('member_seq', 10)->first();
if ($order) {
    echo "Order Email (Plain): " . $order->order_email . "\n";
} else {
    echo "No Order found for member 10.\n";
}

// 2. Check fm_member (Encrypted)
$member = \Illuminate\Support\Facades\DB::table('fm_member')->where('userid', 'newjjang3')->first();
if ($member) {
    echo "Member Encrypted Email (Hex): " . bin2hex($member->email) . "\n";
    
    $userKey = 'OTgwNTc=';
    $decodedKey = base64_decode($userKey);
    $keys = [
        $userKey,
        $decodedKey,
    ];
    
    foreach ($keys as $k) {
        try {
            // Try as string
            $decrypted = \Illuminate\Support\Facades\DB::select("SELECT AES_DECRYPT(UNHEX(?), ?) as d", [$member->email, $k]);
            $val = $decrypted[0]->d;
             echo "Key [{$k}]: " . ($val && mb_check_encoding($val, 'UTF-8') ? $val : '(null/garbage/binary)') . "\n";
        } catch (\Exception $e) {
             echo "Key [{$k}]: Error " . $e->getMessage() . "\n";
        }
    }
}
