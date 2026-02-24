<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Find or create test active member
$member = DB::table('fm_member')->where('userid', 'dormancy_tester')->first();

// Ensure admin exists
$admin = DB::table('fm_manager')->where('manager_id', 'admin')->first();
if (!$admin) {
    DB::table('fm_manager')->insert([
        'manager_id' => 'admin',
        'mpasswd' => bcrypt('password123!'),
        'mname' => 'Admin User',
        'regist_date' => now(),
    ]);
    echo "Created admin user.\n";
} else {
    DB::table('fm_manager')->where('manager_id', 'admin')->update([
         'mpasswd' => bcrypt('password123!')
    ]);
    echo "Reset admin password.\n";
}

if (!$member) {
    DB::table('fm_member')->insert([
        'userid' => 'dormancy_tester',
        'password' => bcrypt('password123!'),
        'user_name' => '휴면테스터',
        'email' => DB::raw("HEX(AES_ENCRYPT('dormancy@example.com', 'FirstMall'))"),
        'status' => 'done',
        'group_seq' => 1,
        'regist_date' => now(),
        'lastlogin_date' => now(),
    ]);
    echo "Created active testing member: dormancy_tester\n";
} else {
    // Reset status just in case
    DB::table('fm_member')->where('userid', 'dormancy_tester')->update([
        'status' => 'done',
        'user_name' => '휴면테스터',
    ]);
    // Force delete from DR tables to be safe
    DB::table('fm_member_dr')->where('userid', 'dormancy_tester')->delete();
    DB::table('fm_dormancy_log')->where('member_seq', $member->member_seq)->delete();
    
    echo "Reset testing member to active: dormancy_tester\n";
}

echo "Done.\n";
