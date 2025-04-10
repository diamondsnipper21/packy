<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexOrderColumnInClassroomTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('community_classrooms', function (Blueprint $table) {
            $table->index('order');
        });

        Schema::table('community_classrooms_lessons', function (Blueprint $table) {
            $table->index('order');
        });

        Schema::table('community_classrooms_sets', function (Blueprint $table) {
            $table->index('order');
        });

        Schema::table('community_posts', function (Blueprint $table) {
            $table->index('path');
            $table->index('visibility');
        });

        Schema::table('community_media', function (Blueprint $table) {
            $table->index(['owner', 'owner_id']);
        });

        Schema::table('community', function (Blueprint $table) {
            $table->index('url');
        });

        Schema::table('community_events', function (Blueprint $table) {
            $table->index('origin_id');
        });

        Schema::table('resource_files', function (Blueprint $table) {
            $table->index('uuid');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('community_classrooms', function (Blueprint $table) {
            $table->dropIndex(['order']);
        });

        Schema::table('community_classrooms_lessons', function (Blueprint $table) {
            $table->dropIndex(['order']);
        });

        Schema::table('community_classrooms_sets', function (Blueprint $table) {
            $table->dropIndex(['order']);
        });

        Schema::table('community_posts', function (Blueprint $table) {
            $table->dropIndex(['path']);
            $table->dropIndex(['visibility']);
        });

        Schema::table('community_media', function (Blueprint $table) {
            $table->dropIndex(['owner', 'owner_id']);
        });

        Schema::table('community', function (Blueprint $table) {
            $table->dropIndex(['url']);
        });

        Schema::table('community_events', function (Blueprint $table) {
            $table->dropIndex(['origin_id']);
        });

        Schema::table('resource_files', function (Blueprint $table) {
            $table->dropIndex(['uuid']);
        });

    }
}
