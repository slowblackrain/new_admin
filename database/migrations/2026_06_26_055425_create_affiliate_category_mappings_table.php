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
        Schema::create('affiliate_category_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_site_id')->constrained('affiliate_sites')->onDelete('cascade');
            $table->string('dometopia_category_code', 50)->comment('도매토피아 카테고리 코드');
            $table->string('affiliate_category_code')->nullable()->comment('제휴처 카테고리 코드 (예: sel_ca1 값)');
            $table->string('affiliate_category_name')->nullable()->comment('제휴처 카테고리 명');
            $table->timestamps();
            
            $table->unique(['affiliate_site_id', 'dometopia_category_code'], 'unique_mapping');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_category_mappings');
    }
};
