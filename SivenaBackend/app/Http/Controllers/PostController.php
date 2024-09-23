<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::query();

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('content', 'like', '%' . $request->search . '%');
        }

        if ($request->has('category')) {
            $query->where('category_id', $request->category);
        }

        return $query->get();
    }

    public function show($id)
    {
        return Post::find($id);
    }

    public function store(Request $request)
    {
        $post = new Post();
        $post->title = $request->title;
        $post->content = $request->content;
        $post->post_image = $request->post_image;
        $post->category_id = $request->category_id;
        $post->author_id = $request->author_id;
        $post->save();

        return response()->json($post, 201);
    }

    public function update(Request $request, $id)
    {
        $post = Post::find($id);
        $post->title = $request->title;
        $post->content = $request->content;
        $post->post_image = $request->post_image;
        $post->category_id = $request->category_id;
        $post->author_id = $request->author_id;
        $post->save();

        return response()->json($post, 200);
    }

    public function destroy($id)
    {
        $post = Post::find($id);
        $post->delete();

        return response()->json(null, 204);
    }

    public function userPosts(Request $request)
    {
        $user = $request->user();
        $posts = Post::where('author_id', $user->id)->get();
        return response()->json($posts);
    }
}
