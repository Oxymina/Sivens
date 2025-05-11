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
Route::post('/posts', [PostController::class, 'store']);
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