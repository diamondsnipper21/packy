<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAutoPostApprobationColumnToCommunityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('community', function (Blueprint $table) {
            $table->tinyInteger('auto_post_approbation')->default('1')->after('last_sent_notification');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('community', function (Blueprint $table) {
            $table->dropColumn('auto_post_approbation');
        });
    }
}
