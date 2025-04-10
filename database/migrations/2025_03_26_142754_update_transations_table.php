<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTransationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_plans_transactions', function (Blueprint $table) {
            $table->string('charge')->nullable()->change();
        });

        Schema::table('community_member_subscriptions_transactions', function (Blueprint $table) {
            $table->string('charge')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_plans_transactions', function (Blueprint $table) {
            $table->string('charge')->nullable(false)->change();
        });

        Schema::table('community_member_subscriptions_transactions', function (Blueprint $table) {
            $table->string('charge')->nullable(false)->change();
        });
    }
}
