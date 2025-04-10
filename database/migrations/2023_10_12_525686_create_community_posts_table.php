<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommunityPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('community_posts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('community_id');
            $table->unsignedBigInteger('classroom_lesson_id')->nullable();
            $table->unsignedBigInteger('member_id');
            $table->string('title');
            $table->text('content')->nullable();
            $table->string('path')->nullable();
            $table->boolean('pinned')->default(0);
            $table->string('category');
            $table->text('likes')->nullable();
            $table->timestamps();
            $table->foreign('community_id')->references('id')->on('community')->onDelete('cascade');
            $table->foreign('classroom_lesson_id')->references('id')->on('community_classrooms_lessons')->onDelete('cascade');
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
        Schema::dropIfExists('community_posts');
    }
}
