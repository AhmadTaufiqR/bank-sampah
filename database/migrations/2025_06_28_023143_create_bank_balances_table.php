<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bank_balances', function (Blueprint $table) {
            $table->id('id_balance');
            $table->unsignedBigInteger('id_admin');
            $table->unsignedBigInteger('id_user');
            $table->decimal('total_balance', 15, 2);
            $table->text('description')->nullable();
            $table->date('date');
            $table->timestamps();

            $table->foreign('id_admin')->references('id_admin')->on('admins')->onDelete('cascade');
            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('bank_balances');
    }
};
