<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommunityClassroomsLessonsCompletedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('community_classrooms_lessons_completed', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('community_id');
            $table->unsignedBigInteger('member_id');
            $table->unsignedBigInteger('classroom_id');
            $table->unsignedBigInteger('lesson_id');
            $table->timestamp('completed_at')->useCurrent();
            $table->timestamps();
            $table->foreign('community_id')->references('id')->on('community')->onDelete('cascade');
            $table->foreign('member_id')->references('id')->on('community_members')->onDelete('cascade');
            $table->foreign('classroom_id')->references('id')->on('community_classrooms')->onDelete('cascade');
            $table->foreign('lesson_id')->references('id')->on('community_classrooms_lessons')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('community_classrooms_lessons_completed');
    }
}
