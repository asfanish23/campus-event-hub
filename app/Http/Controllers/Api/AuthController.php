<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Get data from JSON body
        $data = $request->json()->all() ?? $request->all();
        
        // Validate the data
        $validated = \Illuminate\Support\Facades\Validator::make($data, [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ])->validate();

        // Generate a unique student ID
        $studentId = 'STU' . strtoupper(substr(md5(time() . $validated['email']), 0, 8));

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'student',  // Mobile API registrations are always students
            'student_id' => $studentId,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token
        ], 201);
    }

    public function login(Request $request)
    {
        // Get data from JSON body
        $data = $request->json()->all() ?? $request->all();
        
        // Validate the data
        $validated = \Illuminate\Support\Facades\Validator::make($data, [
            'email' => 'required|email',
            'password' => 'required'
        ])->validate();

        if (!Auth::attempt($validated)) {
            return response()->json([
                'message' => 'Invalid login credentials'
            ], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token
        ]);
    }

    /**
     * Update user profile (including profile photo)
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = Auth::guard('sanctum')->user();
            if (!$user) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 401);
            }

            // Validate input
            $validated = \Illuminate\Support\Facades\Validator::make(
                array_merge($request->all(), $request->json()->all() ?? []),
                [
                    'name' => 'sometimes|string|max:255',
                    'email' => 'sometimes|email|unique:users,email,' . $user->id,
                    'phone' => 'sometimes|string|nullable',
                    'address' => 'sometimes|string|nullable',
                    'city' => 'sometimes|string|nullable',
                    'postal_code' => 'sometimes|string|nullable',
                    'profile_photo' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
                ]
            )->validate();

            // Update basic profile data
            if (isset($validated['name'])) {
                $user->name = $validated['name'];
            }
            if (isset($validated['email'])) {
                $user->email = $validated['email'];
            }
            if (isset($validated['phone'])) {
                $user->phone = $validated['phone'];
            }
            if (isset($validated['address'])) {
                $user->address = $validated['address'];
            }
            if (isset($validated['city'])) {
                $user->city = $validated['city'];
            }
            if (isset($validated['postal_code'])) {
                $user->postal_code = $validated['postal_code'];
            }

            // Handle profile photo upload
            if ($request->hasFile('profile_photo')) {
                // Delete old profile photo if it exists
                if ($user->profile_photo && \Storage::exists('public/' . $user->profile_photo)) {
                    \Storage::delete('public/' . $user->profile_photo);
                }

                // Store new profile photo
                $path = $request->file('profile_photo')->store('profile_photos', 'public');
                $user->profile_photo = $path;
            }

            $user->save();

            return response()->json([
                'message' => 'Profile updated successfully',
                'user' => $user
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update profile: ' . $e->getMessage()
            ], 500);
        }
    }
}
