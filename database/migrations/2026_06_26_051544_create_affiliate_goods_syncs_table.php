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
        Schema::create('affiliate_goods_syncs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_site_id')->constrained()->onDelete('cascade');
            $table->string('goods_seq')->comment('도매토피아 상품 번호')->index();
            $table->string('affiliate_goods_code')->nullable()->comment('제휴사 측 상품 코드');
            $table->enum('sync_status', ['pending', 'success', 'failed'])->default('pending')->index();
            $table->text('error_message')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_goods_syncs');
    }
};
