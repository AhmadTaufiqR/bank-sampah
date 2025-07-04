<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id('id_withdrawal');
            $table->unsignedBigInteger('id_user');
            $table->string('user_name');
            $table->date('withdrawal_date');
            $table->decimal('withdrawal_amount', 15, 2);
            $table->string('status')->default('pending'); // Menambahkan status
            $table->unsignedBigInteger('admin_verified_by')->nullable(); // Menambahkan admin yang memverifikasi
            $table->timestamps();

            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
            $table->foreign('admin_verified_by')->references('id_admin')->on('admins')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('withdrawals');
    }
};
