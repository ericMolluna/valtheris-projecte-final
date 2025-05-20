<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Devuelve los datos del usuario autenticado.
     */
    public function getUser(Request $request)
    {
        $user = $request->user();
        
        // Conteo de capturas y comentarios
        $user->screenshots = $user->screenshots()->count();
        $user->comments = $user->comments()->count();
        
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->avatar, // Include avatar in the response
            'screenshots' => $user->screenshots,
            'comments' => $user->comments,
            'inventory' => $user->inventory ?? 0,
            'videos' => $user->videos ?? 0,
            'workshopItems' => $user->workshopItems ?? 0,
            'artwork' => $user->artwork ?? 0,
        ]);
    }

    /**
     * Devuelve los comentarios del usuario autenticado.
     */
    public function getComments(Request $request)
    {
        $user = $request->user();
        $comments = $user->comments()
            ->with(['screenshot' => function ($query) {
                $query->select('id', 'title');
            }])
            ->get()
            ->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'text' => $comment->text,
                    'screenshot' => $comment->screenshot ? [
                        'id' => $comment->screenshot->id,
                        'title' => $comment->screenshot->title,
                    ] : null,
                ];
            });

        return response()->json($comments);
    }

    /**
     * Maneja la subida del avatar del usuario autenticado.
     */
    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|max:5120' // 5MB max
        ]);
    
        $user = Auth::user();
    
        // Delete old avatar if it exists
        if ($user->avatar) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $user->avatar));
        }
    
        // Store the new avatar
        $path = $request->file('avatar')->store('avatars', 'public');
        $user->avatar = '/storage/' . $path;
        $user->save();
    
        return response()->json([
            'avatar_url' => $user->avatar
        ]);
    }

    public function updateTier(Request $request)
    {
        $request->validate([
            'tier' => 'required|string|in:Tier 1,Tier 2,Tier 3',
        ]);

        $user = Auth::user(); // Obtener usuario autenticado

        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        $user->tier = $request->input('tier');
        $user->save();

        return response()->json([
            'message' => 'Tier actualizado correctamente',
            'tier' => $user->tier
        ]);
    }
}