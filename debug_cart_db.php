<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Cart;
use Illuminate\Support\Facades\DB;

// Show last 5 cart entries
$carts = DB::table('fm_cart')->orderBy('cart_seq', 'desc')->limit(5)->get();

echo "Last 5 Cart Entries:\n";
foreach ($carts as $c) {
    echo "Seq: {$c->cart_seq} | Member: {$c->member_seq} | Session: {$c->session_id} | Date: {$c->regist_date}\n";
}

echo "\nSession Config:\n";
echo "Driver: " . config('session.driver') . "\n";
echo "Active: " . (config('session.http_only') ? 'HttpOnly' : 'Not HttpOnly') . "\n";
