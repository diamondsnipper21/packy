<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class CreateElementLikesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('element_likes', function (Blueprint $table) {
            $table->id();
            $table->integer('community_id');
            $table->integer('member_id');
            $table->integer('element_id');
            $table->integer('element_type');
            $table->integer('element_owner_id');
            $table->integer('status')->default(0);
            $table->timestamps();

            $table->index('element_owner_id');
            $table->index(['community_id', 'member_id', 'element_id', 'element_type'], 'like_element_object_index');
        });

        Artisan::call('command:migrate-element-likes');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('element_likes');
    }
}
