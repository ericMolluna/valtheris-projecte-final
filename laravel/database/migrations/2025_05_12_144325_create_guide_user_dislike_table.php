<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGuideUserDislikeTable extends Migration
{
    public function up()
    {
        Schema::create('guide_user_dislike', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guide_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('guide_user_dislike');
    }
}