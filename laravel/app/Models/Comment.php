<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'text',
        'user_id',
        'video_id',
    ];

    // Relación con el usuario que creó el comentario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación con el video al que pertenece el comentario
    public function video()
    {
        return $this->belongsTo(Video::class);
    }

    public function screenshot()
    {
        return $this->belongsTo(Screenshot::class); // CAMBIO AQUÍ
    }
    
}