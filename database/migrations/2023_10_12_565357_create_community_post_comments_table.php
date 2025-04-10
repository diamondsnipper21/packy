<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommunityPostCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('community_post_comments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('post_id');
            $table->unsignedBigInteger('member_id');
            $table->text('content')->nullable();
            $table->string('path')->nullable();
            $table->text('likes')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->timestamps();

            // Foreign Keys
            $table->foreign('post_id')->references('id')->on('community_posts')->onDelete('cascade');
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
        Schema::dropIfExists('community_post_comments');
    }
}
