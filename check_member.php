<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$userId = 'test_front_01';
echo "Checking Member: $userId\n";

$member = \Illuminate\Support\Facades\DB::table('fm_member')->where('userid', $userId)->first();

if ($member) {
    echo "Member Found!\n";
    // echo "Seq: " . $member->member_seq . "\n";
    // echo "Name: " . $member->username . "\n";
    print_r($member);
} else {
    echo "Member NOT Found.\n";
}
