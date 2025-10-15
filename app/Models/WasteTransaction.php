<?php
// app/Models/WasteTransaction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WasteTransaction extends Model
{
    use HasFactory;

    protected $table = 'waste_transactions';
    protected $primaryKey = 'id_transaction';

    protected $fillable = [
        'id_user',
        'waste_type',
        'weight',
        'description',
        'price',
        'batch_code',
        'photo'
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'price' => 'decimal:2',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    // Accessors
    public function getTotalValueAttribute()
    {
        return $this->weight * $this->price;
    }

    public function getFormattedTotalValueAttribute()
    {
        return 'Rp ' . number_format($this->total_value, 0, ',', '.');
    }

    public function getFormattedWeightAttribute()
    {
        return $this->weight . ' kg';
    }

    // Scopes
    public function scopeByUser($query, $userId)
    {
        return $query->where('id_user', $userId);
    }

    public function scopeByWasteType($query, $wasteType)
    {
        return $query->where('waste_type', $wasteType);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}