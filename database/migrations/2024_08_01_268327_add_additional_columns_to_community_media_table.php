<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdditionalColumnsToCommunityMediaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('community_media', function (Blueprint $table) {
            $table->string('ext')->nullable()->after('path');
            $table->string('filename')->nullable()->after('ext');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('community_media', function (Blueprint $table) {
            $table->dropColumn('ext');
            $table->dropColumn('filename');
        });
    }
}
