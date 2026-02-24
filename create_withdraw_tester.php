<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$m = new \App\Models\Member();
$m->userid = 'test_withdraw';
$m->password = bcrypt('1111');
$m->user_name = 'Withdraw Tester';
$m->email = 'withdraw@test.com';
$m->phone = '02-111-2222';
$m->status = 'done';
$m->save();

echo "Member Created ID: " . $m->member_seq;
