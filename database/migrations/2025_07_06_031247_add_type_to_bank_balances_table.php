<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bank_balances', function (Blueprint $table) {
            $table->string('transaction_type')->after('total_balance')->default('cash_in');
        });
    }

    public function down()
    {
        Schema::table('bank_balances', function (Blueprint $table) {
            $table->dropColumn('transaction_type');
        });
    }
};
