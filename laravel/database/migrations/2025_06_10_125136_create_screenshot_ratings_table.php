<?php

use Illuminate\Database\Migrations\Migration;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScreenshotRatingsTable extends Migration
{
    public function up()
    {
        Schema::create('screenshot_ratings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('screenshot_id');
            $table->unsignedTinyInteger('rating')->check('rating >= 1 AND rating <= 5');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('screenshot_id')->references('id')->on('screenshots')->onDelete('cascade');
            $table->unique(['user_id', 'screenshot_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('screenshot_ratings');
    }
}