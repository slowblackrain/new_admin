<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ids = \App\Models\BoardManager::pluck('id');
echo "Board IDs found: " . implode(', ', $ids->toArray()) . PHP_EOL;
