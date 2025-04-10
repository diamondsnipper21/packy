<?php

use App\Enum\CountryCodeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('community_member_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unsigned();
            $table->unsignedBigInteger('community_id')->unsigned();
            $table->unsignedBigInteger('price_id')->unsigned()->nullable();
            $table->unsignedBigInteger('payment_method_id')->unsigned()->nullable();
            $table->string('stripe_subscription_id');
            $table->string('period');
            $table->string('status');
            $table->integer('failed_attempts')->default(0);
            $table->dateTime('trial_ends_at')->nullable();
            $table->dateTime('next_billing_at');
            $table->dateTime('cancelled_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id', 'cms_fk1')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('community_id', 'cms_fk2')->references('id')->on('community')->onDelete('cascade');
            $table->foreign('price_id', 'cms_fk3')->references('id')->on('stripe_prices')->onDelete('set null');
            $table->foreign('payment_method_id', 'cms_fk4')->references('id')->on('payment_methods')->onDelete('set null');
        });

        Schema::create('community_member_subscriptions_cancel_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('community_id')->unsigned();
            $table->unsignedBigInteger('member_id')->unsigned();
            $table->unsignedBigInteger('subscription_id')->unsigned();
            $table->timestamps();

            $table->foreign('community_id', 'cmscr_fk1')->references('id')->on('community')->onDelete('cascade');
            $table->foreign('member_id', 'cmscr_fk2')->references('id')->on('community_members')->onDelete('cascade');
            $table->foreign('subscription_id', 'cmscr_fk3')->references('id')->on('community_member_subscriptions')->onDelete('cascade');
        });

        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('community_id')->unsigned()->nullable();
            $table->unsignedBigInteger('to')->unsigned();
            $table->integer('amount');
            $table->string('currency');
            $table->string('status');
            $table->dateTime('period_start')->nullable();
            $table->dateTime('period_end')->nullable();
            $table->string('stripe_transfer_id')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('community_id')->references('id')->on('community')->onDelete('set null');
            $table->foreign('to')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('community_member_subscriptions_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unsigned();
            $table->unsignedBigInteger('community_id')->unsigned()->nullable();
            $table->unsignedBigInteger('subscription_id')->unsigned()->nullable();
            $table->unsignedBigInteger('payment_method_id')->unsigned()->nullable();
            $table->unsignedBigInteger('payout_id')->unsigned()->nullable();
            $table->string('number');
            $table->string('charge');
            $table->string('invoice');
            $table->integer('amount');
            $table->integer('tax')->default(0)->nullable();
            $table->integer('tax_rate')->default(0)->nullable();
            $table->integer('fees');
            $table->string('currency');
            $table->string('country');
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->foreign('user_id', 'cmst_fk1')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('community_id', 'cmst_fk2')->references('id')->on('community')->onDelete('set null');
            $table->foreign('subscription_id', 'cmst_fk3')->references('id')->on('community_member_subscriptions')->onDelete('set null');
            $table->foreign('payment_method_id', 'cmst_fk4')->references('id')->on('payment_methods')->onDelete('set null');
            $table->foreign('payout_id', 'cmst_fk5')->references('id')->on('payouts')->onDelete('set null');
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unsigned();
            $table->unsignedBigInteger('community_id')->unsigned()->nullable();
            $table->string('number');
            $table->integer('amount');
            $table->integer('tax');
            $table->integer('tax_rate');
            $table->string('currency');
            $table->longText('data');
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('community_id')->references('id')->on('community')->onDelete('set null');
        });

        Schema::table('community', function (Blueprint $table) {
            $table->integer('trial_days')->default(0)->after('status');
        });

        Schema::table('community_has_members', function (Blueprint $table) {
            $table->unsignedBigInteger('subscription_id')->unsigned()->nullable()->after('access');

            $table->foreign('subscription_id')->references('id')->on('community_member_subscriptions')->onDelete('set null');
        });

        Schema::table('webhook_events', function (Blueprint $table) {
            $table->dateTime('treated_at')->nullable()->after('headers');
        });

        Schema::create('vat_rates', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('category');
            $table->string('country');
            $table->float('rate')->default(0);
            $table->string('stripe_tax_rate_id')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('country')->nullable()->after('email');
        });

        Schema::create('community_notifications_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('community_id')->unsigned()->nullable();
            $table->string('type');
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
        Schema::dropIfExists('community_notifications_settings');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('country');
        });

        Schema::dropIfExists('vat_rates');

        Schema::table('webhook_events', function (Blueprint $table) {
            $table->dropColumn('treated_at');
        });

        Schema::table('community_has_members', function (Blueprint $table) {
            $table->dropForeign(['subscription_id']);
            $table->dropColumn('subscription_id');
        });

        Schema::table('community', function (Blueprint $table) {
            $table->dropColumn('trial_days');
        });

        Schema::dropIfExists('payouts');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('community_member_subscriptions_transactions');
        Schema::dropIfExists('community_member_subscriptions_cancel_requests');
        Schema::dropIfExists('community_member_subscriptions');
    }
}
