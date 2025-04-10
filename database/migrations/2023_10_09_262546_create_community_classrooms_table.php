<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommunityClassroomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('community_classrooms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('community_id');
            $table->string('title');
            $table->text('content')->nullable();
            $table->string('photo');
            $table->string('media');
            $table->tinyInteger('publish')->default('0');
            $table->timestamps();
            $table->foreign('community_id')->references('id')->on('community')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('community_classrooms');
    }
}
