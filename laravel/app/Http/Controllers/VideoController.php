<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class VideoController extends Controller
{
    public function store(Request $request)
    {
        try {

            $thumbnailPath = null;
            if ($request->hasFile('thumbnail')) {
                $thumbnailFile = $request->file('thumbnail');
                $thumbnailName = time() . '_' . $thumbnailFile->getClientOriginalName();
                $thumbnailPath = $thumbnailFile->storeAs('thumbnails', $thumbnailName, 'public');
                Log::info('Thumbnail almacenado en: storage/app/public/' . $thumbnailPath);
            }
            Log::info('Authorization header', ['header' => $request->header('Authorization')]);
            Log::info('Auth check', ['user' => auth()->user(), 'check' => auth()->check()]);

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'video' => 'required|file|mimes:mp4,mov,avi|max:10240',
                'description' => 'nullable|string',
                'thumbnail' => 'nullable|file|mimes:jpeg,png,jpg|max:2048',
            ]);

            if (!auth()->check()) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            // Log video file details
            Log::info('Video upload attempt', [
                'video_mime' => $request->file('video') ? $request->file('video')->getMimeType() : 'No file',
                'video_size' => $request->file('video') ? $request->file('video')->getSize() / 1024 : 'No file',
                'video_extension' => $request->file('video') ? $request->file('video')->getClientOriginalExtension() : 'No file',
                'thumbnail_mime' => $request->file('thumbnail') ? $request->file('thumbnail')->getMimeType() : 'No file',
                'thumbnail_size' => $request->file('thumbnail') ? $request->file('thumbnail')->getSize() / 1024 : 'No file',
            ]);

            // Store the video
            $videoFile = $request->file('video');
            $videoName = time() . '_' . $videoFile->getClientOriginalName();
            $videoPath = $videoFile->storeAs('videos', $videoName, 'public');
            Log::info('Video almacenado en: storage/app/public/' . $videoPath);

            // Store the thumbnail (if provided)
            $thumbnailPath = null;
            if ($request->hasFile('thumbnail')) {
                $thumbnailFile = $request->file('thumbnail');
                $thumbnailName = time() . '_' . $thumbnailFile->getClientOriginalName();
                $thumbnailPath = $thumbnailFile->storeAs('thumbnails', $thumbnailName, 'public');
                Log::info('Thumbnail almacenado en: storage/app/public/' . $thumbnailPath);
            }

            // Create the video record
            $video = Video::create([
                'user_id' => auth()->id(),
                'title' => $validated['title'],
                'video_url' => $videoPath,
                'thumbnail_url' => $thumbnailPath, // Store thumbnail path or null
                'description' => $validated['description'],
            ]);

            return response()->json([
                'id' => $video->id,
                'user_id' => $video->user_id,
                'username' => auth()->user()->name,
                'title' => $video->title,
                'video_url' => Storage::url($videoPath), // /storage/videos/filename.mp4
                'thumbnail_url' => $thumbnailPath ? Storage::url($thumbnailPath) : null, // /storage/thumbnails/filename.jpg or null
                'description' => $video->description,
                'popularity' => 0,
                'likes' => 0,
                'dislikes' => 0,
                'created_at' => $video->created_at,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::info('Validation failed', [
                'errors' => $e->errors(),
                'video_mime' => $request->file('video') ? $request->file('video')->getMimeType() : 'No file',
                'video_size' => $request->file('video') ? $request->file('video')->getSize() / 1024 : 'No file',
                'video_extension' => $request->file('video') ? $request->file('video')->getClientOriginalExtension() : 'No file',
                'thumbnail_mime' => $request->file('thumbnail') ? $request->file('thumbnail')->getMimeType() : 'No file',
                'thumbnail_size' => $request->file('thumbnail') ? $request->file('thumbnail')->getSize() / 1024 : 'No file',
            ]);
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Video upload error: ' . $e->getMessage() . "\nStack trace: " . $e->getTraceAsString());
            return response()->json(['message' => 'Error uploading video: ' . $e->getMessage()], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $query = Video::with('user');

            if ($request->query('user') === 'me') {
                $user = Auth::user();
                if (!$user) {
                    return response()->json(['message' => 'Unauthorized'], 401);
                }
                $query->where('user_id', $user->id);
            }

            if ($request->has('sort')) {
                $sort = $request->input('sort');
                if ($sort === 'popular') {
                    $query->orderByDesc('popularity');
                } elseif ($sort === 'recent') {
                    $query->latest();
                }
            } else {
                $query->latest();
            }

            $videos = $query->get();
            Log::info('Raw videos data:', $videos->toArray()); // Log raw data

            $videos = $videos->map(function ($video) {
                return [
                    'id' => $video->id,
                    'user_id' => $video->user_id,
                    'username' => $video->user->name,
                    'title' => $video->title,
                    'description' => $video->description,
                    'video_url' => Storage::url($video->video_url),
                    'thumbnail_url' => $video->thumbnail_url ? Storage::url($video->thumbnail_url) : null,
                    'popularity' => $video->popularity,
                    'likes' => $video->likes()->count(),
                    'dislikes' => $video->dislikes()->count(),
                    'created_at' => $video->created_at,
                    'updated_at' => $video->updated_at,
                ];
            });

            return response()->json($videos, 200);
        } catch (\Exception $e) {
            Log::error('Error fetching videos: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching videos'], 500);
        }
    }
    public function show($id)
    {
        try {
            $video = Video::with(['user', 'likes', 'dislikes'])->findOrFail($id);
            return response()->json([
                'id' => $video->id,
                'user_id' => $video->user_id,
                'username' => $video->user->name,
                'title' => $video->title,
                'video_url' => Storage::url($video->video_url),
                'thumbnail_url' => $video->thumbnail_url ? Storage::url($video->thumbnail_url) : null,
                'description' => $video->description,
                'popularity' => $video->likes->count() - $video->dislikes->count(),
                'created_at' => $video->created_at,
                'likes' => $video->likes()->count(),
                'dislikes' => $video->dislikes()->count(),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching video: ' . $e->getMessage());
            return response()->json(['message' => 'Video not found'], 404);
        }
    }

    public function destroy(Video $video)
    {
        try {
            if ($video->user_id !== Auth::id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            Storage::delete($video->video_url);
            if ($video->thumbnail_url) {
                Storage::delete($video->thumbnail_url);
            }
            $video->delete();

            return response()->json(['message' => 'Video deleted successfully'], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting video: ' . $e->getMessage());
            return response()->json(['message' => 'Error deleting video'], 500);
        }
    }



    public function comments($id)
    {
        try {
            Log::info('Fetching comments for video ID: ' . $id);
            $video = Video::findOrFail($id);
            $comments = $video->comments()->with('user')->get();
            Log::info('Comments retrieved: ' . $comments->count());
            $mappedComments = $comments->map(function ($comment) {
                Log::info('Mapping comment ID: ' . $comment->id);
                return [
                    'id' => $comment->id,
                    'user' => [
                        'id' => $comment->user->id,
                        'name' => $comment->user->name,
                    ],
                    'text' => $comment->text,
                    'created_at' => $comment->created_at,
                ];
            });
            return response()->json($mappedComments, 200);
        } catch (\Exception $e) {
            Log::error('Error fetching video comments: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching comments: ' . $e->getMessage()], 500);
        }
    }


    public function storeComment(Request $request, $id)
    {
        try {
            $request->validate([
                'text' => 'required|string|max:1000',
            ]);

            $video = Video::findOrFail($id);
            $comment = $video->comments()->create([
                'user_id' => Auth::id(),
                'text' => $request->text,
            ]);

            return response()->json([
                'message' => 'Comment added successfully',
                'comment' => [
                    'id' => $comment->id,
                    'user' => [
                        'id' => Auth::user()->id,
                        'name' => Auth::user()->name,
                    ],
                    'text' => $comment->text,
                    'created_at' => $comment->created_at,
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error adding comment to video: ' . $e->getMessage());
            return response()->json(['message' => 'Error adding comment: ' . $e->getMessage()], 500);
        }
    }

    public function destroyComment(Video $video, Comment $comment)
    {
        try {
            if ($comment->user_id !== Auth::id() || $comment->video_id !== $video->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $comment->delete();
            return response()->json(['message' => 'Comment deleted successfully'], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting comment: ' . $e->getMessage());
            return response()->json(['message' => 'Error deleting comment'], 500);
        }
    }

    public function like($id)
    {
        try {
            $video = Video::findOrFail($id);
            $user = Auth::user();

            if ($video->dislikes()->where('user_id', $user->id)->exists()) {
                $video->dislikes()->detach($user->id);
            }

            if (!$video->likes()->where('user_id', $user->id)->exists()) {
                $video->likes()->attach($user->id);
            }

            $video->popularity = $video->likes()->count() - $video->dislikes()->count();
            $video->save();

            return response()->json([
                'message' => 'Video liked successfully',
                'popularity' => $video->popularity,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error liking video: ' . $e->getMessage());
            return response()->json(['message' => 'Error liking video'], 500);
        }
    }

    public function dislike($id)
    {
        try {
            $video = Video::findOrFail($id);
            $user = Auth::user();

            if ($video->likes()->where('user_id', $video->id)->exists()) {
                $video->likes()->detach($user->id);
            }

            if (!$video->dislikes()->where('user_id', $user->id)->exists()) {
                $video->dislikes()->attach($user->id);
            }

            $video->popularity = $video->likes()->count() - $video->dislikes()->count();
            $video->save();

            return response()->json([
                'message' => 'Video disliked successfully',
                'popularity' => $video->popularity,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error disliking video: ' . $e->getMessage());
            return response()->json(['message' => 'Error disliking video'], 500);
        }
    }
}
