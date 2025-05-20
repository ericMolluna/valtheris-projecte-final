<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'video_url',
        'thumbnail_url',
        'description',
        'popularity',
        
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function likes()
    {
        return $this->belongsToMany(User::class, 'video_likes')->withTimestamps();
    }

    public function dislikes()
    {
        return $this->belongsToMany(User::class, 'video_dislikes')->withTimestamps();
    }
}