<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    // Fetch authenticated user profile
    public function profile()
    {
        $user = Auth::user();
        return response()->json($user);
    }

    // Update user profile
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        
        // Check if profile picture is being updated
        if ($request->hasFile('userPFP')) {
            $file = $request->file('userPFP');
            $path = $file->store('profile_pictures', 'public');
            $user->userPFP = $path;
        }

        $user->save();

        return response()->json($user);
    }
}
