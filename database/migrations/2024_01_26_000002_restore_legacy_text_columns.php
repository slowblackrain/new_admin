<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('fm_goods', function (Blueprint $table) {
            // Restore legacy text columns identified in Frontend Controller
            
            if (!Schema::hasColumn('fm_goods', 'maker_name')) {
                $table->string('maker_name')->nullable()->after('goods_name'); 
            }
            if (!Schema::hasColumn('fm_goods', 'origin_name')) {
                $table->string('origin_name')->nullable()->after('maker_name');
            }
            if (!Schema::hasColumn('fm_goods', 'model')) {
                $table->string('model')->nullable()->after('origin_name');
            }
        });
    }

    public function down()
    {
        Schema::table('fm_goods', function (Blueprint $table) {
            $table->dropColumn(['maker_name', 'origin_name', 'model']);
        });
    }
};
