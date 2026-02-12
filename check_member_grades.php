<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking Member Groups (fm_member_group):\n";

try {
    $groups = \Illuminate\Support\Facades\DB::table('fm_member_group')->get();
    foreach ($groups as $g) {
        print_r($g);
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
