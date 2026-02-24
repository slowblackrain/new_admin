<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Let's create a dummy member to test dormancy
$m = new \App\Models\Member();
$m->userid = 'test_dormant';
$m->password = hash('sha256', '2222');
$m->user_name = 'Dormant Tester';
$m->email = 'sleep@test.com';
$m->phone = '02-333-4444';
$m->status = 'done';
$m->save();

$memberSeq = $m->member_seq;
echo "Created Dormant Tester ID: " . $memberSeq . PHP_EOL;

try {
    $service = app(\App\Services\MemberManagementService::class);
    $logId = $service->processDormancyOn($memberSeq);
    echo "Dormancy ON processed successfully! Log ID: " . $logId . PHP_EOL;

    // Verify DB State
    $checkMain = \Illuminate\Support\Facades\DB::table('fm_member')->where('member_seq', $memberSeq)->first();
    echo "Main Table Status: " . $checkMain->status . " | Name: " . $checkMain->user_name . PHP_EOL;

    $checkDr = \Illuminate\Support\Facades\DB::table('fm_member_dr')->where('member_seq', $memberSeq)->first();
    echo "DR Table Name: " . ($checkDr ? $checkDr->user_name : 'NOT FOUND') . PHP_EOL;

} catch (\Exception $e) {
    echo "Error processing dormancy: " . $e->getMessage() . PHP_EOL;
}
