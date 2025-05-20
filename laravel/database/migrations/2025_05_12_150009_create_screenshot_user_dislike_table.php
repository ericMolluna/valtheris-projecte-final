<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScreenshotUserDislikeTable extends Migration
{
    public function up()
    {
        Schema::create('screenshot_user_dislike', function (Blueprint $table) {
            $table->id();
            $table->foreignId('screenshot_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('screenshot_user_dislike');
    }
}
