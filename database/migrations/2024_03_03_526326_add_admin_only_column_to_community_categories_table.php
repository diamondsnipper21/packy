<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdminOnlyColumnToCommunityCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('community_categories', function (Blueprint $table) {
            $table->tinyInteger('admin_only')->default('0')->after('description');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('community_categories', function (Blueprint $table) {
            $table->dropColumn('admin_only');
        });
    }
}
