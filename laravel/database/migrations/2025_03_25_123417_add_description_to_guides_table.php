<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDescriptionToGuidesTable extends Migration
{
    public function up()
    {
        Schema::table('guides', function (Blueprint $table) {
            $table->text('description')->nullable()->after('title');
        });
    }

    public function down()
    {
        Schema::table('guides', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
}