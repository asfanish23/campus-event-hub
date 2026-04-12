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
        \Log::info('Register endpoint called', ['path' => $request->getPathInfo(), 'method' => $request->getMethod()]);
        
        // Get JSON data explicitly
        $data = $request->json()->all();
        \Log::info('Request JSON data', ['data' => $data]);
        
        try {
            // Validate using JSON data
            $validated = \Illuminate\Support\Facades\Validator::make($data, [
                'name' => 'required|string',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:6'
            ])->validate();
            
            \Log::info('Validation passed', ['validated' => $validated]);

            $user = User::create($validated + ['password' => Hash::make($validated['password'])]);
            \Log::info('User created', ['user_id' => $user->id]);

            $token = $user->createToken('auth_token')->plainTextToken;
            \Log::info('Token created');

            return response()->json([
                'user' => $user,
                'token' => $token
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Register error', ['exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    public function login(Request $request)
    {
        \Log::info('Login endpoint called');
        
        $data = $request->json()->all();
        \Log::info('Login JSON data', ['data' => ['email' => $data['email'] ?? null]]);
        
        try {
            $validated = \Illuminate\Support\Facades\Validator::make($data, [
                'email' => 'required|email',
                'password' => 'required'
            ])->validate();

            if (!Auth::attempt($validated)) {
                \Log::warning('Invalid login credentials', ['email' => $validated['email']]);
                return response()->json([
                    'message' => 'Invalid login credentials'
                ], 401);
            }

            $user = User::where('email', $validated['email'])->firstOrFail();
            $token = $user->createToken('auth_token')->plainTextToken;
            
            \Log::info('Login successful', ['user_id' => $user->id]);

            return response()->json([
                'user' => $user,
                'token' => $token
            ]);
        } catch (\Exception $e) {
            \Log::error('Login error', ['exception' => $e->getMessage()]);
            throw $e;
        }
    }
}
