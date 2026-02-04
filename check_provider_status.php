<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$provider = DB::table('fm_provider')->where('provider_seq', 1)->first();

if ($provider) {
    echo "Provider 1 Status: " . $provider->provider_status . "\n";
    if ($provider->provider_status != 'Y') {
        DB::table('fm_provider')->where('provider_seq', 1)->update(['provider_status' => 'Y']);
        echo "Updated Provider 1 to Status Y.\n";
    }
} else {
    echo "Provider 1 NOT FOUND. Creating dummy provider.\n";
    DB::table('fm_provider')->insert([
        'provider_seq' => 1,
        'provider_id' => 'admin',
        'provider_name' => 'Main SCM',
        'provider_status' => 'Y',
        'regdate' => now()
    ]);
    echo "Created Provider 1.\n";
}
