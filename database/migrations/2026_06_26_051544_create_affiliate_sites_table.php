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
        Schema::create('affiliate_sites', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('제휴사명 (예: 오너클랜, 대한판촉)');
            $table->string('domain')->nullable()->comment('제휴사 도메인');
            $table->enum('sync_type', ['api', 'scraping'])->default('api')->comment('연동 방식');
            $table->string('api_key')->nullable()->comment('API 연동 키');
            $table->string('login_id')->nullable()->comment('스크래핑용 로그인 아이디');
            $table->string('login_password')->nullable()->comment('스크래핑용 로그인 비밀번호');
            $table->boolean('is_active')->default(true)->comment('사용 여부');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_sites');
    }
};
