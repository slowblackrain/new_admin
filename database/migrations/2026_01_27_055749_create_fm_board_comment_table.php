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
        if (!Schema::hasTable('fm_board_comment')) {
            Schema::create('fm_board_comment', function (Blueprint $table) {
                $table->increments('seq');
                $table->integer('parent')->default(0)->index();
                $table->string('boardid', 50)->nullable();
                $table->integer('mseq')->default(0);
                $table->string('mid', 60)->nullable();
                $table->string('name', 50)->nullable();
                $table->string('pw', 100)->nullable();
                $table->text('content')->nullable();
                $table->string('ip', 15)->nullable();
                $table->dateTime('r_date')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fm_board_comment');
    }
};
