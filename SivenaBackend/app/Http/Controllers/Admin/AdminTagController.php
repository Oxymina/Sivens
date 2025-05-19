<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag; // Use Tag model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AdminTagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('manage-tags');

        $query = Tag::withCount('posts'); // Eager load post count associated with tags

        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where('name', 'like', $searchTerm);
        }

        $sortBy = $request->input('sortBy', 'name');
        $sortDesc = $request->input('sortDesc', 'false') === 'true' ? 'desc' : 'asc';

        $allowedSorts = ['id', 'name', 'posts_count', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDesc);
        } else {
            $query->orderBy('name', 'asc');
        }

        $tags = $query->paginate($request->input('perPage', 15));
        return response()->json($tags);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('manage-tags');

        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:tags,name',
            // 'slug' => 'nullable|string|max:255|unique:tags,slug',
        ]);

        // if (empty($validatedData['slug']) && !empty($validatedData['name'])) {
        //     $validatedData['slug'] = Str::slug($validatedData['name']);
        // }

        $tag = Tag::create($validatedData);
        return response()->json($tag->loadCount('posts'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Tag $tag)
    {
        Gate::authorize('manage-tags');
        return response()->json($tag->loadCount('posts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tag $tag)
    {
        Gate::authorize('manage-tags');

        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:tags,name,' . $tag->id,
            // 'slug' => 'nullable|string|max:255|unique:tags,slug,' . $tag->id,
        ]);

        // if (empty($validatedData['slug']) && !empty($validatedData['name'])) {
        //     $validatedData['slug'] = Str::slug($validatedData['name']);
        // }

        $tag->update($validatedData);
        return response()->json($tag->loadCount('posts'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tag $tag)
    {
        Gate::authorize('manage-tags');
        // Detach from all posts before deleting if using many-to-many with cascade not set or on a different model
        // $tag->posts()->detach(); // if post_tag table doesn't cascade deletes on tag_id
        $tag->delete();
        return response()->json(['message' => 'Tag deleted successfully.'], 200);
    }

    /**
     * Return a simple list of tags (id, name) for dropdowns/autocomplete.
     */
    public function listSimple()
    {
        // This might not need admin gate if public components use it for filtering
        return response()->json(Tag::orderBy('name')->get(['id', 'name']));
    }
}