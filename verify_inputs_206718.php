<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\GoodsInput;

$inputs = GoodsInput::where('goods_seq', 206718)->get();
foreach ($inputs as $input) {
    echo "ID: {$input->input_seq}, Name: {$input->input_name}, Form: {$input->input_form}, Limit: " . substr($input->input_limit, 0, 50) . "...\n";
}
