<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImageToGuidesTable extends Migration
{
    public function up()
    {
        Schema::table('guides', function (Blueprint $table) {
            $table->string('image')->nullable()->after('description'); // Add the image column
        });
    }

    public function down()
    {
        Schema::table('guides', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }
}
