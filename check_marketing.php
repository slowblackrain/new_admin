<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

if (Schema::hasTable('fm_marketing_advertising')) {
    echo "Table Exists. Count: " . DB::table('fm_marketing_advertising')->count();
} else {
    echo "Table Does Not Exist";
}
