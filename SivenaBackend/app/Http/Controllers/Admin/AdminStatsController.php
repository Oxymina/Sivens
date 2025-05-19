<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Support\Facades\Gate;

class AdminStatsController extends Controller
{
    public function index()
    {
        Gate::authorize('access-admin-panel');

        return response()->json([
            'posts_count' => Post::count(),
            'users_count' => User::count(),
            'categories_count' => Category::count(),
            'tags_count' => Tag::count(),
        ]);
    }
}