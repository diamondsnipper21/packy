<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommunityClassroomsSetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('community_classrooms_sets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('classroom_id');
            $table->string('name');
            $table->tinyInteger('publish')->default('0');
            $table->timestamps();
            $table->foreign('classroom_id')->references('id')->on('community_classrooms')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('community_classrooms_sets');
    }
}
