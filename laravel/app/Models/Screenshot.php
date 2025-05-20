<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Screenshot extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'image_url',
        'description',
        'likes',
        'dislikes',
    ];

    protected $appends = ['liked_by_user', 'disliked_by_user'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function usersWhoLiked()
    {
        return $this->belongsToMany(User::class, 'screenshot_likes', 'screenshot_id', 'user_id')
                    ->withTimestamps();
    }

    public function usersWhoDisliked()
    {
        return $this->belongsToMany(User::class, 'screenshot_dislikes', 'screenshot_id', 'user_id')
                    ->withTimestamps();
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function getLikesAttribute()
    {
        return $this->usersWhoLiked()->count();
    }

    public function getDislikesAttribute()
    {
        return $this->usersWhoDisliked()->count();
    }

    public function getLikedByUserAttribute()
    {
        return auth()->check() && $this->usersWhoLiked()->where('user_id', auth()->id())->exists();
    }

    public function getDislikedByUserAttribute()
    {
        return auth()->check() && $this->usersWhoDisliked()->where('user_id', auth()->id())->exists();
    }
}