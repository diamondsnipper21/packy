<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateNewCommunityMediasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('medias', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type');
            $table->string('path');
            $table->string('ext')->nullable();
            $table->string('filename')->nullable();
            $table->timestamps();
        });

        Schema::create('community_medias', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('community_id');
            $table->unsignedBigInteger('media_id');
            $table->unsignedBigInteger('order');
            $table->timestamps();

            $table->foreign('community_id')->references('id')->on('community')->onDelete('cascade');
            $table->foreign('media_id')->references('id')->on('medias')->onDelete('cascade');
        });

        Schema::create('community_posts_medias', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('post_id');
            $table->unsignedBigInteger('media_id');
            $table->timestamps();

            $table->foreign('post_id')->references('id')->on('community_posts')->onDelete('cascade');
            $table->foreign('media_id')->references('id')->on('medias')->onDelete('cascade');
        });

        Schema::create('community_scheduled_posts_medias', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('scheduled_post_id');
            $table->unsignedBigInteger('media_id');
            $table->timestamps();

            $table->foreign('scheduled_post_id')->references('id')->on('scheduled_posts')->onDelete('cascade');
            $table->foreign('media_id')->references('id')->on('medias')->onDelete('cascade');
        });

        Schema::create('community_comments_medias', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('comment_id');
            $table->unsignedBigInteger('media_id');
            $table->timestamps();

            $table->foreign('comment_id')->references('id')->on('community_post_comments')->onDelete('cascade');
            $table->foreign('media_id')->references('id')->on('medias')->onDelete('cascade');
        });

        Schema::create('community_chats_medias', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('chat_id');
            $table->unsignedBigInteger('media_id');
            $table->timestamps();

            $table->foreign('chat_id')->references('id')->on('chats')->onDelete('cascade');
            $table->foreign('media_id')->references('id')->on('medias')->onDelete('cascade');
        });

        Artisan::call('command:migrate-community-media');

        sleep(3);

        Schema::table('community', function (Blueprint $table) {
            $table->dropColumn('video');
            $table->dropColumn('photo');
        });

        Schema::dropIfExists('community_media');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('community', function (Blueprint $table) {
            $table->string('photo')->nullable()->after('summary_photo');
            $table->string('video')->nullable()->after('photo');
        });

        Schema::dropIfExists('community_chats_medias');
        Schema::dropIfExists('community_comments_medias');
        Schema::dropIfExists('community_scheduled_posts_medias');
        Schema::dropIfExists('community_posts_medias');
        Schema::dropIfExists('community_medias');
        Schema::dropIfExists('medias');
    }
}
