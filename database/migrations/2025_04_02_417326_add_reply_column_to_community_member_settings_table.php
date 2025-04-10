<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReplyColumnToCommunityMemberSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('community_member_settings', function (Blueprint $table) {
            $table->boolean('reply')->default(1)->after('likes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('community_member_settings', function (Blueprint $table) {
            $table->dropColumn('reply');
        });
    }
}
