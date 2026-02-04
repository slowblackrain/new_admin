<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$providers = Illuminate\Support\Facades\DB::table('fm_provider')
    ->where('provider_id', 'like', '%mks%')
    ->orWhere('provider_name', 'like', '%mks%')
    ->get();

echo "Providers matching 'mks':\n";
foreach ($providers as $p) {
    echo "ID: {$p->provider_id}, Name: {$p->provider_name}, Seq: {$p->provider_seq}\n";
}
