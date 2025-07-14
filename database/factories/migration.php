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
        Schema::create('users', function (Blueprint $table) {
            $table->id('id_user');
            $table->string('user_name');
            $table->string('user_password');
            $table->string('email')->nullable()->unique();
            $table->string('username')->nullable()->unique();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('photo')->nullable();
            $table->string('nik')->nullable();
            $table->string('jenis_kelamin')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->decimal('balance', 15, 2)->default(0)->nullable(); // Total saldo tabungan
            $table->integer('withdrawal_count')->default(0)->nullable();
            $table->decimal('withdrawal_amount', 15, 2)->default(0)->nullable();
            $table->boolean('is_primary')->default(false)->nullable(); // Menambahkan kolom untuk rekening utama
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};



return new class extends Migration
{
    public function up()
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id('id_admin');
            $table->string('admin_name');
            $table->string('email')->unique();
            $table->string('admin_password');
            $table->string('username')->unique()->nullable();
            $table->string('phone')->nullable();
            $table->string('nik')->nullable();
            $table->string('jenis_kelamin')->nullable();
            $table->string('tanggal_lahir')->nullable();
            $table->string('address')->nullable();
            $table->string('photo')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('admins');
    }
};


// bank balances


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


// withdrawal

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

// notification


return new class extends Migration
{
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id('id_notification');
            $table->unsignedBigInteger('id_user');
            $table->unsignedBigInteger('id_admin')->nullable(); // Bisa null jika belum diverifikasi
            $table->string('message_content');
            $table->date('date');
            $table->string('status')->default('pending'); // Menambahkan status: pending, verified, rejected
            $table->timestamps();

            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
            $table->foreign('id_admin')->references('id_admin')->on('admins')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('notifications');
    }
};





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




return new class extends Migration
{
    public function up()
    {
        Schema::create('news', function (Blueprint $table) {
            $table->id('id_news');
            $table->unsignedBigInteger('id_admin');
            $table->string('source');
            $table->string('title');
            $table->text('content');
            $table->string('photo')->nullable();
            $table->date('date');
            $table->timestamps();

            $table->foreign('id_admin')->references('id_admin')->on('admins')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('news');
    }
};





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



return new class extends Migration
{
    public function up()
    {
        Schema::create('waste_transactions', function (Blueprint $table) {
            $table->id('id_transaction');
            $table->unsignedBigInteger('id_user');
            $table->string('waste_type');
            $table->decimal('weight', 8, 2);
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('photo')->nullable();
            $table->timestamps();

            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('waste_transactions');
    }
};



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
