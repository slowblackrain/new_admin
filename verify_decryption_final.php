<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\Member::where('userid', 'newjjang3')->first();
if ($user) {
    echo "User: " . $user->userid . "\n";
    echo "Decrypted Email: " . $user->email . "\n";
    echo "Decrypted Cellphone: " . $user->cellphone . "\n";
    
    // Check raw attribute to confirm it was encrypted (optional, need to use getAttributes to bypass accessor)
    $raw = $user->getAttributes();
    echo "Raw Email (truncated): " . substr($raw['email'], 0, 10) . "...\n";
    echo "Raw Cellphone: " . $raw['cellphone'] . "\n";

    // Try explicit decrypt on cellphone to see what DB returns
    $key = 'OTgwNTc=';
    $res = \Illuminate\Support\Facades\DB::select("SELECT AES_DECRYPT(UNHEX(?), ?) as d", [$raw['cellphone'], $key]);
    echo "Explicit Decrypt Cellphone: " . bin2hex($res[0]->d) . " (Hex) / " . $res[0]->d . " (Str)\n";
} else {
    echo "User newjjang3 not found.\n";
}
