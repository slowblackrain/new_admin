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
        Schema::create('shipping_extra_costs', function (Blueprint $table) {
            $table->id();
            $table->string('zipcode_start', 5)->comment('시작 우편번호(5자리)');
            $table->string('zipcode_end', 5)->comment('끝 우편번호(5자리)');
            $table->integer('extra_cost')->comment('추가 배송비');
            $table->string('area_name')->nullable()->comment('지역 구분명(도서/산간/제주 등)');
            $table->timestamps();

            // 대량 주문건(장바구니) 배송비 책정 시 빠른 조회를 위한 복합 인덱스
            $table->index(['zipcode_start', 'zipcode_end']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_extra_costs');
    }
};
