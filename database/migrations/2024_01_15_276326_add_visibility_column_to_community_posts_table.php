<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVisibilityColumnToCommunityPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('community_posts', function (Blueprint $table) {
            $table->tinyInteger('visibility')->default('1')->after('likes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('community_posts', function (Blueprint $table) {
            $table->dropColumn('visibility');
        });
    }
}
