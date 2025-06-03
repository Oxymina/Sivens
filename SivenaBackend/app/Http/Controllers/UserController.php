<?php
// app/Http/Controllers/UserController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;


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
 public function updateProfileDetails(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id), // Check unique email, ignoring current user
            ],
        ]);

        $user->name = $validatedData['name'];
        $user->email = $validatedData['email'];
        $user->save();

        return response()->json([
            'message' => 'Profile details updated successfully.',
            'user' => $user->load('role') // Return updated user with role
        ]);
    }

    /**
     * Update the authenticated user's password.
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $validator = Validator::make($request->all(), [
            'current_password' => ['required', 'string'],
            'password' => [ // 'password' is the key for the new password for Laravel's 'confirmed' rule
                'required',
                'string',
                Password::min(8) // Use Laravel's built-in password rule object
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(), // Checks if password has appeared in data breaches (requires internet)
                'confirmed' // Requires 'password_confirmation' field in request
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if current password matches
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'errors' => ['current_password' => ['The provided current password does not match our records.']]
            ], 422);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json(['message' => 'Password updated successfully.']);
    }

    /**
     * Update the authenticated user's profile picture.
     */
    public function updateProfilePicture(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $request->validate([
            'userPFP' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // Max 5MB
        ]);

        if ($request->hasFile('userPFP')) {
            // Delete old PFP if it exists
            if ($user->userPFP && Storage::disk('public')->exists($user->userPFP)) {
                Storage::disk('public')->delete($user->userPFP);
            }

            // Store new PFP; store method returns relative path from 'storage/app/public/'
            $path = $request->file('userPFP')->store('profile_pictures', 'public');
            $user->userPFP = $path; // Save relative path like 'profile_pictures/filename.jpg'
            $user->save();

            // The frontend already constructs the full URL, so just return the user
            return response()->json([
                'message' => 'Profile picture updated successfully.',
                'user' => $user->load('role') // Return updated user with new PFP path
            ]);
        }

        return response()->json(['message' => 'No profile picture file was uploaded.'], 400);
    }
}