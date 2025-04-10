<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserPlansTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_plans_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unsigned()->nullable();
            $table->unsignedBigInteger('community_id')->unsigned()->nullable();
            $table->unsignedBigInteger('plan_id')->unsigned()->nullable();
            $table->unsignedBigInteger('payment_method_id')->unsigned()->nullable();
            $table->string('number');
            $table->string('charge');
            $table->string('invoice');
            $table->integer('amount');
            $table->integer('tax')->default(0)->nullable();
            $table->integer('tax_rate')->default(0)->nullable();
            $table->string('currency');
            $table->string('country');
            $table->string('status')->default('complete');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('community_id', 'upt_fk1')->references('id')->on('community')->onDelete('set null');
            $table->foreign('plan_id', 'upt_fk2')->references('id')->on('community_plans')->onDelete('set null');
            $table->foreign('payment_method_id', 'upt_fk3')->references('id')->on('user_payment_methods')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_plans_transactions');
    }
}
