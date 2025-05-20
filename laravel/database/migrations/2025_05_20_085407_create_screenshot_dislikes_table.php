<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScreenshotDislikesTable extends Migration
{
    public function up()
    {
        // Check if table doesn't exist before creating
        if (!Schema::hasTable('screenshot_dislikes')) {
            Schema::create('screenshot_dislikes', function (Blueprint $table) {
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
        Schema::dropIfExists('screenshot_dislikes');
    }
}