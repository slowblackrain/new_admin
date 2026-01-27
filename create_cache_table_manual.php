<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

echo "Creating cache table...\n";
if (!Schema::hasTable('cache')) {
    Schema::create('cache', function (Blueprint $table) {
        $table->string('key')->primary();
        $table->mediumText('value');
        $table->integer('expiration');
    });
    echo "Cache table created.\n";
} else {
    echo "Cache table already exists.\n";
}

if (!Schema::hasTable('cache_locks')) {
    Schema::create('cache_locks', function (Blueprint $table) {
        $table->string('key')->primary();
        $table->string('owner');
        $table->integer('expiration');
    });
    echo "Cache locks table created.\n";
} else {
    echo "Cache locks table already exists.\n";
}
