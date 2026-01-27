<?php
use Illuminate\Support\Facades\DB;
use App\Models\Member;
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$userid = 'testuser';
// Create or Update
$member = Member::where('userid', $userid)->first();

$password = hash('sha256', '1234');

if ($member) {
    $member->password = $password;
    $member->save();
    echo "Updated password for {$userid}.\n";
} else {
    $newMember = Member::create([
        'userid' => $userid,
        'password' => $password,
        'user_name' => '테스터',
        'email' => 'test@example.com',
        'cellphone' => '010-1234-5678',
        'status' => 'done',
        'regist_date' => now(),
        'update_date' => now(),
        'lastlogin_date' => now(), // Prevent 'dormant' checks if any
    ]);
    echo "Created new user {$userid}.\n";
}
