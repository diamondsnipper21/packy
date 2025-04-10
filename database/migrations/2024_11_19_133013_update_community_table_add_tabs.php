<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCommunityTableAddTabs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('community', function (Blueprint $table) {
            $table->boolean('tab_classrooms')->default(true)->after('auto_post_approbation');
            $table->boolean('tab_calendar')->default(true)->after('tab_classrooms');
            $table->boolean('tab_leaderboard')->default(true)->after('tab_calendar');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('community', function (Blueprint $table) {
            $table->dropColumn('tab_classrooms');
            $table->dropColumn('tab_calendar');
            $table->dropColumn('tab_leaderboard');
        });
    }
}
