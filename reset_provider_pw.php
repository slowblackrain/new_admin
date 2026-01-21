<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$newHash = md5('dometopia');
DB::table('fm_provider')->where('provider_id', 'dometopia')->update(['provider_passwd' => $newHash]);

echo "Updated password for 'dometopia' to md5('dometopia').\n";
