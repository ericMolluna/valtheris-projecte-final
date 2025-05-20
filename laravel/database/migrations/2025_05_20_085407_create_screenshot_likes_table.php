<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScreenshotLikesTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('screenshot_likes')) {
            Schema::create('screenshot_likes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('screenshot_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->timestamps();

                $table->unique(['screenshot_id', 'user_id']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('screenshot_likes');
    }
}