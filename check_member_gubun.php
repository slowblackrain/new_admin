<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking Member Gubun (fm_member_gubun) locally:\n";

try {
    $rows = \Illuminate\Support\Facades\DB::table('fm_member_gubun')->get();
    foreach ($rows as $r) {
        print_r($r);
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
