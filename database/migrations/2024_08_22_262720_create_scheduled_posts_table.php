<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScheduledPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scheduled_posts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('community_id');
            $table->unsignedBigInteger('classroom_lesson_id')->nullable();
            $table->unsignedBigInteger('member_id');
            $table->string('title');
            $table->text('content')->nullable();
            $table->string('path')->nullable();
            $table->boolean('pinned')->default(0);
            $table->unsignedBigInteger('category_id')->default(0);
            $table->tinyInteger('visibility')->default('1');
            $table->tinyInteger('disable_comment')->default('0');
            $table->timestamp('publish_at')->nullable();
            $table->string('publish_timezone')->nullable();
            $table->string('repeat_end_at')->nullable();
            $table->string('repeat_every')->nullable();
            $table->string('repeat_on')->nullable();
            $table->unsignedBigInteger('origin_id')->default(0);
            $table->tinyInteger('send_notification')->default('0');
            $table->timestamps();
            $table->foreign('community_id')->references('id')->on('community')->onDelete('cascade');
            $table->foreign('classroom_lesson_id')->references('id')->on('community_classrooms_lessons')->onDelete('cascade');
            $table->foreign('member_id')->references('id')->on('community_members')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('community_categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('scheduled_posts');
    }
}
