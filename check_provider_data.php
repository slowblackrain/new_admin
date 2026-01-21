<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$provider = DB::table('fm_provider')->where('provider_id', 'dometopia')->first();

if ($provider) {
    echo "Provider Found: " . $provider->provider_id . "\n";
    echo "Stored Hash: " . $provider->provider_passwd . "\n";
} else {
    echo "Provider 'dometopia' NOT FOUND.\n";
    // List some providers to help debugging
    $providers = DB::table('fm_provider')->limit(5)->get();
    echo "Available providers:\n";
    foreach($providers as $p) {
        echo "- " . $p->provider_id . "\n";
    }
}
