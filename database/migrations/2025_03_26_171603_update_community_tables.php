<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades;

class UpdateCommunityTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('community', function (Blueprint $table) {
            $table->string('url')->nullable()->change();
        });
        Schema::table('community_plans', function (Blueprint $table) {
            $table->string('status', 50)->change();
        });

        Facades\DB::statement('ALTER TABLE `community` ADD UNIQUE (url);');
        Facades\DB::statement('ALTER TABLE `payment_methods_marketplace` ADD UNIQUE (user_id, payment_method_id);');
        Facades\DB::statement('ALTER TABLE `user_payment_methods` ADD UNIQUE (user_id, payment_method_id);');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('community', function (Blueprint $table) {
            $table->string('url')->nullable(false)->change();
        });
        Schema::table('community_plans', function (Blueprint $table) {
            $table->string('status', 10)->change();
        });
    }
}
