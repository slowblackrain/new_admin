<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\DB;

$providers = DB::table('fm_provider')->get();
foreach($providers as $p) {
    echo "ID: " . $p->provider_id . " / Name: " . $p->provider_name . " / User: " . $p->userid . "\n";
}
