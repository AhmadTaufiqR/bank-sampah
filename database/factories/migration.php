<?php

// 1. Create Admins Table Migration
// php artisan make:migration create_admins_table

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminsTable extends Migration
{
    public function up()
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id('id_admin');
            $table->string('admin_name');
            $table->string('admin_username')->unique();
            $table->string('admin_password');
            $table->string('phone');
            $table->string('address');
            $table->string('photo')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('admins');
    }
}

// 2. Create Users Table Migration
// php artisan make:migration create_users_table

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id('id_user');
            $table->string('user_name');
            $table->string('username')->unique();
            $table->string('user_password');
            $table->string('phone');
            $table->string('address');
            $table->string('photo');
            $table->decimal('balance', 15, 2)->default(0);
            $table->integer('withdrawal_count')->default(0);
            $table->decimal('withdrawal_amount', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}

// 3. Create Bank Balance Table Migration
// php artisan make:migration create_bank_balances_table

class CreateBankBalancesTable extends Migration
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
}

// 4. Create Withdrawals Table Migration
// php artisan make:migration create_withdrawals_table

class CreateWithdrawalsTable extends Migration
{
    public function up()
    {
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id('id_withdrawal');
            $table->unsignedBigInteger('id_user');
            $table->string('user_name');
            $table->date('withdrawal_date');
            $table->decimal('withdrawal_amount', 15, 2);
            $table->timestamps();

            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('withdrawals');
    }
}

// 5. Create Notifications Table Migration
// php artisan make:migration create_notifications_table

class CreateNotificationsTable extends Migration
{
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id('id_notification');
            $table->unsignedBigInteger('id_user');
            $table->unsignedBigInteger('id_admin');
            $table->string('message_content');
            $table->date('date');
            $table->timestamps();

            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
            $table->foreign('id_admin')->references('id_admin')->on('admins')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('notifications');
    }
}

// 6. Create Waste Collection Schedule Table Migration
// php artisan make:migration create_waste_collection_schedules_table

class CreateWasteCollectionSchedulesTable extends Migration
{
    public function up()
    {
        Schema::create('waste_collection_schedules', function (Blueprint $table) {
            $table->id('id_schedule');
            $table->unsignedBigInteger('id_admin');
            $table->string('photo')->nullable();
            $table->text('content');
            $table->date('date');
            $table->string('activity');
            $table->timestamps();

            $table->foreign('id_admin')->references('id_admin')->on('admins')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('waste_collection_schedules');
    }
}

// 7. Create News Table Migration
// php artisan make:migration create_news_table

class CreateNewsTable extends Migration
{
    public function up()
    {
        Schema::create('news', function (Blueprint $table) {
            $table->id('id_news');
            $table->unsignedBigInteger('id_admin');
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
}

// 8. Create Waste Types Table Migration
// php artisan make:migration create_waste_types_table

class CreateWasteTypesTable extends Migration
{
    public function up()
    {
        Schema::create('waste_types', function (Blueprint $table) {
            $table->id('id_waste_type');
            $table->unsignedBigInteger('id_user');
            $table->unsignedBigInteger('id_admin');
            $table->string('waste_type');
            $table->decimal('price', 10, 2);
            $table->timestamps();

            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
            $table->foreign('id_admin')->references('id_admin')->on('admins')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('waste_types');
    }
}

// 9. Create Waste Transactions Table Migration
// php artisan make:migration create_waste_transactions_table

class CreateWasteTransactionsTable extends Migration
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
}

// Commands to run migrations:
// php artisan migrate

// To create individual migration files, run:
// php artisan make:migration create_admins_table
// php artisan make:migration create_users_table
// php artisan make:migration create_bank_balances_table
// php artisan make:migration create_withdrawals_table
// php artisan make:migration create_notifications_table
// php artisan make:migration create_waste_collection_schedules_table
// php artisan make:migration create_news_table
// php artisan make:migration create_waste_types_table
// php artisan make:migration create_waste_transactions_table