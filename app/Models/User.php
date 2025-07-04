<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id_user';

    protected $fillable = [
        'user_name',
        'user_password',
        'phone',
        'address',
        'photo',
        'balance',
        'withdrawal_count',
        'withdrawal_amount',
        'nik',
        'jenis_kelamin',
        'email',
        'tanggal_lahir'
    ];

    protected $hidden = [
        'user_password',
        'remember_token',
    ];

    protected $casts = [
        'user_password' => 'hashed',
        'balance' => 'decimal:2',
        'withdrawal_amount' => 'decimal:2',
        'withdrawal_count' => 'integer',
    ];

    // Relationships
    public function bankBalances()
    {
        return $this->hasMany(BankBalance::class, 'id_user', 'id_user');
    }

    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class, 'id_user', 'id_user');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'id_user', 'id_user');
    }

    public function wasteTypes()
    {
        return $this->hasMany(WasteType::class, 'id_user', 'id_user');
    }

    public function wasteTransactions()
    {
        return $this->hasMany(WasteTransaction::class, 'id_user', 'id_user');
    }

    // Accessor & Mutator
    public function getFormattedBalanceAttribute()
    {
        return 'Rp ' . number_format($this->balance, 0, ',', '.');
    }

    // Scopes
    public function scopeWithPositiveBalance($query)
    {
        return $query->where('balance', '>', 0);
    }

}
