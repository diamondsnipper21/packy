<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class AddLevelInCommunityClassroomSetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('community_classrooms_sets', function (Blueprint $table) {
            $table->integer('level')->default(1);
        });
        Artisan::call('command:migrate-update-level-props');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('community_classrooms_sets', function (Blueprint $table) {
            $table->dropColumn('level');
        });
    }
}
