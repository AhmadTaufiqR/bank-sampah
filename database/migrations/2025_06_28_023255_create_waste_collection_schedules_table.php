<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('waste_collection_schedules', function (Blueprint $table) {
            $table->id('id_schedule');
            $table->unsignedBigInteger('id_admin');
            $table->string('photo')->nullable();
            $table->text('content')->nullable(); // Deskripsi tambahan jika perlu
            $table->string('month'); // Menambahkan kolom untuk bulan
            $table->json('dates'); // Menambahkan kolom JSON untuk menyimpan daftar tanggal
            $table->string('activity')->nullable();
            $table->timestamps();

            $table->foreign('id_admin')->references('id_admin')->on('admins')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('waste_collection_schedules');
    }
};
