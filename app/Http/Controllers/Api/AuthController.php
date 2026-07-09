<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Events\PasswordReset;
use App\Services\ResendEmailService;

class AuthController extends Controller
{
    private function getSanctumUser(): ?User
    {
        $authUser = Auth::guard('sanctum')->user();

        if (!$authUser) {
            return null;
        }

        return User::query()->find($authUser->getAuthIdentifier());
    }

    public function register(Request $request)
    {
        // Get data from JSON body
        $data = $request->json()->all() ?? $request->all();
        
        // Validate the data
        $validated = \Illuminate\Support\Facades\Validator::make($data, [
            'name'       => 'required|string',
            'email'      => 'required|email|unique:users',
            'password'   => 'required|min:6',
            'student_id' => 'sometimes|string|nullable',
            'faculty'    => 'required|string|in:' . implode(',', User::FACULTIES),
            'phone'      => 'sometimes|string|nullable',
            'address'    => 'sometimes|string|nullable',
            'state'      => 'sometimes|string|nullable',
            'city'       => 'sometimes|string|nullable',
            'postal_code'=> 'sometimes|string|nullable',
        ])->validate();

        // Use provided student_id or generate one
        $studentId = !empty($validated['student_id']) 
            ? $validated['student_id']
            : 'STU' . strtoupper(substr(md5(time() . $validated['email']), 0, 8));

        $user = User::create([
            'name'        => $validated['name'],
            'email'       => $validated['email'],
            'password'    => Hash::make($validated['password']),
            'role'        => 'student',  // Mobile API registrations are always students
            'student_id'  => $studentId,
            'faculty'     => $validated['faculty'] ?? null,
            'phone'       => $validated['phone'] ?? null,
            'address'     => $validated['address'] ?? null,
            'state'       => $validated['state'] ?? null,
            'city'        => $validated['city'] ?? null,
            'postal_code' => $validated['postal_code'] ?? null,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'user'    => $user,
            'token'   => $token
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

        $user = User::query()->find(Auth::id());
        if (!$user) {
            return response()->json([
                'message' => 'Authenticated user not found'
            ], 404);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token
        ]);
    }

    public function requestPasswordReset(Request $request, ResendEmailService $resendEmailService)
    {
        $validated = \Illuminate\Support\Facades\Validator::make(
            $request->json()->all() ?? $request->all(),
            [
                'email' => 'required|email',
            ]
        )->validate();

        // Allow reset for the same roles supported by web auth flow.
        $user = User::where('email', $validated['email'])
            ->whereIn('role', ['admin', 'super_admin', 'student'])
            ->first();

        if (!$user) {
            return response()->json([
                'message' => 'We could not find an account with that email address.',
            ], 404);
        }

        try {
            $token = Password::createToken($user);
            $resetUrl = route('password.reset', ['token' => $token, 'email' => $user->email]);

            $sent = $resendEmailService->sendPasswordResetEmail(
                $user->email,
                $user->name ?? 'User',
                $resetUrl
            );

            if (!$sent) {
                return response()->json([
                    'message' => 'Unable to send reset link. Please try again later.',
                ], 500);
            }

            return response()->json([
                'message' => 'Password reset link sent',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send password reset email via API.', [
                'email' => $validated['email'],
                'error_message' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Unable to send reset link. Please try again later.',
            ], 500);
        }
    }

    public function resetPassword(Request $request)
    {
        $payload = $request->json()->all() ?? $request->all();

        $validated = \Illuminate\Support\Facades\Validator::make($payload, [
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ])->validate();

        $status = Password::reset(
            [
                'email' => $validated['email'],
                'password' => $validated['password'],
                'token' => $validated['token'],
            ],
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => bcrypt($password),
                ])->save();

                $user->tokens()->delete();
                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Password reset successful',
            ]);
        }

        return response()->json([
            'message' => trans($status),
        ], 422);
    }

    /**
     * Update user profile (including profile photo)
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = $this->getSanctumUser();
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
                    'student_id' => 'sometimes|string|nullable',
                    'faculty' => 'sometimes|string|in:' . implode(',', User::FACULTIES),
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
            if (isset($validated['student_id'])) {
                $user->student_id = $validated['student_id'];
            }
            if (isset($validated['faculty'])) {
                $user->faculty = $validated['faculty'];
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
                if ($user->profile_photo && Storage::exists('public/' . $user->profile_photo)) {
                    Storage::delete('public/' . $user->profile_photo);
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

    /**
     * Get user's registered events (joined events)
     */
    public function getUserRegistrations(Request $request)
    {
        try {
            $user = $this->getSanctumUser();
            if (!$user) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 401);
            }

            $registrations = $user->registrations()
                ->with('event')
                ->get();

            $events = $registrations->map(function ($registration) {
                return $registration->event;
            })->filter();

            return response()->json([
                'success' => true,
                'message' => 'User registrations retrieved successfully',
                'count' => $events->count(),
                'data' => $events
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve registrations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's orders (cart items)
     */
    public function getUserOrders(Request $request)
    {
        try {
            $user = $this->getSanctumUser();
            if (!$user) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 401);
            }

            $orders = $user->orders()
                ->with('product')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'User orders retrieved successfully',
                'count' => $orders->count(),
                'data' => $orders
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve orders: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getFaculties()
    {
        return response()->json([
            'success' => true,
            'data' => User::FACULTIES
        ]);
    }

    public function googleLogin(Request $request, \App\Services\GoogleAuthService $googleAuthService)
    {
        $data = $request->json()->all() ?? $request->all();
        
        $validated = \Illuminate\Support\Facades\Validator::make($data, [
            'access_token' => 'required|string',
        ])->validate();

        $accessToken = $validated['access_token'];

        try {
            $googleProvider = \Laravel\Socialite\Facades\Socialite::driver('google');
            if (!$googleProvider instanceof \Laravel\Socialite\Two\AbstractProvider) {
                return response()->json([
                    'message' => 'Google provider configuration error'
                ], 500);
            }

            // Retrieve Google user statelessly using access token via Socialite
            $googleUser = $googleProvider
                ->stateless()
                ->userFromToken($accessToken);

            if (!$googleUser) {
                return response()->json([
                    'message' => 'Failed to retrieve user info from Google'
                ], 401);
            }

            // Process via the shared service (allowAutoCreate = false for mobile login check)
            $result = $googleAuthService->processGoogleUser($googleUser, null, false);

            if ($result['status'] === 'success') {
                $user = $result['user'];
                $token = $user->createToken('auth_token')->plainTextToken;

                return response()->json([
                    'status' => 'success',
                    'message' => 'Login successful',
                    'user' => $user,
                    'token' => $token
                ]);
            } else {
                return response()->json([
                    'status' => 'needs_registration',
                    'email' => $result['email'],
                    'name' => $result['name'],
                    'google_id' => $result['google_id'],
                    'avatar' => $result['avatar']
                ]);
            }

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Google API authentication failed: ' . $e->getMessage());
            return response()->json([
                'message' => $e->getMessage() ?: 'Google authentication failed'
            ], 422);
        }
    }

    public function googleRegister(Request $request, \App\Services\GoogleAuthService $googleAuthService)
    {
        $data = $request->json()->all() ?? $request->all();
        
        $validated = \Illuminate\Support\Facades\Validator::make($data, [
            'access_token' => 'required|string',
            'faculty' => 'required|string|in:' . implode(',', User::FACULTIES),
        ])->validate();

        $accessToken = $validated['access_token'];

        try {
            $googleProvider = \Laravel\Socialite\Facades\Socialite::driver('google');
            if (!$googleProvider instanceof \Laravel\Socialite\Two\AbstractProvider) {
                return response()->json([
                    'message' => 'Google provider configuration error'
                ], 500);
            }

            // Retrieve Google user statelessly using access token via Socialite
            $googleUser = $googleProvider
                ->stateless()
                ->userFromToken($accessToken);

            if (!$googleUser) {
                return response()->json([
                    'message' => 'Failed to retrieve user info from Google'
                ], 401);
            }

            // Process via the shared service (allowAutoCreate = true, passing selected faculty)
            $result = $googleAuthService->processGoogleUser($googleUser, $validated['faculty'], true);

            if ($result['status'] === 'success') {
                $user = $result['user'];
                $token = $user->createToken('auth_token')->plainTextToken;

                return response()->json([
                    'message' => 'Registration successful',
                    'user' => $user,
                    'token' => $token
                ], 201);
            } else {
                return response()->json([
                    'message' => 'Failed to complete registration'
                ], 500);
            }

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Google API registration failed: ' . $e->getMessage());
            return response()->json([
                'message' => $e->getMessage() ?: 'Google registration failed'
            ], 422);
        }
    }
}
