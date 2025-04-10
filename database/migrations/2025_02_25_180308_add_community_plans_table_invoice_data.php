<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCommunityPlansTableInvoiceData extends Migration
{
    public function up()
    {
        Schema::table('community', function (Blueprint $table) {
            $table->longText('invoice_data')->nullable()->after('price_id');
        });
    }

    public function down()
    {
        Schema::table('community', function (Blueprint $table) {
            $table->dropColumn('invoice_data');
        });
    }
}
