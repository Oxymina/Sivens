<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment; // Import Comment model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // For getting authenticated user

class PostController extends Controller
{
    /**
     * Display a listing of posts.
     */
    public function index(Request $request)
    {
        // Eager load author and category with specific columns
        $query = Post::with(['author:id,name', 'category:id,name'])
        ->withCount('likers as likes_count');

        // Search functionality
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('content', 'like', '%' . $request->search . '%');
        }

        // Category filter
        if ($request->has('category')) {
            $query->where('category_id', $request->category);
        }

        // Order by newest first and paginate
        return $query->latest()->paginate(10);
    }

    /**
     * Display the specified post.
     */
    public function show($id)
    {
        // Find post and eager load relationships + counts
        $post = Post::with([
            'author:id,name,userPFP',    // Load author details
            'category:id,name',         // Load category details
            'comments' => function ($query) { // Load comments and their authors
                $query->with('user:id,name,userPFP')->latest();
            }
            // 'tags:id,name' // Uncomment if you have tags
        ])
        ->withCount('likers as likes_count') // Get like count dynamically
        ->find($id);

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        return response()->json($post);
    }

    /**
     * Store a newly created post in storage.
     * Protected by middleware in api.php
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'post_image' => 'nullable|string', // Handle file uploads separately if needed
            // 'tags' => 'nullable|array',
            // 'tags.*' => 'exists:tags,id'
        ]);

        $post = new Post($validatedData);
        $post->author_id = Auth::id(); // Set author to current user
        $post->save();

        // If you sync tags
        // if ($request->has('tags')) {
        //    $post->tags()->sync($request->tags);
        // }

        return response()->json($post->load(['author:id,name', 'category:id,name']), 201);
    }

    /**
     * Update the specified post in storage.
     * Protected by middleware in api.php
     */
    public function update(Request $request, $id)
    {
        $post = Post::find($id);

        if (!$post) {
             return response()->json(['message' => 'Post not found'], 404);
        }

        // Optional: Add authorization check - ensure current user IS the author
        // if (Auth::id() !== $post->author_id) {
        //      return response()->json(['message' => 'Forbidden'], 403);
        // }

        $validatedData = $request->validate([
             'title' => 'sometimes|required|string|max:255', // sometimes = only validate if present
             'content' => 'sometimes|required|string',
             'category_id' => 'sometimes|required|exists:categories,id',
             'post_image' => 'nullable|string',
        ]);

        $post->update($validatedData);

        // Handle tags update if necessary

        return response()->json($post->load(['author:id,name', 'category:id,name']), 200);
    }

    /**
     * Remove the specified post from storage.
     * Protected by middleware in api.php
     */
    public function destroy($id)
    {
        $post = Post::find($id);

         if (!$post) {
             return response()->json(['message' => 'Post not found'], 404);
         }

        // Optional: Add authorization check - ensure current user IS the author
        // if (Auth::id() !== $post->author_id) {
        //      return response()->json(['message' => 'Forbidden'], 403);
        // }

        $post->delete();

        return response()->json(null, 204); // No content on success
    }

    /**
     * Display posts created by the currently authenticated user.
     * Protected by middleware in api.php
     */
    public function userPosts(Request $request)
    {
        $user = Auth::user();
        // Auth::user() can be null if middleware isn't working perfectly, though middleware should prevent this call
        if (!$user) {
             return response()->json(['message' => 'Unauthenticated.'], 401);
        }
        $posts = Post::where('author_id', $user->id)
                        ->with(['category:id,name']) // Load category
                        ->latest()
                        ->paginate(10);
        return response()->json($posts);
    }

    // --- COMMENT METHODS ---

    /**
     * Get comments for a specific post.
     */
    public function getComments(Request $request, $postId)
    {
        $post = Post::find($postId);
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }
        // Load comments with their authors (user)
        $comments = $post->comments()->with('user:id,name,userPFP')->latest()->paginate(15);
        return response()->json($comments);
    }

    /**
     * Store a new comment for a specific post.
     * Protected by middleware in api.php
     */
    public function storeComment(Request $request, $postId)
    {
        $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        $post = Post::find($postId);
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $comment = new Comment();
        $comment->post_id = $post->id;
        $comment->user_id = Auth::id(); // Currently authenticated user
        $comment->content = $request->content;
        $comment->save();

        // Return the new comment with user details
        return response()->json($comment->load('user:id,name,userPFP'), 201);
    }

    // --- LIKE METHOD ---
    public function toggleLike(Request $request, $postId)
    {
        $user = Auth::user(); // Middleware ensures user exists
        $post = Post::find($postId);

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        // Attach/detach the like using the many-to-many relationship
        $result = $user->likedPosts()->toggle($post->id);

        $isLiked = count($result['attached']) > 0;

        // Update likes count dynamically (assumes likes_count is appended/calculated)
        // $post->refresh(); // Reload the post to get updated counts/relationships if needed
        // $likesCount = $post->likes_count; // Use the dynamic attribute/relationship count

        return response()->json([
            'message' => $isLiked ? 'Post liked' : 'Post unliked',
            'is_liked' => $isLiked,
            'likes_count' => $post->likers()->count() // Recalculate count
        ], 200);
    }

     // Add other methods like updateComment, destroyComment, etc. as needed
     // Remember to add Authorization checks where appropriate (e.g., user can only edit/delete their own posts/comments)
}