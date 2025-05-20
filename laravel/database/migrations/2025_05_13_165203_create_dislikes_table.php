<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDislikesTable extends Migration
{
    public function up()
    {
        Schema::create('dislikes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->unique(['video_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('dislikes');
    }
}