<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    // 1. Create or Update Member
    $memberId = 'testseller';
    $password = '1234'; 
    $md5Pass = md5($password);

    echo "Creating Member...\n";
    $memberSeq = DB::table('fm_member')->insertGetId([
        'userid' => $memberId,
        'password' => $md5Pass, 
        'user_name' => 'Test Seller',
        'email' => 'test@seller.com',
        'status' => 'active',
        'emoney' => 99000,
        'cash' => 5000,
        'regist_date' => now(),
        'update_date' => now(),
    ]);
    echo "Member ID: $memberSeq created.\n";

    // 2. Create Provider
    echo "Creating Provider...\n";
    $providerSeq = DB::table('fm_provider')->insertGetId([
        'provider_id' => $memberId,
        'userid' => $memberId, // Link to member
        'provider_passwd' => $md5Pass, // Authenticatable uses this
        'provider_name' => 'Test Provider Store',
        'provider_status' => 'Y',
        'regdate' => now(),
    ]);
    
    echo "Provider ID: $providerSeq created linked to Member $memberSeq.\n";
    echo "Please login with ID: 'testseller' and PW: '1234'\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
