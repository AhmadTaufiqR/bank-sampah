<?php
// app/Models/BankBalance.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankBalance extends Model
{
    use HasFactory;

    protected $table = 'bank_balances';
    protected $primaryKey = 'id_balance';

    protected $fillable = [
        'id_admin',
        'id_user',
        'total_balance',
        'description',
        'date',
        'transaction_type'
    ];

    protected $casts = [
        'total_balance' => 'decimal:2',
        'date' => 'date',
    ];

    // Relationships
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
