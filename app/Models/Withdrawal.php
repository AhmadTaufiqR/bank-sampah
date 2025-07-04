<?php
// app/Models/Withdrawal.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    use HasFactory;

    protected $table = 'withdrawals';
    protected $primaryKey = 'id_withdrawal';
    protected $fillable = ['id_user', 'user_name', 'withdrawal_date', 'withdrawal_amount', 'status', 'admin_verified_by'];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    protected $casts = [
        'withdrawal_amount' => 'decimal:2',
        'withdrawal_date' => 'date',
    ];
    // Accessor
    public function getFormattedAmountAttribute()
    {
        return 'Rp ' . number_format($this->withdrawal_amount, 0, ',', '.');
    }
}
