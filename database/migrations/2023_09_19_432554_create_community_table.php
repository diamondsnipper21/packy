<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommunityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('community', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('member_id');
            $table->string('name');
            $table->string('privacy')->default('public');
            $table->tinyInteger('owner_show')->default('1');
            $table->text('summary_description')->nullable();
            $table->text('description')->nullable();
            $table->string('summary_photo')->nullable();
            $table->string('photo')->nullable();
            $table->string('video')->nullable();
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            $table->string('url')->nullable();
            $table->timestamp('last_sent_notification')->nullable();
            $table->timestamps();
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
        Schema::dropIfExists('community');
    }
}
