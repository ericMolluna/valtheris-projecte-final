<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavedGame extends Model
{
    protected $fillable = ['user_id', 'game_data'];

    protected $casts = [
        'game_data' => 'array',  // Asegura que `game_data` se maneje como un array o JSON
    ];
}
