<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fm_scm_order_fail_log', function (Blueprint $table) {
            $table->id('seq');
            $table->unsignedInteger('goods_seq');
            $table->unsignedInteger('provider_seq');
            $table->unsignedInteger('sorder_seq')->comment('Batch ID');
            $table->text('fail_reason');
            $table->enum('is_checked', ['Y', 'N'])->default('N');
            $table->dateTime('regist_date')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fm_scm_order_fail_log');
    }
};
