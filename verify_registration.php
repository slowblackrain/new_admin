try {
echo "=== Registration Verification ===\n";
$testUser = 'test_reg_' . rand(1000,9999);

echo "Attempting to register user: $testUser\n";

$passwordHash = hash('sha256', '1234');

$member = App\Models\Member::create([
'userid' => $testUser,
'password' => $passwordHash,
'user_name' => 'Test User',
'nickname' => 'Test User',
'email' => $testUser . '@example.com',
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
]);

if ($member && $member->exists) {
echo "Registration SUCCESS: Created member " . $member->userid . " (seq: " . $member->member_seq . ")\n";
} else {
echo "Registration FAILED.\n";
}

} catch (\Exception $e) {
echo "ERROR: " . $e->getMessage() . "\n";
}