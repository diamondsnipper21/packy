<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAccessColumnToCommunityClassroomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('community_classrooms', function (Blueprint $table) {
            $table->string('access_type')->default('all')->after('order');
            $table->string('access_value')->after('access_type');
        });

        Schema::table('community_classrooms_sets', function (Blueprint $table) {
            $table->string('access_type')->default('all')->after('order');
            $table->string('access_value')->after('access_type');
        });

        Schema::table('community_classrooms_lessons', function (Blueprint $table) {
            $table->string('access_type')->default('all')->after('order');
            $table->string('access_value')->after('access_type');
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
            $table->dropColumn('access_value');
            $table->dropColumn('access_type');
        });

        Schema::table('community_classrooms_sets', function (Blueprint $table) {
            $table->dropColumn('access_value');
            $table->dropColumn('access_type');
        });

        Schema::table('community_classrooms_lessons', function (Blueprint $table) {
            $table->dropColumn('access_value');
            $table->dropColumn('access_type');
        });
    }
}
