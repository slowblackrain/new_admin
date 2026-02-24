<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$m = \App\Models\Member::where('userid', 'test_withdraw')->first();
if($m) {
    $m->password = hash('sha256', '1111');
    $m->save();
    echo "Password updated for Member ID: " . $m->member_seq;
} else {
    echo "Member not found.";
}
