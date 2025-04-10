<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommunityHasMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('community_has_members', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('community_id');
            $table->unsignedBigInteger('member_id');
            $table->tinyInteger('access')->default('0');
            $table->timestamps();
            $table->foreign('community_id')->references('id')->on('community')->onDelete('cascade');
            $table->foreign('member_id')->references('id')->on('community_members')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('community_has_members');
    }
}
