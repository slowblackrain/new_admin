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
        if (!Schema::hasTable('fm_log_message')) {
            Schema::create('fm_log_message', function (Blueprint $table) {
                $table->id('log_seq');
                $table->string('temp_code')->nullable();
                $table->string('call_number')->nullable();
                $table->text('arr_msg')->nullable();
                $table->dateTime('regist_date')->nullable();
            });
        }

        if (!Schema::hasTable('fm_log_sms')) {
            Schema::create('fm_log_sms', function (Blueprint $table) {
                $table->id('log_seq');
                $table->string('result_cd')->nullable();
                $table->string('result_no')->nullable();
                $table->text('result_msg')->nullable();
                $table->text('msg')->nullable();
                $table->string('mtype')->nullable();
                $table->integer('sender')->default(0);
                $table->string('call_number')->nullable();
                $table->dateTime('regist_date')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fm_log_message');
        Schema::dropIfExists('fm_log_sms');
    }
};
