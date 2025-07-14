<?php
// app/Models/WasteType.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WasteType extends Model
{
    use HasFactory;

    protected $table = 'waste_types';
    protected $primaryKey = 'id_waste_type';

    protected $fillable = [
        'id_admin',
        'waste_type',
        'price',
        'photo'
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }

    // Accessor
    public function getFormattedPriceAttribute()
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.') . '/kg';
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('price', '>', 0);
    }
}
