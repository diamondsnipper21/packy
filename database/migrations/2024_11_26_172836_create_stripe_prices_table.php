<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStripePricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stripe_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('community_id');
            $table->string('stripe_product_id');
            $table->timestamps();

            $table->foreign('community_id')->references('id')->on('community')->onDelete('cascade');
            $table->index('community_id');
        });

        Schema::create('stripe_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->float('amount_monthly')->nullable();
            $table->float('amount_yearly')->nullable();
            $table->string('vat')->default('incl');
            $table->string('stripe_price_id_monthly')->nullable();
            $table->string('stripe_price_id_yearly')->nullable();
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('stripe_products')->onDelete('cascade');
            $table->index('product_id');
        });

        Schema::table('community', function (Blueprint $table) {
            $table->unsignedBigInteger('price_id')->nullable()->after('status');
            $table->foreign('price_id')->references('id')->on('stripe_prices')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('community', function (Blueprint $table) {
            $table->dropForeign(['price_id']);
            $table->dropColumn('price_id');
        });

        Schema::dropIfExists('stripe_prices');
        Schema::dropIfExists('stripe_products');
    }
}
