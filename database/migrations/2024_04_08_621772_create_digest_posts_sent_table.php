<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDigestPostsSentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('digest_posts_sent', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('community_id');
            $table->unsignedBigInteger('member_id');
            $table->unsignedBigInteger('post_id');
            $table->timestamps();

            $table->foreign('community_id')->references('id')->on('community')->onDelete('cascade');
            $table->foreign('member_id')->references('id')->on('community_members')->onDelete('cascade');
            $table->foreign('post_id')->references('id')->on('community_posts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('digest_posts_sent');
    }
}
