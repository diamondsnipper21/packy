<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeTrialColumnsInCommunityPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('community_plans', function (Blueprint $table) {
            $table->unsignedBigInteger('trial_start')->nullable()->change();
            $table->unsignedBigInteger('trial_end')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('community_plans', function (Blueprint $table) {
            $table->unsignedBigInteger('trial_start')->nullable(false)->change();
            $table->unsignedBigInteger('trial_end')->nullable(false)->change();
        });
    }
}
