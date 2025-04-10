<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommunityClassroomsLessonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('community_classrooms_lessons', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('classroom_id');
            $table->unsignedBigInteger('set_id')->nullable();
            $table->string('title');
            $table->text('content')->nullable();
            $table->string('media');
            $table->text('action_items')->nullable();
            $table->text('transcript')->nullable();
            $table->tinyInteger('discuss')->default('0');
            $table->string('link')->nullable();
            $table->tinyInteger('publish')->default('0');
            $table->timestamps();
            $table->foreign('classroom_id')->references('id')->on('community_classrooms')->onDelete('cascade');
            $table->foreign('set_id')->references('id')->on('community_classrooms_sets')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('community_classrooms_lessons');
    }
}
