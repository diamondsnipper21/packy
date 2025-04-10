<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class ChangeLevelDefaultValueAtCommunityHasMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('community_has_members', function (Blueprint $table) {
            $table->integer('level')->default(1)->change();
        });

        Artisan::call('command:update-level-default-value');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('community_has_members', function (Blueprint $table) {
            //
        });
    }
}
