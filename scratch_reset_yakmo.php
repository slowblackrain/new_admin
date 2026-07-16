<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$userId = 'yakmo';
$newPassword = '11dnjf7dlf!!';
$md5Password = md5($newPassword);

// Check fm_manager
$manager = DB::table('fm_manager')->where('manager_id', $userId)->first();
if ($manager) {
    echo "Found in fm_manager. Current pass: " . $manager->mpasswd . "\n";
    DB::table('fm_manager')->where('manager_id', $userId)->update(['mpasswd' => $md5Password]);
    echo "Updated in fm_manager to md5.\n";
}

// Check fm_member
$member = DB::table('fm_member')->where('m_id', $userId)->first();
if ($member) {
    echo "Found in fm_member. Current pass: " . $member->passwd . "\n";
    DB::table('fm_member')->where('m_id', $userId)->update(['passwd' => $md5Password]);
    echo "Updated in fm_member to md5.\n";
}

// Check fm_provider
$provider = DB::table('fm_provider')->where('provider_id', $userId)->first();
if ($provider) {
    echo "Found in fm_provider. Current pass: " . $provider->passwd . "\n";
    DB::table('fm_provider')->where('provider_id', $userId)->update(['passwd' => $md5Password]);
    echo "Updated in fm_provider to md5.\n";
}

echo "Done.\n";
