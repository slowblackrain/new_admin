<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Analyzing Cellphone Field Patterns...\n";

// 1. Group by length to see patterns (e.g. 40=SHA1, 32/64=AES)
$stats = \Illuminate\Support\Facades\DB::table('fm_member')
    ->selectRaw('LENGTH(cellphone) as len, count(*) as cnt')
    ->where('cellphone', '!=', '')
    ->groupBy('len')
    ->orderBy('len')
    ->get();

echo "Length Distribution:\n";
foreach ($stats as $s) {
    echo "Length {$s->len}: {$s->cnt} users\n";
}

echo "\nSampling Members for Decryption Test (Key: OTgwNTc=)...\n";
$key = 'OTgwNTc=';

// 2. Sample from each length group
foreach ($stats as $s) {
    echo "\n[Length {$s->len} Sample]\n";
    $users = \Illuminate\Support\Facades\DB::table('fm_member')
        ->whereRaw('LENGTH(cellphone) = ?', [$s->len])
        ->limit(3)
        ->get();
        
    foreach ($users as $u) {
        $raw = $u->cellphone;
        // Try decrypt
        try {
            $dec = \Illuminate\Support\Facades\DB::select("SELECT AES_DECRYPT(UNHEX(?), ?) as d", [$raw, $key]);
            $res = $dec[0]->d;
             // Check if result is readable (utf8)
             $isReadable = $res && mb_check_encoding($res, 'UTF-8');
             $display = $isReadable ? $res : "(Unreadable/Null)";
        } catch (\Exception $e) {
            $display = "(Error)";
        }
        
        echo "User: {$u->userid} | Raw: " . substr($raw, 0, 10) . "... | Decrypted: {$display}\n";
    }
}
