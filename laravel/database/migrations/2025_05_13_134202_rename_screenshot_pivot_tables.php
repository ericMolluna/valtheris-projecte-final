<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class RenameScreenshotPivotTables extends Migration
{
    public function up()
    {
        Schema::rename('screenshot_user_like', 'screenshot_likes');
        Schema::rename('screenshot_user_dislike', 'screenshot_dislikes');
    }

    public function down()
    {
        Schema::rename('screenshot_likes', 'screenshot_user_like');
        Schema::rename('screenshot_dislikes', 'screenshot_user_dislike');
    }
}