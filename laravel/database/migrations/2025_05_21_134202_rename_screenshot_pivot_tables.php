<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class RenameScreenshotPivotTables extends Migration
{
    public function up()
    {
        Schema::rename('screenshot_user_like', 'user_screenshot_likes'); // Use a different name
        Schema::rename('screenshot_user_dislike', 'user_screenshot_dislikes'); // Use a different name
    }

    public function down()
    {
        Schema::rename('user_screenshot_likes', 'screenshot_user_like');
        Schema::rename('user_screenshot_dislikes', 'screenshot_user_dislike');
    }
}
