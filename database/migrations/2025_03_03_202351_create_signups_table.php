<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSignupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('signups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('community_id')->unsigned()->nullable();
            $table->unsignedBigInteger('user_id');
            $table->string('origin')->nullable();
            $table->string('ip');
            $table->string('fbclid')->nullable();
            $table->string('gclid')->nullable();
            $table->timestamp('date');
            $table->timestamps();

            $table->foreign('community_id')->references('id')->on('community')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('signups');
    }
}
