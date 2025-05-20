<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MigrateCommentsDataForPolymorphicRelationship extends Migration
{
    public function up()
    {
        // Add temporary polymorphic columns without dropping screenshot_id yet
        DB::statement('ALTER TABLE comments ADD commentable_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE comments ADD commentable_type VARCHAR(255) NULL');

        // Migrate existing data
        DB::table('comments')
            ->whereNotNull('screenshot_id')
            ->update([
                'commentable_id' => DB::raw('screenshot_id'),
                'commentable_type' => 'App\\Models\\Screenshot',
            ]);
    }

    public function down()
    {
        // Remove temporary polymorphic columns
        DB::statement('ALTER TABLE comments DROP COLUMN commentable_id');
        DB::statement('ALTER TABLE comments DROP COLUMN commentable_type');
    }
}