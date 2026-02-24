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
        Schema::create('fm_mosms', function (Blueprint $table) {
            $table->id('idx');
            $table->string('pg_status', 10)->default('R')->comment('R:미매칭, M:매칭완료, D:제외');
            $table->string('in_bank', 50)->nullable()->comment('입금은행');
            $table->integer('in_price')->default(0)->comment('입금액');
            $table->string('in_name', 50)->nullable()->comment('입금자명');
            $table->text('memo')->nullable()->comment('관리자메모');
            $table->string('order_seq', 50)->nullable()->comment('매칭된주문번호');
            $table->dateTime('update_time')->useCurrent()->comment('업데이트시간');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fm_mosms');
    }
};
