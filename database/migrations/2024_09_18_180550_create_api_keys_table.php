<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApiKeysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('community_member_settings', 'api_key')) {
            Schema::table('community_member_settings', function (Blueprint $table) {
                $table->dropColumn('api_key');
            });
        }

        if (Schema::hasColumn('community_member_settings', 'access_limit')) {
            Schema::table('community_member_settings', function (Blueprint $table) {
                $table->dropColumn('access_limit');
            });
        }

        Schema::create('api_keys', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('community_id');
            $table->unsignedBigInteger('member_id');
            $table->string('api_key', 64);
            $table->integer('max_requests')->nullable();
            $table->timestamps();
            $table->foreign('community_id')->references('id')->on('community')->onDelete('cascade');
            $table->foreign('member_id')->references('id')->on('community_members')->onDelete('cascade');
            $table->unique('api_key');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('api_keys');
    }
}
