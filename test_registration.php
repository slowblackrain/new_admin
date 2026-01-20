<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Member;
use Illuminate\Support\Facades\DB;

try {
    DB::beginTransaction();
    echo "Testing Member Creation...\n";

    $data = [
        'userid' => 'test_reg_' . time(),
        'password' => hash('sha256', 'password123'),
        'user_name' => '테스트유저',
        'nickname' => '테스트유저',
        'email' => 'test_' . time() . '@example.com',
        'phone' => '',
        'cellphone' => '010-0000-0000',
        'zipcode' => '',
        'address' => '',
        'address_street' => '',
        'address_detail' => '',
        'regist_date' => now(),
        'update_date' => now(),
        'status' => 'done',
        'gubun_seq' => 1,
        'group_seq' => 1,
        'group_set_date' => '1000-01-01 00:00:00',
        'rute' => 'none',
        'mailing' => 'n',
        'sms' => 'n',
        'sex' => 'male',
        'birthday' => '1000-01-01',
        'anniversary' => '',
        'lastlogin_date' => now(),
        'grade_update_date' => '1000-01-01 00:00:00',
        'marketplace' => '',
        'account_cnt' => 0,
        'Personal_ccn' => '',
    ];

    $member = Member::create($data);
    echo "Success! Member created with ID: " . $member->id . "\n";

    // Cleanup
    DB::rollBack();
    echo "Rolled back transaction (Test data removed).\n";

} catch (\Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    DB::rollBack();
}
