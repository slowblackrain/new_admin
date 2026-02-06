<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "PHP Version: " . phpversion() . "\n";

$admin = \App\Models\Admin::where('manager_id', 'admin')->first();
if (!$admin) {
    echo "Admin user 'admin' NOT FOUND in DB.\n";
    exit;
}

echo "Stored Password (mpasswd): " . $admin->mpasswd . "\n";
echo "Length: " . strlen($admin->mpasswd) . "\n";

$input = '1111';
$bcryptPrefix = '$2y$';

$isBcrypt = str_starts_with($admin->mpasswd, $bcryptPrefix);
echo "str_starts_with(\$2y$): " . ($isBcrypt ? 'YES' : 'NO') . "\n";

if ($isBcrypt) {
    $check = \Illuminate\Support\Facades\Hash::check($input, $admin->mpasswd);
    echo "Hash::check('$input'): " . ($check ? 'PASS' : 'FAIL') . "\n";
} else {
    echo "Not a standard Bcrypt hash.\n";
}

$md5Input = md5($input);
echo "MD5('$input'): " . $md5Input . "\n";
$md5Check = ($md5Input === $admin->mpasswd);
echo "MD5 Check: " . ($md5Check ? 'PASS' : 'FAIL') . "\n";

// Force Update if needed
if (!$isBcrypt && !$md5Check) {
    echo "Attempting force update to Bcrypt...\n";
    $admin->mpasswd = \Illuminate\Support\Facades\Hash::make($input);
    $admin->save();
    echo "Update saved. New Hash: " . $admin->mpasswd . "\n";
}
