<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

    class AdminPostController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('manage-any-post-admin');
        $query = Post::with(['author:id,name', 'category:id,name'])
                      ->withCount('likers as likes_count', 'comments as comments_count');

        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                  ->orWhereHas('author', function($authorQuery) use ($searchTerm) {
                      $authorQuery->where('name', 'like', $searchTerm);
                  });
            });
        }

        $sortBy = $request->input('sortBy', 'created_at');
        $sortDesc = $request->input('sortDesc', 'true') === 'true' ? 'desc' : 'asc';

        // Basic sorting for direct columns, related column sorting needs more work (joins or specific logic)
        $allowedSorts = ['title', 'created_at', 'updated_at']; // 'author.name', 'category.name'
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDesc);
        } elseif ($sortBy === 'author.name') {
            $query->select('posts.*') // Important to select all post columns to avoid conflicts
                  ->leftJoin('users', 'posts.author_id', '=', 'users.id')
                  ->orderBy('users.name', $sortDesc);
        } elseif ($sortBy === 'category.name') {
             $query->select('posts.*')
                  ->leftJoin('categories', 'posts.category_id', '=', 'categories.id')
                  ->orderBy('categories.name', $sortDesc);
        }
        else {
            $query->orderBy('posts.created_at', 'desc');
        }

        $posts = $query->paginate($request->input('perPage', 10));
        return response()->json($posts);
    }

    public function updateAuthor(Request $request, Post $post)
    {
        Gate::authorize('manage-any-post-admin');
        $request->validate([
            'author_id' => 'required|integer|exists:users,id',
        ]);

        $post->author_id = $request->author_id;
        $post->save();

        return response()->json($post->load('author:id,name'));
    }

    public function adminDestroyPost(Post $post) // Renamed to avoid conflict
    {
        Gate::authorize('manage-any-post-admin');
        $post->delete();
        return response()->json(['message' => 'Post deleted successfully by admin.'], 200);
    }
}