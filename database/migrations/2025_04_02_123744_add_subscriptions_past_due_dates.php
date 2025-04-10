<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSubscriptionsPastDueDates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('community_plans', function (Blueprint $table) {
            $table->string('passed_due')->nullable()->default(null)->after('trial_end');
        });

        Schema::create('community_plans_reminders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plan_id');
            $table->unsignedBigInteger('community_id');
            $table->timestamps();

            $table->foreign('plan_id')->references('id')->on('community_plans')->onDelete('cascade');
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
        Schema::table('community_plans', function (Blueprint $table) {
            $table->dropColumn('passed_due');
            $table->dropColumn('email_reminders');
        });

        Schema::dropIfExists('community_plans_reminders');
    }
}
