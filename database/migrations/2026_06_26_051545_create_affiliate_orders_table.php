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
        Schema::create('affiliate_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_site_id')->constrained()->onDelete('cascade');
            $table->string('affiliate_order_id')->comment('제휴사 측 주문 번호');
            $table->string('order_seq')->nullable()->comment('도매토피아 주문 테이블 번호 (매핑 완료시)');
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->text('shipping_address')->nullable();
            $table->text('order_items')->nullable()->comment('주문 상품 내역 (JSON)');
            $table->enum('status', ['collected', 'mapped', 'failed'])->default('collected')->comment('주문 수집 상태');
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_orders');
    }
};
