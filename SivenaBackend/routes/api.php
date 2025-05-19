<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminStatsController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminRoleController;
Use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminTagController;
Use App\Http\Controllers\Admin\AdminCommentController;
Use App\Http\Controllers\Admin\AdminMessageController;
Use App\Http\Controllers\Admin\AdminPostController;

use Laravel\Passport\Http\Controllers\AccessTokenController;
use Laravel\Passport\Http\Controllers\AuthorizationController;  
use Laravel\Passport\Http\Controllers\ApproveAuthorizationController;
use Laravel\Passport\Http\Controllers\DenyAuthorizationController;
use Laravel\Passport\Http\Controllers\TransientTokenController;

// Authentication Routes
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth:api');
Route::post('/register', [RegisterController::class, 'register']);

// Post Routes
Route::get('/posts', [PostController::class, 'index']);
Route::post('/posts', [PostController::class, 'store'])->middleware('auth:api');
Route::get('/posts/{id}', [PostController::class, 'show']);
Route::put('/posts/{id}', [PostController::class, 'update']);
Route::delete('/posts/{id}', [PostController::class, 'destroy']);
Route::get('/user-posts', [PostController::class, 'userPosts'])->middleware('auth:api');

// Category Routes
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);
Route::post('/categories', [CategoryController::class, 'store']);
Route::put('/categories/{id}', [CategoryController::class, 'update']);
Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

// Tag Routes
Route::get('/tags', [TagController::class, 'index']);
Route::get('/tags/{id}', [TagController::class, 'show']);
Route::post('/tags', [TagController::class, 'store']);
Route::put('/tags/{id}', [TagController::class, 'update']);
Route::delete('/tags/{id}', [TagController::class, 'destroy']);

// Message Routes
Route::post('/messages', [MessageController::class, 'store']);

// User Routes
Route::middleware('auth:api')->get('/users', [UserController::class, 'profile']);
Route::middleware('auth:api')->post('/users/update', [UserController::class, 'updateProfile']);

// Like Routes
Route::middleware('auth:api')->post('/posts/{postId}/like', [PostController::class, 'toggleLike']);

// Comment Routes
Route::get('/posts/{postId}/comments', [PostController::class, 'getComments']);
Route::middleware('auth:api')->post('/posts/{postId}/comments', [PostController::class, 'storeComment']);

Route::post('/oauth/token', [AccessTokenController::class, 'issueToken'])
    ->middleware(['throttle']);
Route::get('/oauth/authorize', [AuthorizationController::class, 'authorize']);
Route::post('/oauth/approve', [ApproveAuthorizationController::class, 'approve']);
Route::delete('/oauth/deny', [DenyAuthorizationController::class, 'deny']);
Route::post('/oauth/token/refresh', [TransientTokenController::class, 'refresh']);

//admin routes ( api/admin/[route])

Route::prefix('admin')->middleware(['auth:api', 'can:access-admin-panel'])->group(function () {
    // Dashboard Stats
    Route::get('/stats', [AdminStatsController::class, 'index']);

    // User Management
    Route::get('/users', [AdminUserController::class, 'index'])->can('manage-users');
    Route::get('/users-list', [AdminUserController::class, 'listSimple'])->can('manage-users'); // For dropdowns
    Route::put('/users/{user}/role', [AdminUserController::class, 'updateRole'])->can('manage-users');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->can('manage-users');

    // Roles (for user edit dialog)
    Route::get('/roles', [AdminRoleController::class, 'index'])->can('manage-users');

    // Post Management (Admin view, focused on moderation)
    Route::get('/posts', [AdminPostController::class, 'index'])->can('manage-any-post-admin');
    Route::put('/posts/{post}/author', [AdminPostController::class, 'updateAuthor'])->can('manage-any-post-admin');
    Route::delete('/posts/{post}', [AdminPostController::class, 'adminDestroyPost'])->can('manage-any-post-admin');

    // Category Management
    Route::get('/categories', [AdminCategoryController::class, 'index'])->can('manage-categories');
    Route::post('/categories', [AdminCategoryController::class, 'store'])->can('manage-categories');
    Route::put('/categories/{category}', [AdminCategoryController::class, 'update'])->can('manage-categories');
    Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy'])->can('manage-categories');
    Route::get('/categories-list', [AdminCategoryController::class, 'listSimple'])->can('manage-categories');


    // Tag Management
    Route::get('/tags', [AdminTagController::class, 'index'])->can('manage-tags');
    Route::post('/tags', [AdminTagController::class, 'store'])->can('manage-tags');
    Route::put('/tags/{tag}', [AdminTagController::class, 'update'])->can('manage-tags');
    Route::delete('/tags/{tag}', [AdminTagController::class, 'destroy'])->can('manage-tags');

    // Comment Management
    Route::get('/posts/{post}/comments', [AdminCommentController::class, 'indexForPost'])->can('manage-comments-admin');
    Route::delete('/comments/{comment}', [AdminCommentController::class, 'destroy'])->can('manage-comments-admin');

    // Contact Messages
    Route::get('/messages', [AdminMessageController::class, 'index'])->can('manage-messages');
    Route::delete('/messages/{message}', [AdminMessageController::class, 'destroy'])->can('manage-messages');
});