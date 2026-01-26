<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // 1. Create fm_maker table
        if (!Schema::hasTable('fm_maker')) {
            Schema::create('fm_maker', function (Blueprint $table) {
                $table->increments('maker_seq');
                $table->string('maker_name', 100);
                $table->dateTime('regist_date')->useCurrent();
            });
            
            // Seed Sample Data
            DB::table('fm_maker')->insert([
                ['maker_name' => '삼성전자'],
                ['maker_name' => 'LG전자'],
                ['maker_name' => '나이키'],
                ['maker_name' => '애플'],
                ['maker_name' => '자체제작'],
            ]);
        }

        // 2. Create fm_origin table
        if (!Schema::hasTable('fm_origin')) {
            Schema::create('fm_origin', function (Blueprint $table) {
                $table->increments('origin_seq');
                $table->string('origin_name', 100);
                $table->string('origin_code', 10)->nullable();
                $table->dateTime('regist_date')->useCurrent();
            });

            // Seed Sample Data
            DB::table('fm_origin')->insert([
                ['origin_name' => '대한민국', 'origin_code' => 'KR'],
                ['origin_name' => '중국', 'origin_code' => 'CN'],
                ['origin_name' => '미국', 'origin_code' => 'US'],
                ['origin_name' => '일본', 'origin_code' => 'JP'],
                ['origin_name' => '베트남', 'origin_code' => 'VN'],
            ]);
        }

        // 3. Add Columns to fm_goods (Relational IDs)
        Schema::table('fm_goods', function (Blueprint $table) {
            // Relational Columns
            if (!Schema::hasColumn('fm_goods', 'maker_seq')) {
                $table->integer('maker_seq')->unsigned()->nullable()->after('goods_name');
            }
            if (!Schema::hasColumn('fm_goods', 'origin_seq')) {
                $table->integer('origin_seq')->unsigned()->nullable()->after('maker_seq');
            }
            
            // Standard Legacy "Model Name" Column (Usually just text)
            // User complained about "Arbitrary Change", implying we should check if `model` exists or `model_name` exists.
            // Standard FirstMall uses `model`.
            if (!Schema::hasColumn('fm_goods', 'model')) {
                $table->string('model', 255)->nullable()->after('origin_seq');
            }
        });
    }

    public function down()
    {
        Schema::table('fm_goods', function (Blueprint $table) {
            $table->dropColumn(['maker_seq', 'origin_seq', 'model']);
        });

        Schema::dropIfExists('fm_maker');
        Schema::dropIfExists('fm_origin');
    }
};
