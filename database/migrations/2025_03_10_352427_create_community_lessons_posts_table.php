<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommunityLessonsPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('community_lessons_posts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('lesson_id');
            $table->unsignedBigInteger('post_id');
            $table->timestamps();

            // Foreign Keys
            $table->foreign('lesson_id')->references('id')->on('community_classrooms_lessons')->onDelete('cascade');
            $table->foreign('post_id')->references('id')->on('community_posts')->onDelete('cascade');
        });

        Schema::table('community_posts', function (Blueprint $table) {
            $table->dropForeign(['classroom_lesson_id']);
            $table->dropColumn('classroom_lesson_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('community_posts', function (Blueprint $table) {
            $table->unsignedBigInteger('classroom_lesson_id')->nullable()->after('community_id');
            $table->foreign('classroom_lesson_id')->references('id')->on('community_classrooms_lessons')->onDelete('cascade');
        });

        Schema::dropIfExists('community_lessons_posts');
    }
}
