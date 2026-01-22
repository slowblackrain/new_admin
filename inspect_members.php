<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = \Illuminate\Support\Facades\DB::table('fm_member')
    ->select('userid', 'email', 'cellphone')
    ->limit(5)
    ->get();

$key = 'OTgwNTc=';

foreach ($users as $u) {
    echo "User: " . $u->userid . "\n";
    echo "  Raw Email: " . substr($u->email, 0, 10) . "... (Len: " . strlen($u->email) . ")\n";
    echo "  Raw Cell:  " . $u->cellphone . " (Len: " . strlen($u->cellphone) . ")\n";
    
    $decEmail = \Illuminate\Support\Facades\DB::select("SELECT AES_DECRYPT(UNHEX(?), ?) as d", [$u->email, $key])[0]->d;
    echo "  Dec Email: " . ($decEmail ?: 'FAIL') . "\n";

    $decCell = \Illuminate\Support\Facades\DB::select("SELECT AES_DECRYPT(UNHEX(?), ?) as d", [$u->cellphone, $key])[0]->d;
    echo "  Dec Cell:  " . ($decCell ?: 'FAIL') . "\n";
    echo "---------------------------\n";
}
