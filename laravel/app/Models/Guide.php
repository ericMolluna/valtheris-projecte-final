<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guide extends Model
{
    protected $fillable = ['title', 'description', 'createdBy', 'content', 'category', 'image', 'user_id', 'likes', 'dislikes'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function images()
    {
        return $this->hasMany(GuideImage::class);
    }

    public function usersWhoLiked()
    {
        return $this->belongsToMany(User::class, 'guide_user_like')
                    ->withTimestamps();
    }

    public function usersWhoDisliked()
    {
        return $this->belongsToMany(User::class, 'guide_user_dislike')
                    ->withTimestamps();
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }
}