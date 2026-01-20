<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Cart;
use Illuminate\Support\Facades\DB;

// Show last 5 cart items
$items = DB::table('fm_cart')->orderBy('regist_date', 'desc')->limit(5)->get();
print_r($items);
