<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVideosTable extends Migration
{
    public function up()
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('video_url');
            $table->string('thumbnail_url')->nullable();
            $table->text('description')->nullable();
            $table->integer('popularity')->default(0);
            $table->timestamps();
        });

        Schema::create('video_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('video_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('video_dislikes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('video_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('video_dislikes');
        Schema::dropIfExists('video_likes');
        Schema::dropIfExists('videos');
    }
}