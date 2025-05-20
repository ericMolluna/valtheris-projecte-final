<?php

namespace App\Http\Controllers;

use App\Models\Guide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GuideController extends Controller
{
    // Método existente para obtener las guías del usuario autenticado
    public function index()
    {
        $guides = Auth::user()->guides()->get();
        return response()->json($guides);
    }

    // Nuevo método para obtener todas las guías (públicas)
    public function all()
    {
        $guides = Guide::with('user')->get(); // Incluye el usuario que creó la guía
        return response()->json($guides);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'required|string',
            'category' => 'nullable|string|max:100',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Image is now required
        ]);

        $guideData = [
            'title' => $request->title,
            'description' => $request->description,
            'content' => $request->content,
            'category' => $request->category,
            'user_id' => Auth::id(),
        ];

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('guides', 'public');
            $guideData['image'] = asset('storage/' . $path);
        }

        $guide = Guide::create($guideData);
        $guide->load('user');
        return response()->json(['message' => 'Guía creada con éxito', 'guide' => $guide], 201);
    }

    public function update(Request $request, Guide $guide)
    {
        if ($guide->user_id !== Auth::id()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'required|string',
            'category' => 'nullable|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Image is optional for updates
            'image_url' => 'nullable|string', // Existing image URL
        ]);

        $guideData = [
            'title' => $request->title,
            'description' => $request->description,
            'content' => $request->content,
            'category' => $request->category,
        ];

        // Handle image update
        if ($request->hasFile('image')) {
            // Delete the old image if it exists
            if ($guide->image) {
                $oldImagePath = str_replace(asset('storage/'), 'public/', $guide->image);
                Storage::delete($oldImagePath);
            }
            $path = $request->file('image')->store('guides', 'public');
            $guideData['image'] = asset('storage/' . $path);
        } elseif ($request->has('image_url')) {
            // Use the existing image URL if provided
            $guideData['image'] = $request->image_url;
        }
        // If neither 'image' nor 'image_url' is provided, the existing image is preserved

        $guide->update($guideData);
        $guide->load('user');

        return response()->json(['message' => 'Guía actualizada con éxito', 'guide' => $guide]);
    }

    public function destroy(Guide $guide)
    {
        if ($guide->user_id !== Auth::id()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        // Delete the image if it exists
        if ($guide->image) {
            $imagePath = str_replace(asset('storage/'), 'public/', $guide->image);
            Storage::delete($imagePath);
        }

        $guide->delete();
        return response()->json(['message' => 'Guía eliminada con éxito']);
    }


    public function like($id)
    {
        $guide = Guide::findOrFail($id);
        $user = Auth::user();

        // Verificar y eliminar "No me gusta" si existe
        if ($guide->usersWhoDisliked()->where('user_id', $user->id)->exists()) {
            $guide->decrement('dislikes');
            $guide->usersWhoDisliked()->detach($user->id);
            $guide->user_disliked = false; // Actualizar estado localmente
        }

        // Verificar si el usuario ya dio "Me gusta"
        if ($guide->usersWhoLiked()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Ya has dado "Me gusta" a esta guía'], 400);
        }

        // Registrar "Me gusta"
        $guide->increment('likes');
        $guide->usersWhoLiked()->attach($user->id);

        return response()->json([
            'message' => 'Like registrado',
            'likes' => $guide->likes,
            'dislikes' => $guide->dislikes,
            'user_liked' => true,
            'user_disliked' => false,
        ]);
    }

    public function dislike($id)
    {
        $guide = Guide::findOrFail($id);
        $user = Auth::user();

        // Verificar y eliminar "Me gusta" si existe
        if ($guide->usersWhoLiked()->where('user_id', $user->id)->exists()) {
            $guide->decrement('likes');
            $guide->usersWhoLiked()->detach($user->id);
            $guide->user_liked = false; // Actualizar estado localmente
        }

        // Verificar si el usuario ya dio "No me gusta"
        if ($guide->usersWhoDisliked()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Ya has dado "No me gusta" a esta guía'], 400);
        }

        // Registrar "No me gusta"
        $guide->increment('dislikes');
        $guide->usersWhoDisliked()->attach($user->id);

        return response()->json([
            'message' => 'Dislike registrado',
            'likes' => $guide->likes,
            'dislikes' => $guide->dislikes,
            'user_liked' => false,
            'user_disliked' => true,
        ]);
    }

    public function show($id)
    {
        $guide = Guide::with(['user', 'usersWhoLiked', 'usersWhoDisliked'])->findOrFail($id);
        $user = Auth::user();

        return response()->json([
            'id' => $guide->id,
            'title' => $guide->title,
            'description' => $guide->description,
            'content' => $guide->content,
            'category' => $guide->category,
            'image' => $guide->image,
            'user_id' => $guide->user_id,
            'createdBy' => $guide->createdBy,
            'user' => $guide->user,
            'likes' => $guide->likes,
            'dislikes' => $guide->dislikes,
            'user_liked' => $user ? $guide->usersWhoLiked->contains($user->id) : false,
            'user_disliked' => $user ? $guide->usersWhoDisliked->contains($user->id) : false,
            'created_at' => $guide->created_at,
            'updated_at' => $guide->updated_at,
        ]);
    }
}
