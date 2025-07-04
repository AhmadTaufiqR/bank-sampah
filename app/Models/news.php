<?php
// app/Models/News.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    protected $table = 'news';
    protected $primaryKey = 'id_news';

    protected $fillable = [
        'id_admin',
        'title',
        'content',
        'photo',
        'date'
    ];

    protected $casts = [
        'date' => 'date',
    ];

    // Relationships
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('date', '<=', now()->toDateString())
            ->orderBy('date', 'desc');
    }

    public function scopeLatest($query, $limit = 5)
    {
        return $query->orderBy('date', 'desc')->limit($limit);
    }

    // Accessor
    public function getExcerptAttribute()
    {
        return substr(strip_tags($this->content), 0, 150) . '...';
    }
}