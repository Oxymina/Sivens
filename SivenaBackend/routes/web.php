<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;

// Serve Vue.js application
Route::get('/{any}', function () {
    return view('main');
})->where('any', '.*');

// API routes for fetching data
Route::get('/api/posts', 'App\Http\Controllers\PostController@index');
Route::get('/api/categories', 'App\Http\Controllers\CategoryController@index');
Route::get('/api/tags', 'App\Http\Controllers\TagController@index');

// Authentication routes
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::post('/register', [RegisterController::class, 'register'])->name('register');

Route::get('/', function () {
    return view('welcome');
});
