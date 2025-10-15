<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('waste_transactions', function (Blueprint $table) {
            $table->string('batch_code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('waste_transactions', function (Blueprint $table) {
            $table->dropColumn('batch_code'); // Batch unik tiap setoran
        });
    }
};
