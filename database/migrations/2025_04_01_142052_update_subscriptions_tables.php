<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades;

class UpdateSubscriptionsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Facades\DB::statement('ALTER TABLE `community_plans` ADD UNIQUE (st_subscription_id);');
        Facades\DB::statement('ALTER TABLE `user_plans_transactions` ADD UNIQUE (invoice);');
        Facades\DB::statement('ALTER TABLE `community_member_subscriptions` ADD UNIQUE (stripe_subscription_id);');
        Facades\DB::statement('ALTER TABLE `community_member_subscriptions_transactions` ADD UNIQUE (invoice);');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
