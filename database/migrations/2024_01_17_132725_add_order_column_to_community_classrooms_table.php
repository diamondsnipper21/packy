<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrderColumnToCommunityClassroomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('community_classrooms', function (Blueprint $table) {
            $table->unsignedBigInteger('order')->after('publish');
        });

        Schema::table('community_classrooms_sets', function (Blueprint $table) {
            $table->unsignedBigInteger('order')->after('publish');
        });

        Schema::table('community_classrooms_lessons', function (Blueprint $table) {
            $table->unsignedBigInteger('order')->after('publish');
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
            $table->dropColumn('order');
        });

        Schema::table('community_classrooms_sets', function (Blueprint $table) {
            $table->dropColumn('order');
        });

        Schema::table('community_classrooms_lessons', function (Blueprint $table) {
            $table->dropColumn('order');
        });
    }
}
