<?php

// database/migrations/2025_06_10_add_average_rating_to_screenshots_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAverageRatingToScreenshotsTable extends Migration
{
    public function up()
    {
        Schema::table('screenshots', function (Blueprint $table) {
            $table->decimal('average_rating', 3, 1)->nullable()->after('dislikes');
        });
    }

    public function down()
    {
        Schema::table('screenshots', function (Blueprint $table) {
            $table->dropColumn('average_rating');
        });
    }
}