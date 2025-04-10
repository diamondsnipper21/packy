<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNextScheduledAtColumnToScheduledPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('scheduled_posts', function (Blueprint $table) {
            $table->timestamp('next_scheduled_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('scheduled_posts', function (Blueprint $table) {
            $table->dropColumn('next_scheduled_at');
        });
    }
}
