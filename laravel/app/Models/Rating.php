<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    protected $fillable = ['guide_id', 'user_id', 'rating', 'text'];

    public function guide()
    {
        return $this->belongsTo(Guide::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}