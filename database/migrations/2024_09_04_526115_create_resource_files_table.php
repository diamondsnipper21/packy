<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResourceFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('resource_files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('uuid');
            $table->string('mime_type');
            $table->integer('size');
            $table->string('type');
            $table->string('url', 1024);
            $table->string('file_name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('resource_files');
    }
}
