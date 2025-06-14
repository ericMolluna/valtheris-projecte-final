<?php

namespace App\Http\Controllers;

use App\Models\Screenshot;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ScreenshotController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Screenshot::with(['user', 'usersWhoLiked', 'usersWhoDisliked']);

            // Filter by authenticated user if requested (e.g., for profile page)
            if ($request->has('user') && Auth::check()) {
                $query->where('user_id', Auth::id());
            }

            if ($request->has('sort')) {
                $sort = $request->input('sort');
                if ($sort === 'popular') {
                    $query->orderByDesc('likes');
                } elseif ($sort === 'recent') {
                    $query->latest();
                }
            } else {
                $query->latest(); // Por defecto, ordenar por fecha descendente
            }

            $screenshots = $query->get()->map(function ($screenshot) {
                return [
                    'id' => $screenshot->id,
                    'user_id' => $screenshot->user_id,
                    'username' => $screenshot->user ? $screenshot->user->name : 'Anónimo',
                    'title' => $screenshot->title,
                    'image_url' => $screenshot->image_url,
                    'description' => $screenshot->description,
                    'likes' => $screenshot->likes,
                    'dislikes' => $screenshot->dislikes,
                    'popularity' => $screenshot->likes - $screenshot->dislikes,
                    'created_at' => $screenshot->created_at,
                    'updated_at' => $screenshot->updated_at,
                ];
            });

            return response()->json($screenshots, 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener capturas: ' . $e->getMessage());
            return response()->json(['message' => 'Error al obtener capturas'], 500);
        }
    }

    public function show($id)
    {
        try {
            $screenshot = Screenshot::with(['user', 'usersWhoLiked', 'usersWhoDisliked'])->findOrFail($id);
            $user = Auth::user();
            return response()->json([
                'id' => $screenshot->id,
                'user_id' => $screenshot->user_id,
                'username' => $screenshot->user ? $screenshot->user->name : 'Anónimo',
                'image_url' => $screenshot->image_url,
                'description' => $screenshot->description,
                'title' => $screenshot->title,
                'likes' => $screenshot->likes,
                'dislikes' => $screenshot->dislikes,
                'popularity' => $screenshot->likes - $screenshot->dislikes,
                'user_liked' => $user ? $screenshot->usersWhoLiked->contains($user->id) : false,
                'user_disliked' => $user ? $screenshot->usersWhoDisliked->contains($user->id) : false,
                'created_at' => $screenshot->created_at,
                'updated_at' => $screenshot->updated_at,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener captura: ' . $e->getMessage());
            return response()->json(['message' => 'Error al obtener captura'], 404);
        }
    }

    public function store(Request $request)
    {
        try {
            // Validate input
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                'description' => 'nullable|string|max:255',
            ]);

            // Ensure user is authenticated
            if (!Auth::check()) {
                Log::warning('Intento de subir captura sin autenticación');
                return response()->json(['message' => 'No autorizado'], 401);
            }

            // Store the image
            $imagePath = $request->file('image')->store('screenshots', 'public');
            $imageUrl = '/storage/' . $imagePath;

            // Create screenshot record
            $screenshot = Screenshot::create([
                'user_id' => Auth::id(),
                'image_url' => $imageUrl,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'likes' => 0,
                'dislikes' => 0,
            ]);

            // Load user relationship
            $screenshot->load('user');

            return response()->json([
                'message' => 'Captura subida exitosamente',
                'screenshot' => [
                    'id' => $screenshot->id,
                    'user_id' => $screenshot->user_id,
                    'username' => $screenshot->user ? $screenshot->user->name : 'Anónimo',
                    'title' => $screenshot->title,
                    'image_url' => $imageUrl,
                    'description' => $screenshot->description,
                    'likes' => $screenshot->likes,
                    'dislikes' => $screenshot->dislikes,
                    'popularity' => $screenshot->likes - $screenshot->dislikes,
                    'created_at' => $screenshot->created_at,
                    'updated_at' => $screenshot->updated_at,
                ],
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validación fallida al subir captura: ' . json_encode($e->errors()));
            return response()->json(['message' => 'Validación fallida', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error al subir captura: ' . $e->getMessage() . ' | Stack: ' . $e->getTraceAsString());
            return response()->json(['message' => 'Error al subir captura: ' . $e->getMessage()], 500);
        }
    }

    

    public function destroy(Screenshot $screenshot)
    {
        try {
            if ($screenshot->user_id !== Auth::id()) {
                return response()->json(['message' => 'No autorizado'], 403);
            }

            if ($screenshot->image_url) {
                $imagePath = str_replace('/storage/', '', $screenshot->image_url);
                Storage::disk('public')->delete($imagePath);
            }

            $screenshot->delete();

            return response()->json(['message' => 'Captura eliminada con éxito'], 200);
        } catch (\Exception $e) {
            Log::error('Error al eliminar captura: ' . $e->getMessage());
            return response()->json(['message' => 'Error al eliminar captura'], 500);
        }
    }

    public function rate(Request $request, Screenshot $screenshot)
{
    try {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $user = Auth::user();

        // Verificar si el usuario ya ha calificado
        $existingRating = $screenshot->ratings()->where('user_id', $user->id)->first();
        if ($existingRating) {
            return response()->json(['message' => 'Ya has calificado esta captura'], 400);
        }

        // Guardar la calificación
        $screenshot->ratings()->create([
            'user_id' => $user->id,
            'rating' => $validated['rating'],
        ]);

        // Calcular el promedio de calificaciones
        $averageRating = $screenshot->ratings()->avg('rating');
        $screenshot->average_rating = $averageRating;
        $screenshot->save();

        return response()->json([
            'message' => 'Calificación registrada',
            'average_rating' => round($averageRating, 1),
            'user_rating' => $validated['rating'],
        ], 200);
    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::error('Validación fallida al calificar captura: ' . json_encode($e->errors()));
        return response()->json(['message' => 'Validación fallida', 'errors' => $e->errors()], 422);
    } catch (\Exception $e) {
        Log::error('Error al calificar captura: ' . $e->getMessage());
        return response()->json(['message' => 'Error al calificar captura'], 500);
    }
}


    public function getComments(Screenshot $screenshot)
    {
        try {
            $comments = $screenshot->comments()->with('user')->get()->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'user_id' => $comment->user_id,
                    'user' => [
                        'id' => $comment->user ? $comment->user->id : null,
                        'name' => $comment->user ? $comment->user->name : 'Anónimo',
                    ],
                    'text' => $comment->text,
                    'created_at' => $comment->created_at,
                ];
            });
            return response()->json($comments, 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener comentarios: ' . $e->getMessage());
            return response()->json(['message' => 'Error al obtener comentarios'], 500);
        }
    }

    public function storeComment(Request $request, Screenshot $screenshot)
    {
        try {
            $validated = $request->validate([
                'text' => 'required|string|max:1000',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validación fallida: ' . json_encode($e->errors()));
            return response()->json(['message' => 'Validación fallida', 'errors' => $e->errors()], 422);
        }

        try {
            $comment = $screenshot->comments()->create([
                'user_id' => Auth::id(),
                'text' => $request->text,
            ]);

            $comment->load('user');

            return response()->json([
                'message' => 'Comentario añadido exitosamente',
                'comment' => [
                    'id' => $comment->id,
                    'user_id' => $comment->user_id,
                    'user' => [
                        'id' => $comment->user ? $comment->user->id : null,
                        'name' => $comment->user ? $comment->user->name : 'Anónimo',
                    ],
                    'text' => $comment->text,
                    'created_at' => $comment->created_at,
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error al guardar comentario: ' . $e->getMessage());
            return response()->json(['message' => 'Error al guardar comentario'], 500);
        }
    }

    public function destroyComment(Screenshot $screenshot, Comment $comment)
    {
        try {
            if ($comment->commentable_id !== $screenshot->id || $comment->commentable_type !== get_class($screenshot) || $comment->user_id !== Auth::id()) {
                return response()->json(['message' => 'No autorizado'], 403);
            }

            $comment->delete();

            return response()->json(['message' => 'Comentario eliminado con éxito'], 200);
        } catch (\Exception $e) {
            Log::error('Error al eliminar comentario: ' . $e->getMessage());
            return response()->json(['message' => 'Error al eliminar comentario'], 500);
        }
    }

    public function like($id)
    {
        try {
            $screenshot = Screenshot::findOrFail($id);
            $user = Auth::user();

            if ($screenshot->usersWhoDisliked()->where('user_id', $user->id)->exists()) {
                $screenshot->decrement('dislikes');
                $screenshot->usersWhoDisliked()->detach($user->id);
            }

            if ($screenshot->usersWhoLiked()->where('user_id', $user->id)->exists()) {
                return response()->json(['message' => 'Ya has dado "Me gusta"'], 400);
            }

            $screenshot->increment('likes');
            $screenshot->usersWhoLiked()->attach($user->id);

            return response()->json([
                'message' => 'Like registrado',
                'likes' => $screenshot->likes,
                'dislikes' => $screenshot->dislikes,
                'popularity' => $screenshot->likes - $screenshot->dislikes,
                'user_liked' => true,
                'user_disliked' => false,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al dar like: ' . $e->getMessage());
            return response()->json(['message' => 'Error al dar like'], 500);
        }
    }

    public function dislike($id)
    {
        try {
            $screenshot = Screenshot::findOrFail($id);
            $user = Auth::user();

            if ($screenshot->usersWhoLiked()->where('user_id', $user->id)->exists()) {
                $screenshot->decrement('likes');
                $screenshot->usersWhoLiked()->detach($user->id);
            }

            if ($screenshot->usersWhoDisliked()->where('user_id', $user->id)->exists()) {
                return response()->json(['message' => 'Ya has dado "No me gusta"'], 400);
            }

            $screenshot->increment('dislikes');
            $screenshot->usersWhoDisliked()->attach($user->id);

            return response()->json([
                'message' => 'Dislike registrado',
                'likes' => $screenshot->likes,
                'dislikes' => $screenshot->dislikes,
                'popularity' => $screenshot->likes - $screenshot->dislikes,
                'user_liked' => false,
                'user_disliked' => true,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al dar dislike: ' . $e->getMessage());
            return response()->json(['message' => 'Error al dar dislike'], 500);
        }
    }
}