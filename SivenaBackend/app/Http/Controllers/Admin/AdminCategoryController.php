<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AdminCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('manage-categories');

        $query = Category::withCount('posts'); // Eager load post count

        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where('name', 'like', $searchTerm);
        }

        $sortBy = $request->input('sortBy', 'name');
        $sortDesc = $request->input('sortDesc', 'false') === 'true' ? 'desc' : 'asc'; // Default to asc for names

        // Validate sortBy against allowed columns
        $allowedSorts = ['id', 'name', 'posts_count', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDesc);
        } else {
            $query->orderBy('name', 'asc'); // Default sort
        }

        $categories = $query->paginate($request->input('perPage', 15));
        return response()->json($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('manage-categories');

        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            // 'slug' => 'nullable|string|max:255|unique:categories,slug', // If you use slugs
        ]);

        // if (empty($validatedData['slug']) && !empty($validatedData['name'])) {
        //     $validatedData['slug'] = Str::slug($validatedData['name']);
        // }

        $category = Category::create($validatedData);

        return response()->json($category->loadCount('posts'), 201);
    }

    /**
     * Display the specified resource (not typically needed for admin list, but good practice).
     */
    public function show(Category $category)
    {
        Gate::authorize('manage-categories');
        return response()->json($category->loadCount('posts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        Gate::authorize('manage-categories');

        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            // 'slug' => 'nullable|string|max:255|unique:categories,slug,' . $category->id,
        ]);

        // if (empty($validatedData['slug']) && !empty($validatedData['name'])) {
        //     $validatedData['slug'] = Str::slug($validatedData['name']);
        // }

        $category->update($validatedData);

        return response()->json($category->loadCount('posts'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        Gate::authorize('manage-categories');

        // Optional: Check if category is in use before deleting, or handle with DB constraints
        if ($category->posts()->count() > 0) {
            return response()->json(['message' => 'Cannot delete category. It is currently assigned to posts. Please reassign posts first.'], 422);
        }

        $category->delete();
        return response()->json(['message' => 'Category deleted successfully.'], 200);
    }

    /**
     * Return a simple list of categories (id, name) for dropdowns.
     */
    public function listSimple()
    {
        // This might not need admin gate if public components use it for filtering
        // Gate::authorize('manage-categories');
        return response()->json(Category::orderBy('name')->get(['id', 'name']));
    }
}