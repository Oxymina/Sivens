<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AdminCommentController extends Controller
{
    /**
     * Display a listing of comments for a specific post (admin view).
     */
    public function indexForPost(Request $request, Post $post) // Route model binding for Post
    {
        Gate::authorize('manage-comments');

        $query = $post->comments()->with('user:id,name,userPFP'); // Eager load user of comment

        // You could add search for comment content here if needed
        // if ($request->filled('search')) {
        //    $query->where('content', 'like', '%' . $request->search . '%');
        // }

        $sortBy = $request->input('sortBy', 'created_at');
        $sortDesc = $request->input('sortDesc', 'true') === 'true' ? 'desc' : 'asc';

        $allowedSorts = ['created_at', 'id']; // Add 'user.name' if you implement joined sorting
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDesc);
        } else {
            $query->orderBy('created_at', 'desc'); // Default sort
        }

        $comments = $query->paginate($request->input('perPage', 15));
        return response()->json($comments);
    }

    /**
     * Remove the specified comment from storage by admin.
     */
    public function destroy(Comment $comment) // Route model binding
    {
        Gate::authorize('manage-comments');

        $comment->delete();
        return response()->json(['message' => 'Comment deleted successfully by admin.'], 200);
    }
}