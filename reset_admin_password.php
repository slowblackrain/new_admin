<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

$adminId = 'admin';
$password = '1234';

// Check if admin exists
$admin = Admin::where('manager_id', $adminId)->first();

if (!$admin) {
    echo "Creating admin user...\n";
    DB::table('fm_manager')->insert([
        'manager_id' => $adminId,
        'mpasswd' => md5($password),
        'mname' => 'Super Admin',
        'mregdate' => now(),
    ]);
} else {
    echo "Updating admin password...\n";
    $admin->mpasswd = md5($password);
    $admin->save();
}

echo "Admin ($adminId) password set to: $password\n";
