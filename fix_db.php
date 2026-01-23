<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

echo "Fixing Database...\n";

if (!Schema::hasTable('fm_member_emp')) {
    echo "Creating fm_member_emp...\n";
    Schema::dropIfExists('fm_member_emp');
    Schema::create('fm_member_emp', function (Blueprint $table) {
        $table->integer('member_seq')->primary();
        $table->char('emp_biz', 1)->default('N')->nullable();
        $table->string('emp_gubun')->nullable();
    });
    echo "Created fm_member_emp.\n";
} else {
    // Force recreate to be sure
    echo "Recreating fm_member_emp...\n";
    Schema::dropIfExists('fm_member_emp');
    Schema::create('fm_member_emp', function (Blueprint $table) {
        $table->integer('member_seq')->primary();
        $table->char('emp_biz', 1)->default('N')->nullable();
        $table->string('emp_gubun')->nullable();
    });
    echo "Recreated fm_member_emp.\n";
}

// Also check fm_manager just in case
if (!Schema::hasTable('fm_manager')) {
    echo "WARNING: fm_manager missing!\n";
}

// Check if we need to insert dummy goods icons if missing
if (Schema::hasTable('fm_goods_icon')) {
    if (DB::table('fm_goods_icon')->count() == 0) {
        // Optional: seed icons
    }
}
