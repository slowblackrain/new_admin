<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Common\FmConfig;

echo "Checking FmConfig...\n";

// Test Create/Update
$cfg = FmConfig::firstOrNew(['codecd' => 'test_scm_config']);
$cfg->groupcd = 'test';
$cfg->value = json_encode(['foo' => 'bar', 'unit' => 10]);
$cfg->save();

echo "Saved test config.\n";

// Test Read
$read = FmConfig::find('test_scm_config');
if ($read) {
    echo "Read Success: " . $read->value . "\n";
    print_r(json_decode($read->value, true));
} else {
    echo "Read Failed!\n";
}
