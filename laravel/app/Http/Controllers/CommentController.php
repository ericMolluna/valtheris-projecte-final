<?php
namespace App\Http\Controllers;

use App\Models\Screenshot;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, $id)
    {
        $request->validate([
            'text' => 'required|string',
        ]);

        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        $screenshot = Screenshot::findOrFail($id);

        $comment = new Comment();
        $comment->text = $request->text;
        $comment->user_id = $user->id;
        $comment->screenshot_id = $screenshot->id;
        $comment->save();

        $comment->load('user');

        return response()->json($comment, 201);
    }

    public function index($id)
    {
        $screenshot = Screenshot::findOrFail($id);
        $comments = $screenshot->comments()->with('user')->get();
        return response()->json($comments);
    }
}
