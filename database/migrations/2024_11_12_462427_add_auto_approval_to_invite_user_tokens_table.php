<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAutoApprovalToInviteUserTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invite_user_tokens', function (Blueprint $table) {
            $table->boolean('auto_approval')->default(0)->after('token');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invite_user_tokens', function (Blueprint $table) {
            $table->dropColumn('auto_approval');
        });
    }
}
