<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class AddPointsColumnsInHasMembers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('community_has_members', function (Blueprint $table) {
            $table->integer('monthly_point')->default(0);
            $table->integer('weekly_point')->default(0);
            $table->integer('point')->default(0);
            $table->integer('level')->default(0);
        });

        Artisan::call('command:migrate-initial-user-points');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('community_has_members', function (Blueprint $table) {
            $table->dropColumn('monthly_point');
            $table->dropColumn('weekly_point');
            $table->dropColumn('point');
            $table->dropColumn('level');
        });
    }
}
