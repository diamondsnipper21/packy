<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDisableCommentColumnToCommunityPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('community_posts', function (Blueprint $table) {
            $table->tinyInteger('disable_comment')->default('0')->after('visibility');
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
            $table->dropColumn('disable_comment');
        });
    }
}
