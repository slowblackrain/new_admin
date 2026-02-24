<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$memberSeq = 346385; // ID generated from the ON test

try {
    $service = app(\App\Services\MemberManagementService::class);
    $logId = $service->processDormancyOff($memberSeq);
    echo "Dormancy OFF processed successfully! Log ID: " . $logId . PHP_EOL;

    // Verify DB State
    $checkMain = \Illuminate\Support\Facades\DB::table('fm_member')->where('member_seq', $memberSeq)->first();
    echo "Restored Main Table Status: " . $checkMain->status . " | Name: " . $checkMain->user_name . PHP_EOL;

    $checkDr = \Illuminate\Support\Facades\DB::table('fm_member_dr')->where('member_seq', $memberSeq)->first();
    echo "DR Table Record Exists: " . ($checkDr ? 'YES' : 'NO') . PHP_EOL;

} catch (\Exception $e) {
    echo "Error processing dormancy OFF: " . $e->getMessage() . PHP_EOL;
}
