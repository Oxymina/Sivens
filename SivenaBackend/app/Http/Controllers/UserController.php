<?php
// app/Http/Controllers/UserController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage; // For handling userPFP paths if storing locally

class UserController extends Controller
{
    // Fetch authenticated user profile
    public function profile(Request $request) // Request object automatically injected
    {
        $user = Auth::user(); // Or $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Ensure the 'role' relationship is loaded
        $user->load('role');

        // If userPFP is a path to a publicly accessible storage disk, construct the URL
        if ($user->userPFP) {
            // This assumes you've run 'php artisan storage:link' and 'userPFP' stores a path like 'profile_pictures/filename.jpg'
            $user->userPFP_url = Storage::disk('public')->url($user->userPFP); // More robust way to get URL
            // OR if asset() was working correctly for you:
            // $user->userPFP_url = asset('storage/' . $user->userPFP);
        } else {
            $user->userPFP_url = null; // Explicitly set if no PFP
        }


        return response()->json($user); // User object will now include 'role' and 'role_name' (due to $appends in User model)
                                        // and 'userPFP_url'
    }

    // Update user profile
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
         if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255', // 'sometimes' means only validate if present
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id, // Ignore current user's email
             // Don't allow password change here unless it's a dedicated password change form/flow
        ]);

        if ($request->has('name')) {
             $user->name = $validatedData['name'];
        }
        if ($request->has('email')) {
            $user->email = $validatedData['email'];
        }

        if ($request->hasFile('userPFP')) {
            $request->validate([
                'userPFP' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048', // Add validation for image
            ]);

            // Delete old PFP if it exists
            if ($user->userPFP && Storage::disk('public')->exists($user->userPFP)) {
                Storage::disk('public')->delete($user->userPFP);
            }

            $path = $request->file('userPFP')->store('profile_pictures', 'public');
            $user->userPFP = $path;
        }

        $user->save();

        // Return updated user, load role, and potentially new PFP URL
        $user->load('role');
        if ($user->userPFP) {
             $user->userPFP_url = Storage::disk('public')->url($user->userPFP);
        } else {
             $user->userPFP_url = null;
        }


        return response()->json($user);
    }
}