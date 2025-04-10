<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class CreateRewardLevelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reward_levels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('community_id');
            $table->string('name');
            $table->integer('level_number');
            $table->integer('goal_point');
            $table->timestamps();

            $table->foreign('community_id')->references('id')->on('community')->onDelete('cascade');
            $table->index('community_id');
        });

        Artisan::call('command:migrate-community-level');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reward_levels');
    }
}
