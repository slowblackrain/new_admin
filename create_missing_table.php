<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

if (!Schema::hasTable('fm_member_emp')) {
    echo "Creating fm_member_emp table...\n";
    Schema::create('fm_member_emp', function (Blueprint $table) {
        $table->integer('member_seq')->index();
        $table->string('emp_biz', 1)->nullable();
        // Add other columns if needed
    });
    echo "Table created successfully.\n";
} else {
    echo "Table fm_member_emp already exists.\n";
}
