<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('waste_types', function (Blueprint $table) {
            $table->id('id_waste_type');
            $table->unsignedBigInteger('id_admin');
            $table->string('waste_type');
            $table->decimal('price', 10, 2);
            $table->string('photo')->nullable(); // Menambahkan kolom untuk foto
            $table->timestamps();

            $table->foreign('id_admin')->references('id_admin')->on('admins')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('waste_types');
    }
};
