<?php
// app/Models/WasteCollectionSchedule.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WasteCollectionSchedule extends Model
{
    use HasFactory;

    protected $table = 'waste_collection_schedules';
    protected $primaryKey = 'id_schedule';
    protected $fillable = ['id_admin', 'photo', 'content', 'month', 'dates', 'activity'];
    protected $casts = [
        'dates' => 'array', // Memastikan kolom dates di-cast sebagai array
    ];

    // Relationships
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }

    // Scopes
    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', now()->toDateString())
            ->orderBy('date', 'asc');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('date', now()->toDateString());
    }
}
