<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommunityPlanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('community_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('community_id');
            $table->integer('payment_method_id');
            $table->string('st_subscription_id');
            $table->string('status', 10);
            $table->unsignedBigInteger('current_period_start');
            $table->unsignedBigInteger('current_period_end');
            $table->unsignedBigInteger('trial_start');
            $table->unsignedBigInteger('trial_end');
            $table->integer('amount');
            $table->string('currency', 5);
            $table->timestamps();
            $table->foreign('community_id')->references('id')->on('community')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('community_plans');
    }
}
