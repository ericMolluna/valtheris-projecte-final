<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdditionalFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('email');         // For storing avatar path
            $table->integer('inventory')->default(0)->after('avatar');    // For inventory stat
            $table->integer('videos')->default(0)->after('inventory');    // For videos stat
            $table->integer('workshopItems')->default(0)->after('videos'); // For workshop items stat
            $table->integer('artwork')->default(0)->after('workshopItems'); // For artwork stat
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['avatar', 'inventory', 'videos', 'workshopItems', 'artwork']);
        });
    }
}