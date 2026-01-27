<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Session Config Details:\n";
echo "Secure Cookie: " . (config('session.secure') ? 'TRUE (Requires HTTPS)' : 'FALSE') . "\n";
echo "Domain: " . (config('session.domain') ?? 'NULL') . "\n";
echo "SameSite: " . (config('session.same_site') ?? 'NULL') . "\n";
echo "Driver: " . config('session.driver') . "\n";
echo "Path: " . config('session.path') . "\n";
