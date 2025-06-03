<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ImageUploadController extends Controller
{
    /**
     * Store a newly uploaded image for blog posts or other content.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if (!Auth::check()) {
             return response()->json(['message' => 'Unauthenticated.'], 401);
        }
        $user = Auth::user();
        if (!$user->isWriter() && !$user->isAdmin()) {
            return response()->json(['message' => 'You do not have permission to upload images.'], 403);
        }


        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();

            $storagePath = 'post_images';
            $path = $file->storeAs($storagePath, $fileName, 'public');

            if ($path) {
                $url = Storage::disk('public')->url($path);

                return response()->json([
                    'message' => 'Image uploaded successfully.',
                    'url' => $path,
                ], 201);
            } else {
                return response()->json(['message' => 'Failed to store image.'], 500);
            }
        }

        return response()->json(['message' => 'No image file was uploaded or the file is invalid.'], 400);
    }
}