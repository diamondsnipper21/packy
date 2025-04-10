<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class RefactorExtensionTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('auto_dms');

        Schema::dropIfExists('auto_dm_templates');

        Schema::dropIfExists('extensions');

        Schema::create('extensions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type')->nullable();
            $table->timestamps();
        });

        Schema::create('community_extensions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('community_id');
            $table->unsignedBigInteger('extension_id');
            $table->boolean('active')->default(0);
            $table->timestamps();

            $table->foreign('community_id')->references('id')->on('community')->onDelete('cascade');
            $table->index('community_id');
            $table->foreign('extension_id')->references('id')->on('extensions')->onDelete('cascade');
        });

        Schema::create('auto_dm_templates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('community_id');
            $table->unsignedBigInteger('extension_id');
            $table->unsignedBigInteger('member_id');
            $table->text('body')->nullable();
            $table->timestamps();
            $table->foreign('community_id')->references('id')->on('community')->onDelete('cascade');
            $table->foreign('extension_id')->references('id')->on('community_extensions')->onDelete('cascade');
            $table->foreign('member_id')->references('id')->on('community_members')->onDelete('cascade');
        });

        Schema::create('auto_dms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('community_id');
            $table->unsignedBigInteger('template_id');
            $table->unsignedBigInteger('from_id');
            $table->unsignedBigInteger('to_id');
            $table->timestamps();
            $table->foreign('community_id')->references('id')->on('community')->onDelete('cascade');
            $table->index('community_id');
            $table->foreign('template_id')->references('id')->on('auto_dm_templates')->onDelete('cascade');
            $table->foreign('from_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('to_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('auto_dms');

        Schema::dropIfExists('auto_dm_templates');

        Schema::dropIfExists('community_extensions');

        Schema::dropIfExists('extensions');
    }
}
