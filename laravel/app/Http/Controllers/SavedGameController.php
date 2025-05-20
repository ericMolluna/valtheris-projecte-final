<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SavedGame;

class SavedGameController extends Controller
{
    public function store(Request $request)
    {
        // Validar los datos del juego
        $request->validate([
            'game_data' => 'required|array',
        ]);

        // Guardar la partida en la base de datos
        $savedGame = SavedGame::updateOrCreate(
            ['user_id' => $request->user()->id],  // Asumiendo que estás usando autenticación de usuario
            ['game_data' => $request->game_data]
        );

        return response()->json($savedGame, 201);
    }

    public function show(Request $request)
    {
        // Recuperar la partida guardada
        $savedGame = SavedGame::where('user_id', $request->user()->id)->first();

        if (!$savedGame) {
            return response()->json(['message' => 'No saved game found'], 404);
        }

        return response()->json($savedGame);
    }
}
