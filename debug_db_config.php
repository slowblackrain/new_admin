<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Dumping Database Config for 'mysql':\n";
$config = config('database.connections.mysql');
print_r($config);

echo "\nChecking Environment Variables:\n";
echo "DB_HOST: " . env('DB_HOST') . "\n";
echo "DB_READ_HOST: " . env('DB_READ_HOST') . "\n";
echo "DB_DATABASE: " . env('DB_DATABASE') . "\n";
