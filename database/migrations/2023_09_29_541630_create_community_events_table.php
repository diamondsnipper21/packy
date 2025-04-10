<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommunityEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('community_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('community_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('start_at')->nullable();
            $table->string('end_at')->nullable();
            $table->string('duration')->nullable();
            $table->string('repeat_every')->nullable();
            $table->string('repeat_on')->nullable();
            $table->string('timezone');
            $table->string('media');
            $table->string('link');
            $table->string('type')->nullable();
            $table->string('location')->nullable();
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
        Schema::dropIfExists('community_events');
    }
}
