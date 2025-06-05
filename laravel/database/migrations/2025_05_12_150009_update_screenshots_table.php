<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateScreenshotsTable extends Migration
{
    public function up()
    {
        Schema::table('screenshots', function (Blueprint $table) {
            $table->integer('likes')->default(0)->change();
            $table->integer('dislikes')->default(0)->change();
        });
    }

    public function down()
    {
        Schema::table('screenshots', function (Blueprint $table) {
            $table->dropColumn(['likes', 'dislikes']);
        });
    }
}