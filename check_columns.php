<?php
// Load Laravel
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Check fm_member columns
echo "<h1>fm_member Columns</h1>";
$member = \Illuminate\Support\Facades\DB::table('fm_member')->first();
echo "<pre>";
print_r(array_keys((array)$member));
echo "</pre>";

// Check fm_provider (Seller) columns
echo "<h1>fm_provider Columns</h1>";
$provider = \Illuminate\Support\Facades\DB::table('fm_provider')->first();
echo "<pre>";
print_r(array_keys((array)$provider));
echo "</pre>";
