<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

$userid = 'mypagetest';
$password = 'password';
$passwordHash = hash('sha256', $password);

// 1. Find or Create User
$member = \App\Models\Member::where('userid', $userid)->first();

if (!$member) {
    echo "Creating test member...\n";
    $member = \App\Models\Member::create([
        'userid' => $userid,
        'password' => $passwordHash,
        'user_name' => 'MyPageTestUser',
        'nickname' => 'Tester',
        'email' => 'mypagetest@example.com',
        'cellphone' => '010-1111-2222',
        
        // Default required fields (based on register_process)
        'phone' => '',
        'zipcode' => '',
        'address' => '',
        'address_street' => '',
        'address_detail' => '',
        'regist_date' => now(),
        'update_date' => now(),
        'status' => 'active', 
        'gubun_seq' => 1,
        'group_seq' => 1,
        'group_set_date' => '1000-01-01 00:00:00',
        'rute' => 'none',
        'mailing' => 'n',
        'sms' => 'n',
        'sex' => 'male',
        'birthday' => '1000-01-01',
        'lastlogin_date' => now(),
        'grade_update_date' => '1000-01-01 00:00:00',
        'account_cnt' => 0,
        'Personal_ccn' => '',
    ]);
    echo "Created Member Seq: " . $member->member_seq . "\n";
} else {
    echo "Found Member Seq: " . $member->member_seq . "\n";
    // Reset password just in case
    $member->password = $passwordHash;
    $member->save();
}

// 2. Update Order
$orderSeq = '2026012901305417531';
$order = \App\Models\Order::find($orderSeq);

if ($order) {
    $order->member_seq = $member->member_seq;
    $order->order_user_name = $member->user_name;
    $order->save();
    echo "Updated Order {$orderSeq} to Member Seq " . $member->member_seq . "\n";
} else {
    echo "Order {$orderSeq} not found.\n";
}
