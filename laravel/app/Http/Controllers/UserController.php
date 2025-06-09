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
            'avatar' => $user->avatar,
            'screenshots' => $user->screenshots,
            'comments' => $user->comments,
            'inventory' => $user->inventory ?? 0,
            'videos' => $user->videos ?? 0,
            'workshopItems' => $user->workshopItems ?? 0,
            'artwork' => $user->artwork ?? 0,
            'tier' => $user->tier ?? 'Tier 1', // Include tier in response
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

    /**
     * Procesa la suscripción del usuario.
     */
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'tier' => 'required|in:Tier 1,Tier 2,Tier 3',
            'paymentOption' => 'required|in:monthly,yearly',
            'cardNumber' => 'required|string',
            'cardHolder' => 'required|string',
            'expiryDate' => 'required|string',
            'cvv' => 'required|string',
        ]);

        // Simulate payment processing
        // In a real app, integrate with a payment gateway like Stripe
        // Optionally store subscription details in a subscriptions table

        return response()->json(['message' => 'Subscription processed successfully']);
    }

    /**
     * Actualiza el tier del usuario autenticado.
     */
    public function updateTier(Request $request)
    {
        $validated = $request->validate([
            'tier' => 'required|in:Tier 1,Tier 2,Tier 3',
        ]);

        $user = Auth::user();
        $user->tier = $request->tier;
        $user->save();

        return response()->json([
            'message' => 'Tier updated successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'tier' => $user->tier,
            ]
        ]);
    }
}