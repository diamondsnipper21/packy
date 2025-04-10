<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAccessColumnToCommunityEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('community_events', function (Blueprint $table) {
            $table->string('access_type')->default('all')->after('location');
            $table->string('access_value')->nullable()->after('access_type');
            $table->integer('level')->default(1)->after('access_value');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('community_events', function (Blueprint $table) {
            $table->dropColumn('level');
            $table->dropColumn('access_value');
            $table->dropColumn('access_type');
        });
    }
}
