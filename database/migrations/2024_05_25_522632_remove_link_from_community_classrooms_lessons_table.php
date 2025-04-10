<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveLinkFromCommunityClassroomsLessonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('community_classrooms_lessons', function (Blueprint $table) {
            $table->dropColumn('link');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('community_classrooms_lessons', function (Blueprint $table) {
            $table->string('link')->nullable()->after('discuss');
        });
    }
}
