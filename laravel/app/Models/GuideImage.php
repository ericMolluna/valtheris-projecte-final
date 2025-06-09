<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuideImage extends Model
{
    protected $fillable = ['guide_id', 'image_path'];

    public function guide()
    {
        return $this->belongsTo(Guide::class);
    }
}