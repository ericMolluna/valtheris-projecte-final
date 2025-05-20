<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLikesAndDislikesToGuidesTable extends Migration
{
    public function up()
    {
        Schema::table('guides', function (Blueprint $table) {
            $table->unsignedInteger('likes')->default(0);
            $table->unsignedInteger('dislikes')->default(0);
        });
    }

    public function down()
    {
        Schema::table('guides', function (Blueprint $table) {
            $table->dropColumn(['likes', 'dislikes']);
        });
    }
}