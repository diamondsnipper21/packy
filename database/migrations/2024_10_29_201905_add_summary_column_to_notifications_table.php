<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class AddSummaryColumnToNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->integer('owner_id')->nullable();
            $table->string('summary')->nullable();
            $table->index('owner_id');
        });
        Artisan::call('command:migrate-notification-summary');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['owner_id']);
            $table->dropColumn('owner_id');
            $table->dropColumn('summary');
        });
    }
}
