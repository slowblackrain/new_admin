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
        Schema::create('affiliate_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_site_id')->constrained('affiliate_sites')->onDelete('cascade');
            $table->decimal('margin_rate', 5, 2)->default(0.00)->comment('판매가 책정 마진율 (%)');
            $table->decimal('shipping_fee', 10, 2)->default(0.00)->comment('기본 배송비');
            $table->timestamps();
            
            $table->unique('affiliate_site_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_settings');
    }
};
