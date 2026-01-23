<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

$admin = Admin::where('manager_id', 'admin')->first();

if (!$admin) {
    echo "User 'admin' not found. Checking for any manager...\n";
    $admin = Admin::first();
    if ($admin) {
        echo "Found manager: " . $admin->manager_id . "\n";
    } else {
        echo "No managers found. Creating 'admin'...\n";
        $admin = new Admin();
        $admin->manager_id = 'admin';
        $admin->mname = 'Super Admin';
        $admin->memail = 'admin@example.com';
        $admin->manager_yn = 'Y';
    }
} else {
    echo "Found user 'admin'.\n";
}

if ($admin) {
    $admin->mpasswd = Hash::make('1111');
    $admin->save();
    echo "Password for '" . $admin->manager_id . "' reset to '1111'.\n";
}
