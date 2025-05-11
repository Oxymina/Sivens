<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role; // Make sure to import the Role model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log; // For logging

class RegisterController extends Controller
{
    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        // Validate incoming request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email', // Ensure unique validation for users table, email column
            'password' => 'required|string|min:8|confirmed', // Added min:8 and confirmed (requires password_confirmation field)
        ]);

        // If validation fails, return errors with a 422 status code
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // --- ASSIGN DEFAULT ROLE ---
        // Find the default 'reader' role
        // Role::ROLE_READER is a constant defined in your App\Models\Role model
        $defaultRole = Role::where('name', Role::ROLE_READER)->first();

        if (!$defaultRole) {
            // This should ideally not happen if roles are seeded correctly
            Log::error('Default "reader" role not found during registration.');
            return response()->json([
                'message' => 'Registration failed: Server configuration error. Please contact support.'
            ], 500);
        }
        // --- END ASSIGN DEFAULT ROLE ---

        try {
            // Create a new user with hashed password and the default role ID
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_id' => $defaultRole->id, // Assign the ID of the 'reader' role
            ]);

            // Create a new access token for the user
            // You can customize the token name if needed
            $token = $user->createToken('AuthTokenOnRegister')->accessToken;
            Log::info('User registered and token generated: ' . $user->email);

            // Return the token and user information (with role eager-loaded) in the response
            return response()->json([
                'message' => 'User registered successfully.',
                'token' => $token,
                'user' => $user->load('role') // Eager load the role for the response
            ], 201);

        } catch (\Exception $e) {
            Log::error('User registration error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Registration failed due to an unexpected error. Please try again later.'
            ], 500);
        }
    }
}