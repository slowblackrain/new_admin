<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$admin = \App\Models\Admin::where('manager_id', 'admin')->first();
if ($admin) {
    $admin->mpasswd = md5('dometopia!');
    $admin->save();
    echo "Admin password updated to md5('dometopia!')\n";
} else {
    echo "Admin not found\n";
}
