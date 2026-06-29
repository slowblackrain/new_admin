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
        Schema::table('affiliate_settings', function (Blueprint $table) {
            $table->string('login_id')->nullable()->after('affiliate_site_id')->comment('제휴처 로그인 아이디');
            $table->string('login_password')->nullable()->after('login_id')->comment('제휴처 로그인 비밀번호');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('affiliate_settings', function (Blueprint $table) {
            $table->dropColumn(['login_id', 'login_password']);
        });
    }
};
