<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$admin = Illuminate\Support\Facades\DB::table('fm_member')->where('level', 10)->orWhere('userid', 'admin')->first();
echo "Admin ID for testing: " . ($admin ? $admin->id ?? $admin->member_seq ?? $admin->userid : "None");
