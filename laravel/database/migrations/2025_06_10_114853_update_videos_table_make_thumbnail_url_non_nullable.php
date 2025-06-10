<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateVideosTableMakeThumbnailUrlNonNullable extends Migration
{
    public function up()
    {
        // Update NULL thumbnail_url to a default value
        DB::table('videos')->whereNull('thumbnail_url')->update(['thumbnail_url' => 'thumbnails/default.jpg']);

        // Make thumbnail_url non-nullable
        Schema::table('videos', function (Blueprint $table) {
            $table->string('thumbnail_url')->nullable(false)->change();
        });
    }

    public function down()
    {
        // Revert to nullable
        Schema::table('videos', function (Blueprint $table) {
            $table->string('thumbnail_url')->nullable()->change();
        });
    }
}