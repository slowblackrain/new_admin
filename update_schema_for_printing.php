<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Change input_form to varchar to allow 'select'
DB::statement("ALTER TABLE fm_goods_input MODIFY COLUMN input_form VARCHAR(20) NOT NULL DEFAULT 'text'");

// Change input_limit to TEXT to allow storing options
DB::statement("ALTER TABLE fm_goods_input MODIFY COLUMN input_limit TEXT");

echo "Schema updated.\n";
