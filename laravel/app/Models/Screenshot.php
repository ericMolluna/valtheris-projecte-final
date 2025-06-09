<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Screenshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'image_url',
        'title',
        'description',
        'likes',
        'dislikes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function usersWhoLiked()
    {
        return $this->belongsToMany(User::class, 'screenshot_likes', 'screenshot_id', 'user_id');
    }

    public function usersWhoDisliked()
    {
        return $this->belongsToMany(User::class, 'screenshot_dislikes', 'screenshot_id', 'user_id');
    }
}