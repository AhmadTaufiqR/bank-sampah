<?php
// app/Models/Admin.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'admins';
    protected $primaryKey = 'id_admin';

    protected $fillable = [
        'admin_name',
        'admin_username',
        'admin_password',
        'phone',
        'address',
        'photo'
    ];

    protected $hidden = [
        'admin_password',
        'remember_token',
    ];

    protected $casts = [
        'admin_password' => 'hashed',
    ];

    // Relationships
    public function bankBalances()
    {
        return $this->hasMany(BankBalance::class, 'id_admin', 'id_admin');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'id_admin', 'id_admin');
    }

    public function wasteCollectionSchedules()
    {
        return $this->hasMany(WasteCollectionSchedule::class, 'id_admin', 'id_admin');
    }

    public function news()
    {
        return $this->hasMany(News::class, 'id_admin', 'id_admin');
    }

    public function wasteTypes()
    {
        return $this->hasMany(WasteType::class, 'id_admin', 'id_admin');
    }
}
